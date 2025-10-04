<?php 
require_once __DIR__ . '/../config/db.php';

$TitleName = "Админ панель - Disaster Alert";
?>

<?php require_once __DIR__ . '/../layout/header.php' ?>
<?php require_once __DIR__ . '/../layout/nav.php' ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="bi bi-gear me-2"></i>
                Админ панель
            </h1>
        </div>
    </div>

    <!-- Статистика -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-primary" id="total-alerts">-</h5>
                    <p class="card-text">Всего оповещений</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-success" id="active-alerts">-</h5>
                    <p class="card-text">Активных оповещений</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-info" id="total-subscriptions">-</h5>
                    <p class="card-text">Подписчиков</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-warning" id="total-devices">-</h5>
                    <p class="card-text">Устройств</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Создание оповещения -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-plus-circle me-2"></i>
                        Создать оповещение
                    </h5>
                </div>
                <div class="card-body">
                    <form id="create-alert-form">
                        <div class="mb-3">
                            <label for="alert-type" class="form-label">Тип оповещения</label>
                            <select class="form-select" id="alert-type" required>
                                <option value="earthquake">Землетрясение</option>
                                <option value="fire">Пожар</option>
                                <option value="flood">Наводнение</option>
                                <option value="storm">Шторм</option>
                                <option value="air_quality">Качество воздуха</option>
                                <option value="other">Другое</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="alert-severity" class="form-label">Серьезность</label>
                            <select class="form-select" id="alert-severity" required>
                                <option value="info">Информация</option>
                                <option value="advisory">Рекомендация</option>
                                <option value="warning">Предупреждение</option>
                                <option value="critical">Критическое</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="alert-magnitude" class="form-label">Магнитуда/Интенсивность</label>
                            <input type="number" class="form-control" id="alert-magnitude" step="0.1" min="0" max="10">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="alert-lat" class="form-label">Широта</label>
                                    <input type="number" class="form-control" id="alert-lat" step="0.000001" value="42.8746" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="alert-lon" class="form-label">Долгота</label>
                                    <input type="number" class="form-control" id="alert-lon" step="0.000001" value="74.5698" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="alert-radius" class="form-label">Радиус воздействия (км)</label>
                            <input type="number" class="form-control" id="alert-radius" value="50" min="1" max="1000">
                        </div>
                        
                        <div class="mb-3">
                            <label for="alert-message-ru" class="form-label">Сообщение (Русский)</label>
                            <textarea class="form-control" id="alert-message-ru" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="alert-message-en" class="form-label">Сообщение (English)</label>
                            <textarea class="form-control" id="alert-message-en" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="alert-message-kg" class="form-label">Сообщение (Кыргызча)</label>
                            <textarea class="form-control" id="alert-message-kg" rows="3"></textarea>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="is-test" checked>
                            <label class="form-check-label" for="is-test">
                                Тестовое оповещение
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-send me-2"></i>
                            Создать оповещение
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Список оповещений -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-list-ul me-2"></i>
                        Последние оповещения
                    </h5>
                    <button class="btn btn-outline-primary btn-sm" onclick="loadAlerts()">
                        <i class="bi bi-arrow-clockwise me-1"></i>
                        Обновить
                    </button>
                </div>
                <div class="card-body">
                    <div id="alerts-list">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Загрузка...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Логи рассылки -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-journal-text me-2"></i>
                        Логи рассылки
                    </h5>
                </div>
                <div class="card-body">
                    <div id="audit-logs">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Загрузка...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Загрузка статистики
function loadStats() {
    // Загружаем количество оповещений
    fetch('/api/admin/alerts?admin_key=admin123')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const alerts = data.data;
                const total = alerts.length;
                const active = alerts.filter(a => a.status === 'active').length;
                
                document.getElementById('total-alerts').textContent = total;
                document.getElementById('active-alerts').textContent = active;
            }
        })
        .catch(error => console.error('Ошибка загрузки статистики:', error));
    
    // Загружаем количество подписчиков
    fetch('/api/subscribe?email=stats')
        .then(response => response.json())
        .then(data => {
            // Здесь нужно будет создать отдельный endpoint для статистики
            document.getElementById('total-subscriptions').textContent = '-';
        })
        .catch(error => console.error('Ошибка загрузки подписчиков:', error));
    
    // Загружаем количество устройств
    fetch('/api/devices')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('total-devices').textContent = data.data.length;
            }
        })
        .catch(error => console.error('Ошибка загрузки устройств:', error));
}

// Загрузка оповещений
function loadAlerts() {
    fetch('/api/admin/alerts?admin_key=admin123')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                displayAlerts(data.data);
            }
        })
        .catch(error => {
            console.error('Ошибка загрузки оповещений:', error);
            document.getElementById('alerts-list').innerHTML = '<p class="text-muted">Ошибка загрузки</p>';
        });
}

// Отображение оповещений
function displayAlerts(alerts) {
    let html = '';
    
    if (alerts.length === 0) {
        html = '<p class="text-muted">Оповещений нет</p>';
    } else {
        alerts.slice(0, 10).forEach(alert => {
            const severityClass = {
                'info': 'text-info',
                'advisory': 'text-warning',
                'warning': 'text-warning',
                'critical': 'text-danger'
            }[alert.severity] || 'text-secondary';
            
            html += `
                <div class="border-bottom pb-2 mb-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">${alert.message_ru || alert.message_en || 'Без сообщения'}</h6>
                            <small class="text-muted">
                                ${alert.type} • 
                                <span class="${severityClass}">${alert.severity}</span> • 
                                ${new Date(alert.created_at).toLocaleString()}
                            </small>
                        </div>
                        <span class="badge bg-${alert.status === 'active' ? 'success' : 'secondary'}">${alert.status}</span>
                    </div>
                    ${alert.magnitude ? `<small class="text-muted">Магнитуда: ${alert.magnitude}</small>` : ''}
                </div>
            `;
        });
    }
    
    document.getElementById('alerts-list').innerHTML = html;
}

// Создание оповещения
document.getElementById('create-alert-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        type: document.getElementById('alert-type').value,
        severity: document.getElementById('alert-severity').value,
        magnitude: document.getElementById('alert-magnitude').value || null,
        latitude: parseFloat(document.getElementById('alert-lat').value),
        longitude: parseFloat(document.getElementById('alert-lon').value),
        radius_km: parseFloat(document.getElementById('alert-radius').value),
        message_ru: document.getElementById('alert-message-ru').value,
        message_en: document.getElementById('alert-message-en').value,
        message_kg: document.getElementById('alert-message-kg').value,
        is_test: document.getElementById('is-test').checked
    };
    
    fetch('/api/admin/alerts?admin_key=admin123', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Оповещение успешно создано!');
            loadAlerts();
            loadStats();
        } else {
            alert('Ошибка: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Ошибка создания оповещения:', error);
        alert('Ошибка создания оповещения');
    });
});

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadAlerts();
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php' ?>
