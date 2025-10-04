<?php
/**
 * NASA FIRMS API Integration
 * Получает данные о пожарах с NASA FIRMS API
 */

class FIRMSService {
    private $conn;
    private $api_url = 'https://firms.modaps.eosdis.nasa.gov/api/area/country/';
    
    public function __construct($database_connection) {
        $this->conn = $database_connection;
    }
    
    /**
     * Получить данные о пожарах для Кыргызстана
     */
    public function getFiresForKyrgyzstan() {
        try {
            // Используем публичный API NASA FIRMS
            $url = 'https://firms.modaps.eosdis.nasa.gov/api/area/country/KGZ/MODIS_NRT/1';
            $data = file_get_contents($url);
            $fires = json_decode($data, true);
            
            if (!$fires || !is_array($fires)) {
                return [];
            }
            
            $processed = [];
            foreach ($fires as $fire) {
                $processed[] = [
                    'latitude' => $fire['latitude'],
                    'longitude' => $fire['longitude'],
                    'brightness' => $fire['brightness'],
                    'scan' => $fire['scan'],
                    'track' => $fire['track'],
                    'acq_date' => $fire['acq_date'],
                    'acq_time' => $fire['acq_time'],
                    'satellite' => $fire['satellite'],
                    'confidence' => $fire['confidence'],
                    'version' => $fire['version'],
                    'bright_t31' => $fire['bright_t31'],
                    'frp' => $fire['frp'],
                    'daynight' => $fire['daynight']
                ];
            }
            
            return $processed;
            
        } catch (Exception $e) {
            error_log('FIRMS API Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Получить данные о пожарах для региона (по координатам)
     */
    public function getFiresForRegion($minLat, $maxLat, $minLon, $maxLon) {
        try {
            // Используем API для области
            $url = "https://firms.modaps.eosdis.nasa.gov/api/area/csv/{$minLat},{$minLon},{$maxLat},{$maxLon}/MODIS_NRT/1";
            $data = file_get_contents($url);
            
            if (empty($data)) {
                return [];
            }
            
            $lines = explode("\n", $data);
            $fires = [];
            
            // Пропускаем заголовок
            for ($i = 1; $i < count($lines); $i++) {
                $line = trim($lines[$i]);
                if (empty($line)) continue;
                
                $fields = explode(',', $line);
                if (count($fields) >= 13) {
                    $fires[] = [
                        'latitude' => floatval($fields[0]),
                        'longitude' => floatval($fields[1]),
                        'brightness' => floatval($fields[2]),
                        'scan' => floatval($fields[3]),
                        'track' => floatval($fields[4]),
                        'acq_date' => $fields[5],
                        'acq_time' => $fields[6],
                        'satellite' => $fields[7],
                        'confidence' => intval($fields[8]),
                        'version' => $fields[9],
                        'bright_t31' => floatval($fields[10]),
                        'frp' => floatval($fields[11]),
                        'daynight' => $fields[12]
                    ];
                }
            }
            
            return $fires;
            
        } catch (Exception $e) {
            error_log('FIRMS Regional API Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Сохранить пожары в базу данных
     */
    public function saveFiresToDatabase($fires) {
        if (empty($fires)) {
            return 0;
        }
        
        $saved = 0;
        foreach ($fires as $fire) {
            try {
                // Проверяем, есть ли уже такой пожар
                $check_sql = "SELECT id FROM alerts WHERE source = 'firms' AND type = 'fire' AND latitude = ? AND longitude = ? AND created_at >= ?";
                $check_stmt = $this->conn->prepare($check_sql);
                $check_stmt->execute([
                    $fire['latitude'],
                    $fire['longitude'],
                    date('Y-m-d H:i:s', strtotime('-6 hours'))
                ]);
                
                if ($check_stmt->fetch()) {
                    continue; // Уже есть в базе
                }
                
                // Определяем серьезность по яркости и уверенности
                $severity = 'info';
                if ($fire['confidence'] >= 80 && $fire['brightness'] >= 320) {
                    $severity = 'critical';
                } elseif ($fire['confidence'] >= 60 && $fire['brightness'] >= 300) {
                    $severity = 'warning';
                } elseif ($fire['confidence'] >= 40) {
                    $severity = 'advisory';
                }
                
                // Радиус воздействия пожара (примерно 5-10 км)
                $radius_km = 8;
                
                // Создаем сообщения на разных языках
                $message_ru = "Обнаружен пожар в районе {$fire['latitude']}, {$fire['longitude']}. Уверенность: {$fire['confidence']}%";
                $message_en = "Fire detected at {$fire['latitude']}, {$fire['longitude']}. Confidence: {$fire['confidence']}%";
                $message_kg = "{$fire['latitude']}, {$fire['longitude']} координаттарында өрт аныкталды. Ишенимдүүлүк: {$fire['confidence']}%";
                
                $insert_sql = "INSERT INTO alerts (source, type, severity, latitude, longitude, radius_km, message_ru, message_en, message_kg, properties, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $insert_stmt = $this->conn->prepare($insert_sql);
                $insert_stmt->execute([
                    'firms',
                    'fire',
                    $severity,
                    $fire['latitude'],
                    $fire['longitude'],
                    $radius_km,
                    $message_ru,
                    $message_en,
                    $message_kg,
                    json_encode($fire),
                    date('Y-m-d H:i:s', strtotime($fire['acq_date'] . ' ' . $fire['acq_time']))
                ]);
                
                $saved++;
                
            } catch (Exception $e) {
                error_log('Error saving fire: ' . $e->getMessage());
            }
        }
        
        return $saved;
    }
    
    /**
     * Получить и сохранить новые пожары
     */
    public function updateFires() {
        // Получаем пожары для Кыргызстана
        $fires = $this->getFiresForKyrgyzstan();
        $saved = $this->saveFiresToDatabase($fires);
        
        return [
            'total' => count($fires),
            'saved' => $saved
        ];
    }
}
?>
