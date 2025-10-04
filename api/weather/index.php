<?php
require_once __DIR__ . '/../../config/nasa.php';
$apiKey = NASA_API_KEY;
$url = "https://api.nasa.gov/planetary/apod?api_key=$apiKey";
$response = file_get_contents($url);
header('Content-Type: application/json');
echo $response;
?>
