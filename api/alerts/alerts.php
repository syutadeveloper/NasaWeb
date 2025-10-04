<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../../config/db.php';

// Получаем параметры запроса
$bbox = $_GET['bbox'] ?? null; // minLon,minLat,maxLon,maxLat
$types = $_GET['types'] ?? 'earthquake,fire';
$since = $_GET['since'] ?? date('Y-m-d H:i:s', strtotime('-24 hours'));
$severity = $_GET['severity'] ?? null;

try {
    $sql = "SELECT * FROM alerts WHERE created_at >= ? AND status = 'active'";
    $params = [$since];
    
    // Фильтр по типам
    if ($types) {
        $type_array = explode(',', $types);
        $placeholders = str_repeat('?,', count($type_array) - 1) . '?';
        $sql .= " AND type IN ($placeholders)";
        $params = array_merge($params, $type_array);
    }
    
    // Фильтр по серьезности
    if ($severity) {
        $sql .= " AND severity = ?";
        $params[] = $severity;
    }
    
    // Фильтр по географической области
    if ($bbox) {
        $coords = explode(',', $bbox);
        if (count($coords) === 4) {
            $minLon = $coords[0];
            $minLat = $coords[1];
            $maxLon = $coords[2];
            $maxLat = $coords[3];
            $sql .= " AND longitude BETWEEN ? AND ? AND latitude BETWEEN ? AND ?";
            $params = array_merge($params, [$minLon, $maxLon, $minLat, $maxLat]);
        }
    }
    
    $sql .= " ORDER BY created_at DESC LIMIT 100";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Формируем GeoJSON
    $features = [];
    foreach ($alerts as $alert) {
        $features[] = [
            'type' => 'Feature',
            'id' => 'alert-' . $alert['id'],
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [(float)$alert['longitude'], (float)$alert['latitude']]
            ],
            'properties' => [
                'id' => $alert['id'],
                'type' => $alert['type'],
                'severity' => $alert['severity'],
                'magnitude' => $alert['magnitude'],
                'radius_km' => $alert['radius_km'],
                'message_ru' => $alert['message_ru'],
                'message_en' => $alert['message_en'],
                'message_kg' => $alert['message_kg'],
                'source' => $alert['source'],
                'created_at' => $alert['created_at'],
                'expires_at' => $alert['expires_at']
            ]
        ];
    }
    
    $response = [
        'type' => 'FeatureCollection',
        'features' => $features
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Ошибка получения оповещений: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
