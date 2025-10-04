<?php 
require_once __DIR__ . '/../config/db.php';


$TitleName = "Disaster Alert / TerraAlert - Главная страница";
?>

<?php require_once __DIR__ . '/../layout/header.php' ?>
<?php require_once __DIR__ . '/../layout/nav.php' ?>

<div class="container-fluid">
    <!-- Статус безопасности -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-success" id="safety-status">
                <div class="d-flex align-items-center">
                    <i class="bi bi-shield-check me-2"></i>
                    <span class="translatable">Статус безопасности: Норма</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Основной контент -->
    <div class="row">
        <!-- Левая колонка - Погода и подписка -->
        <div class="col-lg-4 mb-4">
        <?php
// Ваш ключ API
define('OPENWEATHER_API_KEY', '9000fe7f9a0a1200deb20cf9cfa17c28');

// Город, для которого хотим получить погоду
$city = "Bishkek";  // можно заменить на любой другой

// URL запроса к API (метрика в градусах Цельсия, язык русский)
$url = "https://api.openweathermap.org/data/2.5/weather?q={$city}&appid=" . OPENWEATHER_API_KEY . "&units=metric&lang=ru";



// Инициализация cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Получаем ответ
$response = curl_exec($ch);
curl_close($ch);

// Преобразуем JSON в массив PHP
$weather = json_decode($response, true);

// Проверяем, что данные пришли
if ($weather && $weather['cod'] == 200) {
    echo "<div class='card mt-4'>";
    echo "<div class='card-header'><h5>Погода в {$weather['name']}</h5></div>";
    echo "<div class='card-body'>";
    echo "<p>Температура: {$weather['main']['temp']}°C</p>";
    echo "<p>Ощущается как: {$weather['main']['feels_like']}°C</p>";
    echo "<p>Погодные условия: {$weather['weather'][0]['description']}</p>";
    echo "<p>Влажность: {$weather['main']['humidity']}%</p>";
    echo "<p>Скорость ветра: {$weather['wind']['speed']} м/с</p>";
    echo "</div></div>";
} else {
    echo "<p>Ошибка при получении данных погоды.</p>";
}

?>
            <!-- Погода -->
            <!-- <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-cloud-sun me-2"></i>
                        <span class="translatable">Погода</span>
                    </h5>
                </div>
                <div class="card-body" id="weather-widget">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Загрузка...</span>
                        </div>
                    </div>
                </div>
            </div> -->


            <!-- Подписка на уведомления -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-bell me-2"></i>
                        <span class="translatable">Подписка на уведомления</span>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="subscription-form">
                        <div class="mb-3">
                            <label for="email" class="form-label translatable">Email</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label translatable">Категории</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="weather" id="weather-check">
                                <label class="form-check-label" for="weather-check">
                                    <span class="translatable">Погода</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="alerts" id="alerts-check">
                                <label class="form-check-label" for="alerts-check">
                                    <span class="translatable">Оповещения о ЧС</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="space" id="space-check">
                                <label class="form-check-label" for="space-check">
                                    <span class="translatable">Новости NASA</span>
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <span class="translatable">Подписаться</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Центральная колонка - Карта -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-map me-2"></i>
                        <span class="translatable">Карта рисков</span>
                    </h5>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="filter-earthquakes">
                            <i class="bi bi-circle-fill text-danger me-1"></i>
                            <span class="translatable">Землетрясения</span>
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="filter-fires">
                            <i class="bi bi-circle-fill text-warning me-1"></i>
                            <span class="translatable">Пожары</span>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="map" style="height: 500px;"></div>
                </div>
            </div>
        </div>
    </div>
    
    <?php
// Подключаем ключ API
define('NASA_API_KEY', 'ucDQGSZBRYeNs3mekdjEl2NyJ4XueZYAi2gf7wy7');

// URL API: Astronomy Picture of the Day
$url = "https://api.nasa.gov/planetary/apod?api_key=" . NASA_API_KEY;

// Инициализация cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Получение ответа
$response = curl_exec($ch);
curl_close($ch);

// Декодируем JSON
$data = json_decode($response, true);
?>

<?php if ($data): ?>
<div class="nasa-card">
    <div class="nasa-card-header">
        <h2 class="nasa-title"><?= htmlspecialchars($data['title']) ?></h2>
        <p class="nasa-date"><?= htmlspecialchars($data['date']) ?></p>
    </div>
    <?php if (!empty($data['url'])): ?>
        <div class="nasa-image">
            <img src="<?= htmlspecialchars($data['url']) ?>" alt="<?= htmlspecialchars($data['title']) ?>" />
        </div>
    <?php endif; ?>
    <div class="nasa-explanation">
        <p><?= nl2br(htmlspecialchars($data['explanation'])) ?></p>
    </div>
</div>

<style>
.nasa-card {
    max-width: 800px;
    margin: 30px auto;
    background: #1a1a1a;
    color: #f0f0f0;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(0,0,0,0.4);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.nasa-card-header {
    padding: 20px;
    background: #111;
    border-bottom: 1px solid #333;
    text-align: center;
}

.nasa-title {
    margin: 0;
    font-size: 1.8rem;
    color: #ffd700;
}

.nasa-date {
    margin: 5px 0 0;
    font-size: 0.9rem;
    color: #aaa;
}

.nasa-image img {
    display: block;
    max-width: 100%;
    height: auto;
}

.nasa-explanation {
    padding: 20px;
    line-height: 1.6;
    font-size: 1rem;
    background: #222;
}
</style>

<?php else: ?>
<p>Ошибка при получении данных NASA.</p>
<?php endif; ?>

<iframe 
  src="https://eyes.nasa.gov/apps/solar-system/#/home?embed=true" 
  width="100%" 
  height="600px" 
  style="border: none;">
</iframe>


    <!-- Новости NASA -->
    <!-- <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-rocket me-2"></i>
                        <span class="translatable">Новости NASA</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div id="news-container" class="row">
                        <div class="col-12 text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Загрузка...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> -->

<!-- Модальное окно для критических оповещений -->
<div class="modal fade" id="alertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <span class="translatable">КРИТИЧЕСКОЕ ОПОВЕЩЕНИЕ</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="alert-content">
                    <!-- Содержимое оповещения будет загружено динамически -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <span class="translatable">Закрыть</span>
                </button>
                <button type="button" class="btn btn-primary" id="alert-action">
                    <span class="translatable">Что делать</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Глобальные переменные
let map;
let currentLocation = { lat: 42.8746, lon: 74.5698 }; // Бишкек по умолчанию
let alertMarkers = [];

// Инициализация карты
function initMap() {
    map = L.map('map').setView([currentLocation.lat, currentLocation.lon], 8);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Загружаем оповещения
    loadAlerts();
    
    // Загружаем погоду
    loadWeather();
    
    // Загружаем новости
    loadNews();
}

// Загрузка оповещений
function loadAlerts() {
    fetch('/api/alerts')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.text(); // Сначала получаем как текст
        })
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.features) {
                    displayAlerts(data.features);
                } else {
                    console.warn('Нет данных об оповещениях, используем тестовые данные');
                    displayTestAlerts();
                }
            } catch (parseError) {
                console.error('Ошибка парсинга JSON:', parseError);
                console.log('Ответ сервера:', text);
                displayTestAlerts();
            }
        })
        .catch(error => {
            console.error('Ошибка загрузки оповещений:', error);
            displayTestAlerts();
        });
}

// Тестовые данные для оповещений
function displayTestAlerts() {
    const testAlerts = [
        {
            geometry: { coordinates: [74.5698, 42.8746] },
            properties: {
                type: 'earthquake',
                severity: 'warning',
                message_ru: 'Тестовое землетрясение магнитудой 5.2',
                created_at: new Date().toISOString()
            }
        },
        {
            geometry: { coordinates: [74.6000, 42.9000] },
            properties: {
                type: 'fire',
                severity: 'advisory',
                message_ru: 'Обнаружен пожар в районе',
                created_at: new Date().toISOString()
            }
        }
    ];
    displayAlerts(testAlerts);
}

// Отображение оповещений на карте
function displayAlerts(alerts) {
    // Очищаем старые маркеры
    alertMarkers.forEach(marker => map.removeLayer(marker));
    alertMarkers = [];
    
    alerts.forEach(alert => {
        const coords = alert.geometry.coordinates;
        const props = alert.properties;
        
        // Определяем цвет маркера по типу
        let color = 'blue';
        if (props.type === 'earthquake') color = 'red';
        if (props.type === 'fire') color = 'orange';
        if (props.type === 'storm') color = 'purple';
        
        // Создаем маркер
        const marker = L.circleMarker([coords[1], coords[0]], {
            radius: 8,
            fillColor: color,
            color: 'white',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.8
        }).addTo(map);
        
        // Добавляем popup
        marker.bindPopup(`
            <div>
                <h6>${props.message_ru || props.message_en}</h6>
                <p><strong>Тип:</strong> ${props.type}</p>
                <p><strong>Серьезность:</strong> ${props.severity}</p>
                <p><strong>Время:</strong> ${new Date(props.created_at).toLocaleString()}</p>
            </div>
        `);
        
        alertMarkers.push(marker);
    });
}

// Загрузка погоды
function loadWeather() {
    fetch(`/api/weather?lat=${currentLocation.lat}&lon=${currentLocation.lon}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.text();
        })
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.status === 'success') {
                    displayWeather(data);
                } else {
                    console.warn('Ошибка API погоды, используем тестовые данные');
                    displayTestWeather();
                }
            } catch (parseError) {
                console.error('Ошибка парсинга JSON погоды:', parseError);
                console.log('Ответ сервера:', text);
                displayTestWeather();
            }
        })
        .catch(error => {
            console.error('Ошибка загрузки погоды:', error);
            displayTestWeather();
        });
}

// Тестовые данные для погоды
function displayTestWeather() {
    const testWeather = {
        current: {
            temperature: 22,
            feels_like: 25,
            humidity: 65,
            pressure: 1013,
            description: 'Переменная облачность',
            icon: '02d',
            wind_speed: 3.5,
            visibility: 10
        },
        forecast: [
            { date: '2024-01-02', temp_min: 18, temp_max: 25, description: 'Ясно', icon: '01d' },
            { date: '2024-01-03', temp_min: 16, temp_max: 23, description: 'Облачно', icon: '03d' },
            { date: '2024-01-04', temp_min: 14, temp_max: 20, description: 'Дождь', icon: '10d' }
        ]
    };
    displayWeather(testWeather);
}

// Отображение погоды
function displayWeather(weatherData) {
    const current = weatherData.current;
    const forecast = weatherData.forecast;
    
    let html = `
        <div class="text-center mb-3">
            <h3>${current.temperature}°C</h3>
            <p class="mb-0">${current.description}</p>
            <small class="text-muted">Ощущается как ${current.feels_like}°C</small>
        </div>
        <div class="row text-center">
            <div class="col-4">
                <i class="bi bi-droplet"></i>
                <div>${current.humidity}%</div>
                <small>Влажность</small>
            </div>
            <div class="col-4">
                <i class="bi bi-wind"></i>
                <div>${current.wind_speed} м/с</div>
                <small>Ветер</small>
            </div>
            <div class="col-4">
                <i class="bi bi-eye"></i>
                <div>${current.visibility} км</div>
                <small>Видимость</small>
            </div>
        </div>
    `;
    
    if (forecast && forecast.length > 0) {
        html += '<hr><h6>Прогноз на 5 дней:</h6>';
        forecast.slice(0, 5).forEach(day => {
            html += `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>${new Date(day.date).toLocaleDateString()}</span>
                    <span>${Math.round(day.temp_min)}° / ${Math.round(day.temp_max)}°</span>
                    <span>${day.description}</span>
                </div>
            `;
        });
    }
    
    document.getElementById('weather-widget').innerHTML = html;
}

// Загрузка новостей
function loadNews() {
    fetch('/api/news?lang=ru&limit=6')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.text();
        })
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.status === 'success') {
                    displayNews(data.data);
                } else {
                    console.warn('Ошибка API новостей, используем тестовые данные');
                    displayTestNews();
                }
            } catch (parseError) {
                console.error('Ошибка парсинга JSON новостей:', parseError);
                console.log('Ответ сервера:', text);
                displayTestNews();
            }
        })
        .catch(error => {
            console.error('Ошибка загрузки новостей:', error);
            displayTestNews();
        });
}

// Тестовые данные для новостей
function displayTestNews() {
    const testNews = [
        {
            id: 1,
            title: 'Красивая галактика',
            summary: 'Описание красивой галактики, снятой космическим телескопом Хаббл...',
            media_url: 'https://apod.nasa.gov/apod/image/2301/galaxy.jpg',
            published_at: new Date().toISOString()
        },
        {
            id: 2,
            title: 'Земля из космоса',
            summary: 'Вид на Землю с Международной космической станции...',
            media_url: 'https://apod.nasa.gov/apod/image/2301/earth.jpg',
            published_at: new Date().toISOString()
        },
        {
            id: 3,
            title: 'Туманность Ориона',
            summary: 'Красивая туманность в созвездии Ориона...',
            media_url: 'https://apod.nasa.gov/apod/image/2301/orion.jpg',
            published_at: new Date().toISOString()
        }
    ];
    displayNews(testNews);
}

// Отображение новостей
function displayNews(news) {
    let html = '';
    news.forEach(item => {
        html += `
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card h-100">
                    ${item.media_url ? `<img src="${item.media_url}" class="card-img-top" style="height: 200px; object-fit: cover;">` : ''}
                    <div class="card-body">
                        <h6 class="card-title">${item.title}</h6>
                        <p class="card-text">${item.summary ? item.summary.substring(0, 100) + '...' : ''}</p>
                        <small class="text-muted">${new Date(item.published_at).toLocaleDateString()}</small>
                    </div>
                </div>
            </div>
        `;
    });
    
    document.getElementById('news-container').innerHTML = html;
}

// Обработка подписки
document.getElementById('subscription-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const categories = Array.from(document.querySelectorAll('input[type="checkbox"]:checked')).map(cb => cb.value);
    
    fetch('/api/subscribe', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            email: email,
            categories: categories,
            latitude: currentLocation.lat,
            longitude: currentLocation.lon
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Подписка успешно оформлена!');
        } else {
            alert('Ошибка: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Ошибка подписки:', error);
        alert('Ошибка оформления подписки');
    });
});

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    initMap();
});
</script>

<!-- Leaflet CSS и JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<?php require_once __DIR__ . '/../layout/footer.php' ?>