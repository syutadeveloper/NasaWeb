# 🏗 Архитектура Disaster Alert / TerraAlert

## 📋 Обзор системы

Disaster Alert / TerraAlert - это веб-платформа для оповещений о чрезвычайных ситуациях, построенная на PHP + MySQL + JavaScript с интеграцией внешних API.

## 🔧 Технологический стек

### Backend
- **PHP 8.2** - основной язык программирования
- **MySQL 8.0** - база данных
- **Redis** - кеширование
- **Apache** - веб-сервер

### Frontend
- **Vanilla JavaScript** - клиентская логика
- **Bootstrap 5** - UI фреймворк
- **Leaflet** - интерактивные карты
- **PWA** - поддержка установки как приложение

### Внешние API
- **NASA API** - новости и изображения космоса
- **USGS** - данные о землетрясениях
- **NASA FIRMS** - данные о пожарах
- **OpenWeatherMap** - погодные данные
- **Firebase** - push уведомления

## 🗄 База данных

### Основные таблицы

```
users
├── id (PK)
├── email
├── phone
├── password_hash
├── language
├── created_at
└── last_active

user_devices
├── id (PK)
├── user_id (FK)
├── device_type
├── push_token
├── vapid_public_key
├── language
├── latitude
├── longitude
└── created_at

alerts
├── id (PK)
├── source
├── type
├── magnitude
├── severity
├── latitude
├── longitude
├── radius_km
├── message_ru
├── message_en
├── message_kg
├── properties (JSON)
├── created_at
├── expires_at
└── status

subscriptions
├── id (PK)
├── user_id (FK)
├── email
├── category_weather
├── category_alerts
├── category_space
├── latitude
├── longitude
├── radius_km
└── created_at

news_items
├── id (PK)
├── source
├── title_ru
├── title_en
├── title_kg
├── summary_ru
├── summary_en
├── summary_kg
├── body_ru
├── body_en
├── body_kg
├── media_url
├── source_url
├── published_at
└── created_at

audit_logs
├── id (PK)
├── alert_id (FK)
├── recipients_count
├── delivered_count
├── failed_count
├── sent_at
└── payload (JSON)
```

## 🔄 Поток данных

### 1. Сбор данных
```
Внешние API → Cron задачи → База данных
     ↓
USGS Earthquakes → update_data.php → alerts
NASA FIRMS → update_data.php → alerts
OpenWeatherMap → update_data.php → alerts
NASA News → update_data.php → news_items
```

### 2. Обработка оповещений
```
Новое оповещение → Decision Engine → Фильтрация → Push уведомления
     ↓
Проверка серьезности → Географическая фильтрация → Отправка подписчикам
```

### 3. Отображение данных
```
Frontend → API → База данных → Форматирование → JSON → Карта/UI
```

## 🌐 API Endpoints

### Публичные API
- `GET /api/weather` - погодные данные
- `GET /api/alerts` - оповещения о ЧС
- `GET /api/news` - новости NASA
- `POST /api/subscribe` - подписка на уведомления
- `POST /api/devices` - регистрация устройства

### Админ API
- `GET/POST /api/admin/alerts` - управление оповещениями
- `POST /api/push/test` - тестирование push уведомлений

## 🔧 Компоненты системы

### 1. Data Ingestion Layer
- **USGSService** - получение данных о землетрясениях
- **FIRMSService** - получение данных о пожарах
- **WeatherService** - получение погодных данных
- **Cron задачи** - автоматическое обновление

### 2. Processing Layer
- **Decision Engine** - логика принятия решений
- **Geographic Filtering** - фильтрация по местоположению
- **Severity Calculation** - расчет серьезности

### 3. Notification Layer
- **PushNotificationService** - отправка уведомлений
- **Web Push** - уведомления в браузере
- **FCM** - уведомления на мобильные устройства

### 4. Presentation Layer
- **Frontend** - пользовательский интерфейс
- **Admin Panel** - панель администратора
- **Interactive Map** - интерактивная карта

## 🚀 Развертывание

### Docker Compose
```yaml
services:
  web:        # PHP + Apache
  db:         # MySQL 8.0
  redis:      # Redis для кеширования
  phpmyadmin: # Управление БД
```

### Порты
- `8084` - основной сайт
- `8085` - phpMyAdmin
- `3307` - MySQL
- `6379` - Redis

## 🔒 Безопасность

### Аутентификация
- Простая защита админ API ключом
- CSRF защита для форм
- Валидация входных данных

### Данные
- Хеширование паролей (bcrypt)
- SQL injection защита (PDO)
- XSS защита (htmlspecialchars)

## 📊 Мониторинг

### Логирование
- Ошибки в error_log
- Аудит рассылок в audit_logs
- Логи Docker контейнеров

### Метрики
- Количество оповещений
- Статистика рассылок
- Статус сервисов

## 🔄 Масштабирование

### Горизонтальное
- Несколько экземпляров web сервиса
- Load balancer
- Shared Redis для сессий

### Вертикальное
- Увеличение ресурсов контейнеров
- Оптимизация запросов к БД
- Кеширование часто запрашиваемых данных

## 🛠 Разработка

### Структура кода
```
api/           # API endpoints
config/        # Конфигурация
cron/          # Cron задачи
database/      # Схема БД и миграции
include/       # Вспомогательные классы
layout/        # HTML шаблоны
views/         # Страницы
assets/        # Статические файлы
```

### Принципы
- MVC архитектура
- Разделение ответственности
- Переиспользование кода
- Документирование

## 🎯 Будущие улучшения

### Функциональность
- Machine Learning для предсказания ЧС
- Crowdsourcing данных от пользователей
- Интеграция с операторами связи
- Мобильное приложение

### Техническое
- Микросервисная архитектура
- Kubernetes развертывание
- GraphQL API
- Real-time обновления (WebSockets)

---

**Disaster Alert / TerraAlert** - Спасаем жизни через технологии! 🚨🌍
