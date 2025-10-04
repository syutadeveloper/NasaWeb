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
get('/api/news', 'api/news/index.php');

// Погода
get('/api/weather', 'api/weather/index.php');
post('/api/weather/get', 'api/weather/weather.php');

// Уведомления (push)
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
