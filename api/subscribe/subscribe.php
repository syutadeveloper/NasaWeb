<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $email = $input['email'] ?? '';
    $categories = $input['categories'] ?? [];
    $latitude = $input['latitude'] ?? null;
    $longitude = $input['longitude'] ?? null;
    $radius_km = $input['radius_km'] ?? 50;
    
    if (empty($email)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Email обязателен'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    try {
        // Проверяем, есть ли уже подписка с таким email
        $check_sql = "SELECT id FROM subscriptions WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->execute([$email]);
        $existing = $check_stmt->fetch();
        
        if ($existing) {
            // Обновляем существующую подписку
            $update_sql = "UPDATE subscriptions SET 
                category_weather = ?, 
                category_alerts = ?, 
                category_space = ?,
                latitude = ?,
                longitude = ?,
                radius_km = ?
                WHERE email = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->execute([
                in_array('weather', $categories) ? 1 : 0,
                in_array('alerts', $categories) ? 1 : 0,
                in_array('space', $categories) ? 1 : 0,
                $latitude,
                $longitude,
                $radius_km,
                $email
            ]);
        } else {
            // Создаем новую подписку
            $insert_sql = "INSERT INTO subscriptions (email, category_weather, category_alerts, category_space, latitude, longitude, radius_km) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->execute([
                $email,
                in_array('weather', $categories) ? 1 : 0,
                in_array('alerts', $categories) ? 1 : 0,
                in_array('space', $categories) ? 1 : 0,
                $latitude,
                $longitude,
                $radius_km
            ]);
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Подписка успешно оформлена'
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Ошибка оформления подписки: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
} else {
    // GET запрос - получить подписки
    $email = $_GET['email'] ?? '';
    
    if (empty($email)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Email обязателен'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    try {
        $sql = "SELECT * FROM subscriptions WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);
        $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($subscription) {
            $categories = [];
            if ($subscription['category_weather']) $categories[] = 'weather';
            if ($subscription['category_alerts']) $categories[] = 'alerts';
            if ($subscription['category_space']) $categories[] = 'space';
            
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'email' => $subscription['email'],
                    'categories' => $categories,
                    'latitude' => $subscription['latitude'],
                    'longitude' => $subscription['longitude'],
                    'radius_km' => $subscription['radius_km'],
                    'created_at' => $subscription['created_at']
                ]
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'status' => 'success',
                'data' => null
            ], JSON_UNESCAPED_UNICODE);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Ошибка получения подписки: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
}
?>
