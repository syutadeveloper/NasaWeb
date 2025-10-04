<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../../config/db.php';

// Простая авторизация (в реальном проекте используйте JWT или сессии)
$admin_key = $_GET['admin_key'] ?? $_POST['admin_key'] ?? '';
if ($admin_key !== 'admin123') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Неверный ключ администратора'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $type = $input['type'] ?? 'earthquake';
    $severity = $input['severity'] ?? 'warning';
    $magnitude = $input['magnitude'] ?? null;
    $latitude = $input['latitude'] ?? 42.8746;
    $longitude = $input['longitude'] ?? 74.5698;
    $radius_km = $input['radius_km'] ?? 50;
    $message_ru = $input['message_ru'] ?? 'Тестовое оповещение';
    $message_en = $input['message_en'] ?? 'Test alert';
    $message_kg = $input['message_kg'] ?? 'Сынактык билдирүү';
    $is_test = $input['is_test'] ?? true;
    
    try {
        $sql = "INSERT INTO alerts (source, type, magnitude, severity, latitude, longitude, radius_km, message_ru, message_en, message_kg, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'manual',
            $type,
            $magnitude,
            $severity,
            $latitude,
            $longitude,
            $radius_km,
            $message_ru,
            $message_en,
            $message_kg,
            $is_test ? 'test' : 'active'
        ]);
        
        $alert_id = $conn->lastInsertId();
        
        // Логируем создание оповещения
        $log_sql = "INSERT INTO audit_logs (alert_id, recipients_count, delivered_count, failed_count, payload) VALUES (?, ?, ?, ?, ?)";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->execute([
            $alert_id,
            0,
            0,
            0,
            json_encode($input)
        ]);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Оповещение создано',
            'alert_id' => $alert_id
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Ошибка создания оповещения: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
} else {
    // GET запрос - получить все оповещения
    try {
        $sql = "SELECT a.*, al.recipients_count, al.delivered_count, al.failed_count, al.sent_at 
                FROM alerts a 
                LEFT JOIN audit_logs al ON a.id = al.alert_id 
                ORDER BY a.created_at DESC 
                LIMIT 50";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'data' => $alerts
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Ошибка получения оповещений: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
}
?>
