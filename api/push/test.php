<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../include/push_notifications.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $device_token = $input['device_token'] ?? '';
    
    if (empty($device_token)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Device token обязателен'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    try {
        $push_service = new PushNotificationService($conn);
        $result = $push_service->sendTestNotification($device_token);
        
        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Тестовое уведомление отправлено'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Ошибка отправки уведомления'
            ], JSON_UNESCAPED_UNICODE);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Ошибка: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Только POST запросы'
    ], JSON_UNESCAPED_UNICODE);
}
?>
