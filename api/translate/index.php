<?php
require_once __DIR__ . '/../../include/translate.php';
$text = $_POST['text'] ?? '';
$from = $_POST['from'] ?? 'en';
$to = $_POST['to'] ?? 'ru';
$result = translate($text, $from, $to);
header('Content-Type: application/json');
echo json_encode(['result' => $result]);

header('Content-Type: application/json');
require_once __DIR__ . '/../../include/translate.php';

$text = $_POST['text'] ?? '';
$from = $_POST['from'] ?? 'en';
$to = $_POST['to'] ?? 'ru';

if (!$text) {
    echo json_encode(['result' => '', 'error' => 'Нет текста для перевода']);
    exit;
}

$result = translate($text, $from, $to);

if ($result === '') {
    echo json_encode(['result' => '', 'error' => 'Ошибка перевода или пустой ответ']);
} else {
    echo json_encode(['result' => $result]);
}
?>