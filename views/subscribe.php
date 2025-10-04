<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

// Получаем JSON из запроса
$data = json_decode(file_get_contents('php://input'), true);

$email = $data['email'] ?? null;
$categories = $data['categories'] ?? [];
$latitude = $data['latitude'] ?? null;
$longitude = $data['longitude'] ?? null;
$radius = $data['radius'] ?? 50.0; // по умолчанию 50 км

if (!$email || empty($categories)) {
    echo json_encode(['status' => 'error', 'message' => 'Не указаны email или категории']);
    exit;
}

// Подготавливаем категории для БД
$category_weather = in_array('weather', $categories) ? 1 : 0;
$category_alerts  = in_array('alerts', $categories) ? 1 : 0;
$category_space   = in_array('space', $categories) ? 1 : 0;

try {
    $stmt = $pdo->prepare("INSERT INTO subscriptions (email, category_weather, category_alerts, category_space, latitude, longitude, radius_km) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $email,
        $category_weather,
        $category_alerts,
        $category_space,
        $latitude,
        $longitude,
        $radius
    ]);

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
<form id="subscription-form">
    <div class="mb-3">
        <label for="email">Email</label>
        <input type="email" class="form-control" id="email" required>
    </div>

    <div class="mb-3">
        <label>Категории</label>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="weather" id="weather-check">
            <label class="form-check-label" for="weather-check">Погода</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="alerts" id="alerts-check">
            <label class="form-check-label" for="alerts-check">ЧС</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="space" id="space-check">
            <label class="form-check-label" for="space-check">NASA</label>
        </div>
    </div>

    <div class="mb-3">
        <label for="radius">Радиус оповещений (км)</label>
        <input type="number" class="form-control" id="radius" value="50" min="1" max="500">
    </div>

    <button type="submit" class="btn btn-primary w-100">Подписаться</button>
</form>
<script>
    document.getElementById('subscription-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const email = document.getElementById('email').value;
    const categories = Array.from(document.querySelectorAll('input[type="checkbox"]:checked')).map(cb => cb.value);
    const radius = document.getElementById('radius').value;

    navigator.geolocation.getCurrentPosition(async (pos) => {
        const lat = pos.coords.latitude;
        const lon = pos.coords.longitude;

        const response = await fetch('/api/subscribe.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({email, categories, latitude: lat, longitude: lon, radius})
        });

        const data = await response.json();
        if (data.status === 'success') {
            alert('Подписка успешно оформлена!');
        } else {
            alert('Ошибка: ' + data.message);
        }
    }, (err) => {
        alert('Не удалось получить геолокацию. Подписка невозможна.');
        console.error(err);
    });
});

</script>