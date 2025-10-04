<?php
require_once __DIR__ . '/../../include/push.php';
$title = $_POST['title'] ?? 'Уведомление';
$message = $_POST['message'] ?? '';
$userId = $_POST['userId'] ?? '';
if ($userId && $message) {
    $result = sendPush($title, $message, $userId);
    echo json_encode(['status' => 'ok', 'result' => $result]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Нет данных']);
}
?>
