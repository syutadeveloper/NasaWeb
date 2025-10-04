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
    
    $device_type = $input['device_type'] ?? 'web';
    $push_token = $input['push_token'] ?? '';
    $vapid_public_key = $input['vapid_public_key'] ?? '';
    $language = $input['language'] ?? 'ru';
    $latitude = $input['latitude'] ?? null;
    $longitude = $input['longitude'] ?? null;
    $user_id = $input['user_id'] ?? null;
    
    if (empty($push_token)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Push token обязателен'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    try {
        // Проверяем, есть ли уже устройство с таким токеном
        $check_sql = "SELECT id FROM user_devices WHERE push_token = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->execute([$push_token]);
        $existing = $check_stmt->fetch();
        
        if ($existing) {
            // Обновляем существующее устройство
            $update_sql = "UPDATE user_devices SET 
                device_type = ?, 
                vapid_public_key = ?,
                language = ?,
                latitude = ?,
                longitude = ?,
                user_id = ?
                WHERE push_token = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->execute([
                $device_type,
                $vapid_public_key,
                $language,
                $latitude,
                $longitude,
                $user_id,
                $push_token
            ]);
        } else {
            // Создаем новое устройство
            $insert_sql = "INSERT INTO user_devices (device_type, push_token, vapid_public_key, language, latitude, longitude, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->execute([
                $device_type,
                $push_token,
                $vapid_public_key,
                $language,
                $latitude,
                $longitude,
                $user_id
            ]);
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Устройство успешно зарегистрировано'
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Ошибка регистрации устройства: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
} else {
    // GET запрос - получить устройства пользователя
    $user_id = $_GET['user_id'] ?? null;
    
    try {
        if ($user_id) {
            $sql = "SELECT * FROM user_devices WHERE user_id = ? ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$user_id]);
        } else {
            $sql = "SELECT * FROM user_devices ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
        }
        
        $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'data' => $devices
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Ошибка получения устройств: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
}
?>
