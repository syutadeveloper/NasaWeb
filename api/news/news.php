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

// Получаем параметры
$lang = $_GET['lang'] ?? 'ru';
$limit = (int)($_GET['limit'] ?? 20);
$source = $_GET['source'] ?? 'nasa';

try {
    // Сначала проверяем кеш в базе данных
    $sql = "SELECT * FROM news_items WHERE source = ? ORDER BY published_at DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$source, $limit]);
    $cached_news = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Если кеш пустой или устарел, получаем новые данные
    if (empty($cached_news) || strtotime($cached_news[0]['created_at']) < time() - 3600) {
        // Получаем APOD от NASA (несколько запросов для получения нескольких изображений)
        $apiKey = NASA_API_KEY;
        $apod_items = [];
        
        // Получаем изображения за последние 7 дней
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $apod_url = "https://api.nasa.gov/planetary/apod?api_key=$apiKey&date=$date";
            $apod_data = @file_get_contents($apod_url);
            
            if ($apod_data) {
                $item = json_decode($apod_data, true);
                if (isset($item['title'])) {
                    $apod_items[] = $item;
                }
            }
        }
        
        // Очищаем старые записи
        $conn->prepare("DELETE FROM news_items WHERE source = 'nasa'")->execute();
        
        // Сохраняем новые данные
        $insert_sql = "INSERT INTO news_items (source, title_en, summary_en, body_en, media_url, source_url, published_at) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        
        foreach ($apod_items as $item) {
            $insert_stmt->execute([
                'nasa',
                $item['title'] ?? '',
                $item['explanation'] ?? '',
                $item['explanation'] ?? '',
                $item['url'] ?? '',
                $item['hdurl'] ?? $item['url'] ?? '',
                $item['date'] ?? date('Y-m-d')
            ]);
        }
        
        // Получаем обновленные данные
        $stmt->execute([$source, $limit]);
        $cached_news = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Формируем ответ
    $news_items = [];
    foreach ($cached_news as $item) {
        $news_items[] = [
            'id' => $item['id'],
            'title' => $item["title_{$lang}"] ?: $item['title_en'],
            'summary' => $item["summary_{$lang}"] ?: $item['summary_en'],
            'body' => $item["body_{$lang}"] ?: $item['body_en'],
            'media_url' => $item['media_url'],
            'source_url' => $item['source_url'],
            'published_at' => $item['published_at'],
            'source' => $item['source']
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $news_items,
        'count' => count($news_items)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Ошибка получения новостей: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
