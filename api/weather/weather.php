<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/nasa.php';

// Получаем координаты из запроса
$lat = $_GET['lat'] ?? 42.8746; // Бишкек по умолчанию
$lon = $_GET['lon'] ?? 74.5698;

// OpenWeatherMap API ключ
$api_key = OPENWEATHER_API_KEY;
$weather_url = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&appid={$api_key}&units=metric&lang=ru";
$forecast_url = "https://api.openweathermap.org/data/2.5/forecast?lat={$lat}&lon={$lon}&appid={$api_key}&units=metric&lang=ru";

try {
    // Получаем текущую погоду
    $weather_data = @file_get_contents($weather_url);
    if (!$weather_data) {
        throw new Exception('Не удалось получить данные о погоде. Проверьте API ключ и интернет соединение.');
    }
    $weather = json_decode($weather_data, true);
    
    if (isset($weather['error'])) {
        throw new Exception('Ошибка API: ' . $weather['error']['message']);
    }
    
    // Получаем прогноз на 5 дней
    $forecast_data = @file_get_contents($forecast_url);
    if (!$forecast_data) {
        throw new Exception('Не удалось получить прогноз погоды.');
    }
    $forecast = json_decode($forecast_data, true);
    
    if (isset($forecast['error'])) {
        throw new Exception('Ошибка API прогноза: ' . $forecast['error']['message']);
    }
    
    // Формируем ответ
    $response = [
        'status' => 'success',
        'current' => [
            'temperature' => round($weather['main']['temp']),
            'feels_like' => round($weather['main']['feels_like']),
            'humidity' => $weather['main']['humidity'],
            'pressure' => $weather['main']['pressure'],
            'description' => $weather['weather'][0]['description'],
            'icon' => $weather['weather'][0]['icon'],
            'wind_speed' => $weather['wind']['speed'],
            'wind_direction' => $weather['wind']['deg'] ?? 0,
            'visibility' => $weather['visibility'] / 1000, // в км
            'uv_index' => 0, // будет добавлено позже
            'air_quality' => [
                'aqi' => rand(1, 5), // заглушка
                'pm25' => rand(10, 50),
                'pm10' => rand(20, 80)
            ]
        ],
        'forecast' => []
    ];
    
    // Обрабатываем прогноз (берем по одному дню)
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
                'wind_speed' => $item['wind']['speed']
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
    
    $response['forecast'] = array_values($processed_days);
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Ошибка получения данных о погоде: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
