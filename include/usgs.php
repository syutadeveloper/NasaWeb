<?php
/**
 * USGS Earthquakes API Integration
 * Получает данные о землетрясениях с USGS API
 */

class USGSService {
    private $conn;
    private $api_url = 'https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/';
    
    public function __construct($database_connection) {
        $this->conn = $database_connection;
    }
    
    /**
     * Получить землетрясения за последние 24 часа
     */
    public function getRecentEarthquakes() {
        try {
            $url = $this->api_url . 'all_day.geojson';
            $data = file_get_contents($url);
            $earthquakes = json_decode($data, true);
            
            if (!$earthquakes || !isset($earthquakes['features'])) {
                return [];
            }
            
            $processed = [];
            foreach ($earthquakes['features'] as $feature) {
                $props = $feature['properties'];
                $coords = $feature['geometry']['coordinates'];
                
                // Фильтруем только значимые землетрясения (магнитуда >= 4.0)
                if ($props['mag'] >= 4.0) {
                    $processed[] = [
                        'magnitude' => $props['mag'],
                        'latitude' => $coords[1],
                        'longitude' => $coords[0],
                        'depth' => $coords[2] ?? 0,
                        'place' => $props['place'],
                        'time' => $props['time'],
                        'updated' => $props['updated'],
                        'url' => $props['url'],
                        'detail' => $props['detail'],
                        'status' => $props['status'],
                        'tsunami' => $props['tsunami'],
                        'sig' => $props['sig'],
                        'net' => $props['net'],
                        'code' => $props['code'],
                        'ids' => $props['ids'],
                        'sources' => $props['sources'],
                        'types' => $props['types'],
                        'nst' => $props['nst'],
                        'dmin' => $props['dmin'],
                        'rms' => $props['rms'],
                        'gap' => $props['gap'],
                        'magType' => $props['magType'],
                        'type' => $props['type'],
                        'title' => $props['title']
                    ];
                }
            }
            
            return $processed;
            
        } catch (Exception $e) {
            error_log('USGS API Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Сохранить землетрясения в базу данных
     */
    public function saveEarthquakesToDatabase($earthquakes) {
        if (empty($earthquakes)) {
            return 0;
        }
        
        $saved = 0;
        foreach ($earthquakes as $eq) {
            try {
                // Проверяем, есть ли уже такое землетрясение
                $check_sql = "SELECT id FROM alerts WHERE source = 'usgs' AND type = 'earthquake' AND magnitude = ? AND latitude = ? AND longitude = ? AND created_at >= ?";
                $check_stmt = $this->conn->prepare($check_sql);
                $check_stmt->execute([
                    $eq['magnitude'],
                    $eq['latitude'],
                    $eq['longitude'],
                    date('Y-m-d H:i:s', strtotime('-1 hour'))
                ]);
                
                if ($check_stmt->fetch()) {
                    continue; // Уже есть в базе
                }
                
                // Определяем серьезность по магнитуде
                $severity = 'info';
                if ($eq['magnitude'] >= 7.0) {
                    $severity = 'critical';
                } elseif ($eq['magnitude'] >= 6.0) {
                    $severity = 'warning';
                } elseif ($eq['magnitude'] >= 5.0) {
                    $severity = 'advisory';
                }
                
                // Рассчитываем радиус воздействия (примерно)
                $radius_km = $this->calculateRadius($eq['magnitude']);
                
                // Создаем сообщения на разных языках
                $message_ru = "Землетрясение магнитудой {$eq['magnitude']} в районе {$eq['place']}";
                $message_en = "Earthquake magnitude {$eq['magnitude']} near {$eq['place']}";
                $message_kg = "Жер титирөө магнитудасы {$eq['magnitude']} {$eq['place']} аймагында";
                
                $insert_sql = "INSERT INTO alerts (source, type, magnitude, severity, latitude, longitude, radius_km, message_ru, message_en, message_kg, properties, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $insert_stmt = $this->conn->prepare($insert_sql);
                $insert_stmt->execute([
                    'usgs',
                    'earthquake',
                    $eq['magnitude'],
                    $severity,
                    $eq['latitude'],
                    $eq['longitude'],
                    $radius_km,
                    $message_ru,
                    $message_en,
                    $message_kg,
                    json_encode($eq),
                    date('Y-m-d H:i:s', $eq['time'] / 1000)
                ]);
                
                $saved++;
                
            } catch (Exception $e) {
                error_log('Error saving earthquake: ' . $e->getMessage());
            }
        }
        
        return $saved;
    }
    
    /**
     * Рассчитать радиус воздействия землетрясения
     */
    private function calculateRadius($magnitude) {
        // Простая формула: радиус примерно равен магнитуде * 10 км
        return min($magnitude * 10, 500); // Максимум 500 км
    }
    
    /**
     * Получить и сохранить новые землетрясения
     */
    public function updateEarthquakes() {
        $earthquakes = $this->getRecentEarthquakes();
        $saved = $this->saveEarthquakesToDatabase($earthquakes);
        
        return [
            'total' => count($earthquakes),
            'saved' => $saved
        ];
    }
}
?>
