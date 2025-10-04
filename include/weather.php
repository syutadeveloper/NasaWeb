<?php
/**
 * OpenWeatherMap API Integration
 * Получает данные о погоде и качестве воздуха
 */

class WeatherService {
    private $conn;
    private $api_key;
    private $api_url = 'https://api.openweathermap.org/data/2.5/';
    
    public function __construct($database_connection, $api_key) {
        $this->conn = $database_connection;
        $this->api_key = $api_key;
    }
    
    /**
     * Получить текущую погоду
     */
    public function getCurrentWeather($lat, $lon) {
        try {
            $url = $this->api_url . "weather?lat={$lat}&lon={$lon}&appid={$this->api_key}&units=metric&lang=ru";
            $data = file_get_contents($url);
            $weather = json_decode($data, true);
            
            if (!$weather || isset($weather['error'])) {
                return null;
            }
            
            return [
                'temperature' => round($weather['main']['temp']),
                'feels_like' => round($weather['main']['feels_like']),
                'humidity' => $weather['main']['humidity'],
                'pressure' => $weather['main']['pressure'],
                'description' => $weather['weather'][0]['description'],
                'icon' => $weather['weather'][0]['icon'],
                'wind_speed' => $weather['wind']['speed'],
                'wind_direction' => $weather['wind']['deg'] ?? 0,
                'visibility' => $weather['visibility'] / 1000, // в км
                'clouds' => $weather['clouds']['all'],
                'sunrise' => $weather['sys']['sunrise'],
                'sunset' => $weather['sys']['sunset'],
                'country' => $weather['sys']['country'],
                'city' => $weather['name']
            ];
            
        } catch (Exception $e) {
            error_log('Weather API Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Получить прогноз погоды на 5 дней
     */
    public function getForecast($lat, $lon) {
        try {
            $url = $this->api_url . "forecast?lat={$lat}&lon={$lon}&appid={$this->api_key}&units=metric&lang=ru";
            $data = file_get_contents($url);
            $forecast = json_decode($data, true);
            
            if (!$forecast || isset($forecast['error'])) {
                return [];
            }
            
            $processed_days = [];
            foreach ($forecast['list'] as $item) {
                $date = date('Y-m-d', $item['dt']);
                if (!isset($processed_days[$date])) {
                    $processed_days[$date] = [
                        'date' => $date,
                        'temp_min' => $item['main']['temp_min'],
                        'temp_max' => $item['main']['temp_max'],
                        'description' => $item['weather'][0]['description'],
                        'icon' => $item['weather'][0]['icon'],
                        'humidity' => $item['main']['humidity'],
                        'wind_speed' => $item['wind']['speed'],
                        'pressure' => $item['main']['pressure']
                    ];
                } else {
                    // Обновляем минимум и максимум
                    if ($item['main']['temp_min'] < $processed_days[$date]['temp_min']) {
                        $processed_days[$date]['temp_min'] = $item['main']['temp_min'];
                    }
                    if ($item['main']['temp_max'] > $processed_days[$date]['temp_max']) {
                        $processed_days[$date]['temp_max'] = $item['main']['temp_max'];
                    }
                }
            }
            
            return array_values($processed_days);
            
        } catch (Exception $e) {
            error_log('Forecast API Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Получить данные о качестве воздуха
     */
    public function getAirQuality($lat, $lon) {
        try {
            $url = "http://api.openweathermap.org/data/2.5/air_pollution?lat={$lat}&lon={$lon}&appid={$this->api_key}";
            $data = file_get_contents($url);
            $air_quality = json_decode($data, true);
            
            if (!$air_quality || isset($air_quality['error'])) {
                return null;
            }
            
            $aqi = $air_quality['list'][0]['main']['aqi'];
            $components = $air_quality['list'][0]['components'];
            
            return [
                'aqi' => $aqi,
                'aqi_level' => $this->getAQILevel($aqi),
                'pm25' => round($components['pm2_5'], 1),
                'pm10' => round($components['pm10'], 1),
                'no2' => round($components['no2'], 1),
                'so2' => round($components['so2'], 1),
                'o3' => round($components['o3'], 1),
                'co' => round($components['co'], 1)
            ];
            
        } catch (Exception $e) {
            error_log('Air Quality API Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Получить уровень AQI
     */
    private function getAQILevel($aqi) {
        switch ($aqi) {
            case 1: return 'Хорошо';
            case 2: return 'Удовлетворительно';
            case 3: return 'Умеренно';
            case 4: return 'Плохо';
            case 5: return 'Очень плохо';
            default: return 'Неизвестно';
        }
    }
    
    /**
     * Проверить на экстремальные погодные условия
     */
    public function checkExtremeWeather($lat, $lon) {
        $weather = $this->getCurrentWeather($lat, $lon);
        if (!$weather) {
            return null;
        }
        
        $alerts = [];
        
        // Проверяем сильный ветер
        if ($weather['wind_speed'] > 15) { // м/с
            $alerts[] = [
                'type' => 'storm',
                'severity' => $weather['wind_speed'] > 25 ? 'critical' : 'warning',
                'message_ru' => "Сильный ветер: {$weather['wind_speed']} м/с",
                'message_en' => "Strong wind: {$weather['wind_speed']} m/s",
                'message_kg' => "Күчтүү шамал: {$weather['wind_speed']} м/с"
            ];
        }
        
        // Проверяем экстремальную температуру
        if ($weather['temperature'] > 35) {
            $alerts[] = [
                'type' => 'heat',
                'severity' => 'warning',
                'message_ru' => "Экстремальная жара: {$weather['temperature']}°C",
                'message_en' => "Extreme heat: {$weather['temperature']}°C",
                'message_kg' => "Экстремалдык ысык: {$weather['temperature']}°C"
            ];
        } elseif ($weather['temperature'] < -20) {
            $alerts[] = [
                'type' => 'cold',
                'severity' => 'warning',
                'message_ru' => "Экстремальный холод: {$weather['temperature']}°C",
                'message_en' => "Extreme cold: {$weather['temperature']}°C",
                'message_kg' => "Экстремалдык суук: {$weather['temperature']}°C"
            ];
        }
        
        // Проверяем плохую видимость
        if ($weather['visibility'] < 1) {
            $alerts[] = [
                'type' => 'fog',
                'severity' => 'advisory',
                'message_ru' => "Плохая видимость: {$weather['visibility']} км",
                'message_en' => "Poor visibility: {$weather['visibility']} km",
                'message_kg' => "Жаман көрүнүм: {$weather['visibility']} км"
            ];
        }
        
        return $alerts;
    }
    
    /**
     * Сохранить погодные оповещения в базу данных
     */
    public function saveWeatherAlerts($alerts, $lat, $lon) {
        if (empty($alerts)) {
            return 0;
        }
        
        $saved = 0;
        foreach ($alerts as $alert) {
            try {
                $insert_sql = "INSERT INTO alerts (source, type, severity, latitude, longitude, radius_km, message_ru, message_en, message_kg, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $insert_stmt = $this->conn->prepare($insert_sql);
                $insert_stmt->execute([
                    'weather',
                    $alert['type'],
                    $alert['severity'],
                    $lat,
                    $lon,
                    50, // Радиус 50 км для погодных условий
                    $alert['message_ru'],
                    $alert['message_en'],
                    $alert['message_kg'],
                    date('Y-m-d H:i:s')
                ]);
                
                $saved++;
                
            } catch (Exception $e) {
                error_log('Error saving weather alert: ' . $e->getMessage());
            }
        }
        
        return $saved;
    }
}
?>
