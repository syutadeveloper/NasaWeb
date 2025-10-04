<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../include/translate.php';

$text = $_POST['text'] ?? '';
$from = $_POST['from'] ?? 'auto';
$to   = $_POST['to'] ?? 'en';

if (!$text) {
    echo json_encode(['result' => '', 'error' => 'Нет текста для перевода']);
    exit;
}

$result = translate($text, $from, $to);

if (!$result) {
    echo json_encode(['result' => '', 'error' => 'Ошибка перевода']);
    exit;
}

echo json_encode(['result' => $result]);
