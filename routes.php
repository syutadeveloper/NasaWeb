<?php

require_once __DIR__ . '/router.php';

/*
|--------------------------------------------------------------------------
| Основные страницы
|--------------------------------------------------------------------------
*/

// Главная
get('/', 'views/index.php');

// Профиль пользователя
get('/profile', 'views/profile.php');

// Подписка
get('/subscribe', 'views/subscribe.php');

// Админ панель
get('/admin', 'views/admin.php');

// Страница 404
any('/404', 'views/404.php');


/*
|--------------------------------------------------------------------------
| Авторизация
|--------------------------------------------------------------------------
*/

// Форма входа
get('/sign-in', 'auth/sign-in.php');

// Форма регистрации
get('/sign-up', 'auth/sign-up.php');


/*
|--------------------------------------------------------------------------
| API: Новости, Погода, Уведомления, Переводчик
|--------------------------------------------------------------------------
*/

// Новости
get('/api/news', 'api/news/news.php');

// Погода
get('/api/weather', 'api/weather/weather.php');
post('/api/weather/get', 'api/weather/weather.php');

// Оповещения о ЧС
get('/api/alerts', 'api/alerts/alerts.php');

// Подписка на уведомления
get('/api/subscribe', 'api/subscribe/subscribe.php');
post('/api/subscribe', 'api/subscribe/subscribe.php');

// Регистрация устройств для push
get('/api/devices', 'api/devices/devices.php');
post('/api/devices', 'api/devices/devices.php');

// Админ API
get('/api/admin/alerts', 'api/admin/alerts.php');
post('/api/admin/alerts', 'api/admin/alerts.php');

// Push уведомления
post('/api/push/test', 'api/push/test.php');

// Уведомления (push) - старый endpoint
get('/api/notifications', 'api/notifications/index.php');

// Переводчик
get('/api/translate', 'api/translate/index.php');
post('/api/translate/text', 'api/translate/translate.php');


/*
|--------------------------------------------------------------------------
| Вспомогательные include-модули (если будут прямые вызовы)
|--------------------------------------------------------------------------
*/

get('/include/location', 'include/location.php');
get('/include/news', 'include/news.php');
get('/include/mailer', 'include/mailer.php');
get('/include/push', 'include/push.php');
get('/include/translate', 'include/translate.php');


/*
|--------------------------------------------------------------------------
| Фолбэк — если маршрут не найден
|--------------------------------------------------------------------------
*/

any('404', 'views/404.php');
