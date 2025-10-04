<?php
/**
 * Скрипт для автоматического обновления данных
 * Запускается по cron каждые 5-10 минут
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../include/usgs.php';
require_once __DIR__ . '/../include/firms.php';
require_once __DIR__ . '/../include/weather.php';

// Настройки
$openweather_api_key = 'YOUR_OPENWEATHER_API_KEY'; // Замените на ваш ключ
$kyrgyzstan_lat = 42.8746;
$kyrgyzstan_lon = 74.5698;

echo "[" . date('Y-m-d H:i:s') . "] Начинаем обновление данных...\n";

try {
    // 1. Обновляем данные о землетрясениях
    echo "Обновляем данные о землетрясениях...\n";
    $usgs = new USGSService($conn);
    $earthquakes_result = $usgs->updateEarthquakes();
    echo "Землетрясения: получено {$earthquakes_result['total']}, сохранено {$earthquakes_result['saved']}\n";
    
    // 2. Обновляем данные о пожарах
    echo "Обновляем данные о пожарах...\n";
    $firms = new FIRMSService($conn);
    $fires_result = $firms->updateFires();
    echo "Пожары: получено {$fires_result['total']}, сохранено {$fires_result['saved']}\n";
    
    // 3. Проверяем экстремальные погодные условия
    echo "Проверяем погодные условия...\n";
    $weather = new WeatherService($conn, $openweather_api_key);
    $weather_alerts = $weather->checkExtremeWeather($kyrgyzstan_lat, $kyrgyzstan_lon);
    if ($weather_alerts) {
        $weather_saved = $weather->saveWeatherAlerts($weather_alerts, $kyrgyzstan_lat, $kyrgyzstan_lon);
        echo "Погодные оповещения: создано {$weather_saved}\n";
    } else {
        echo "Экстремальных погодных условий не обнаружено\n";
    }
    
    // 4. Очищаем старые оповещения (старше 7 дней)
    echo "Очищаем старые оповещения...\n";
    $cleanup_sql = "DELETE FROM alerts WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $conn->exec($cleanup_sql);
    $deleted = $conn->rowCount();
    echo "Удалено старых оповещений: {$deleted}\n";
    
    echo "[" . date('Y-m-d H:i:s') . "] Обновление завершено успешно!\n";
    
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ОШИБКА: " . $e->getMessage() . "\n";
    error_log("Cron update error: " . $e->getMessage());
}
?>
