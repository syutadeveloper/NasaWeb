<?php
/**
 * Скрипт для добавления тестовых данных
 * Запускать после создания базы данных
 */

require_once __DIR__ . '/../config/db.php';

echo "Добавляем тестовые данные...\n";

try {
    // Добавляем тестовых пользователей
    $users_sql = "INSERT INTO users (email, phone, language) VALUES 
        ('test@example.com', '+996700123456', 'ru'),
        ('admin@terraalert.kg', '+996700654321', 'ru'),
        ('user@example.com', '+996700987654', 'en')";
    $conn->exec($users_sql);
    echo "✓ Добавлены тестовые пользователи\n";

    // Добавляем тестовые подписки
    $subscriptions_sql = "INSERT INTO subscriptions (email, category_weather, category_alerts, category_space, latitude, longitude, radius_km) VALUES 
        ('test@example.com', 1, 1, 1, 42.8746, 74.5698, 50),
        ('admin@terraalert.kg', 1, 1, 1, 42.8746, 74.5698, 100),
        ('user@example.com', 1, 0, 1, 42.8746, 74.5698, 30)";
    $conn->exec($subscriptions_sql);
    echo "✓ Добавлены тестовые подписки\n";

    // Добавляем тестовые устройства
    $devices_sql = "INSERT INTO user_devices (device_type, push_token, language, latitude, longitude) VALUES 
        ('web', 'test-web-token-123', 'ru', 42.8746, 74.5698),
        ('android', 'test-android-token-456', 'ru', 42.8746, 74.5698),
        ('ios', 'test-ios-token-789', 'en', 42.8746, 74.5698)";
    $conn->exec($devices_sql);
    echo "✓ Добавлены тестовые устройства\n";

    // Добавляем тестовые оповещения
    $alerts_sql = "INSERT INTO alerts (source, type, magnitude, severity, latitude, longitude, radius_km, message_ru, message_en, message_kg, status) VALUES 
        ('manual', 'earthquake', 5.2, 'warning', 42.8746, 74.5698, 100, 'Тестовое землетрясение магнитудой 5.2', 'Test earthquake magnitude 5.2', 'Сынактык жер титирөө магнитудасы 5.2', 'test'),
        ('manual', 'fire', NULL, 'advisory', 42.9000, 74.6000, 25, 'Обнаружен пожар в районе', 'Fire detected in the area', 'Аймакта өрт аныкталды', 'test'),
        ('manual', 'storm', NULL, 'info', 42.8500, 74.5500, 50, 'Сильный ветер ожидается', 'Strong wind expected', 'Күчтүү шамал күтүлүүдө', 'test'),
        ('usgs', 'earthquake', 4.8, 'advisory', 42.8000, 74.5000, 75, 'Землетрясение магнитудой 4.8', 'Earthquake magnitude 4.8', 'Жер титирөө магнитудасы 4.8', 'active'),
        ('firms', 'fire', NULL, 'warning', 42.9500, 74.7000, 30, 'Активный пожар обнаружен', 'Active fire detected', 'Активдүү өрт аныкталды', 'active')";
    $conn->exec($alerts_sql);
    echo "✓ Добавлены тестовые оповещения\n";

    // Добавляем тестовые новости NASA
    $news_sql = "INSERT INTO news_items (source, title_ru, title_en, title_kg, summary_ru, summary_en, summary_kg, media_url, source_url, published_at) VALUES 
        ('nasa', 'Красивая галактика', 'Beautiful Galaxy', 'Сулуу галактика', 'Описание красивой галактики', 'Description of beautiful galaxy', 'Сулуу галактиканын сүрөттөмөсү', 'https://apod.nasa.gov/apod/image/2301/galaxy.jpg', 'https://apod.nasa.gov/apod/ap230101.html', NOW()),
        ('nasa', 'Земля из космоса', 'Earth from Space', 'Космостон Жер', 'Вид на Землю с Международной космической станции', 'View of Earth from International Space Station', 'Эл аралык космос станциясынан Жердин көрүнүшү', 'https://apod.nasa.gov/apod/image/2301/earth.jpg', 'https://apod.nasa.gov/apod/ap230102.html', NOW()),
        ('nasa', 'Туманность Ориона', 'Orion Nebula', 'Орион тумандуулугу', 'Красивая туманность в созвездии Ориона', 'Beautiful nebula in Orion constellation', 'Орион шоокумундагы сулуу тумандуулук', 'https://apod.nasa.gov/apod/image/2301/orion.jpg', 'https://apod.nasa.gov/apod/ap230103.html', NOW())";
    $conn->exec($news_sql);
    echo "✓ Добавлены тестовые новости\n";

    // Добавляем тестовые логи рассылки
    $audit_sql = "INSERT INTO audit_logs (alert_id, recipients_count, delivered_count, failed_count, payload) VALUES 
        (1, 150, 145, 5, '{\"test\": true}'),
        (2, 75, 70, 5, '{\"test\": true}'),
        (3, 200, 195, 5, '{\"test\": true}')";
    $conn->exec($audit_sql);
    echo "✓ Добавлены тестовые логи рассылки\n";

    echo "\n✅ Все тестовые данные успешно добавлены!\n";
    echo "Теперь вы можете:\n";
    echo "- Открыть сайт: http://localhost:8084\n";
    echo "- Открыть админ панель: http://localhost:8084/admin\n";
    echo "- Открыть phpMyAdmin: http://localhost:8085\n";

} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
}
?>
