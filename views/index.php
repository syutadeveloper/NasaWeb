<?php 

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../include/mailer.php';
$TitleName = "Главная страница";


?>





<div class="container py-4">
	<h1 class="mb-4 translatable">NASA Web — Новости, Погода, Переводчик</h1>
	<div class="row mb-4">
		<div class="col-md-6">
			<h3 class="translatable">Погода от NASA</h3>
			<div id="weather-widget" class="border rounded p-3 mb-3">Загрузка...</div>
		</div>
		<div class="col-md-6">
			<h3 class="translatable">Новости NASA</h3>
			<div id="news-widget" class="border rounded p-3 mb-3">Загрузка...</div>
		</div>
	</div>
	<div class="row mb-4">
		<div class="col-md-6">
			<h3 class="translatable">Переводчик</h3>
			<form id="translate-form" class="mb-3">
				<input type="text" name="text" class="form-control mb-2" placeholder="Введите текст" required>
				<div class="row mb-2">
					<div class="col">
						<select name="from" class="form-select">
							<option value="en">Английский</option>
							<option value="ru">Русский</option>
						</select>
					</div>
					<div class="col">
						<select name="to" class="form-select">
							<option value="ru">Русский</option>
							<option value="en">Английский</option>
						</select>
					</div>
				</div>
				<button type="submit" class="btn btn-primary">Перевести</button>
			</form>
			<div id="translate-result" class="alert alert-info" style="display:none;"></div>
		</div>
		<div class="col-md-6">
			<h3 class="translatable">Подписка</h3>
			<button class="btn btn-success mb-2" onclick="subscribePush()">Включить PUSH-уведомления</button>
			<form id="subscribe-email-form">
				<input type="email" name="email" class="form-control mb-2" placeholder="Ваш email" required>
				<button type="submit" class="btn btn-warning">Подписаться на рассылку</button>
			</form>
			<div id="subscribe-result" class="alert alert-info" style="display:none;"></div>
		</div>
	</div>
	<div class="row mb-4">
		<div class="col">
			<h3 class="translatable">Ваше местоположение</h3>
			<div id="location-widget" class="border rounded p-3">Загрузка...</div>
		</div>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Погода
fetch('/api/weather/index.php')
	.then(r => r.json())
	.then(data => {
		document.getElementById('weather-widget').innerHTML = `<b>${data.title || 'Погода'}</b><br><img src='${data.url}' style='max-width:100%'><br>${data.explanation || ''}`;
	});
// Новости
fetch('/api/news/index.php')
	.then(r => r.json())
	.then(news => {
		let html = news.map(n => `<b>${n.title}</b><br><small>${n.pubDate}</small><br>${n.description}<br><a href='${n.link}' target='_blank'>Подробнее</a><hr>`).join('');
		document.getElementById('news-widget').innerHTML = html;
	});
// Переводчик
document.getElementById('translate-form').onsubmit = function(e) {
	e.preventDefault();
	let fd = new FormData(this);
	fetch('/api/translate/index.php', {method:'POST', body:fd})
		.then(r => r.json())
		.then(res => {
			let block = document.getElementById('translate-result');
			block.style.display = 'block';
			block.innerText = res.result;
		});
};
// Email-подписка (заглушка)
document.getElementById('subscribe-email-form').onsubmit = function(e) {
	e.preventDefault();
	let fd = new FormData(this);
	// Здесь должен быть реальный API для email-подписки
	document.getElementById('subscribe-result').style.display = 'block';
	document.getElementById('subscribe-result').innerText = 'Спасибо за подписку!';
};
// PUSH-уведомления (заглушка)
function subscribePush() {
	alert('PUSH-уведомления будут реализованы позже!');
}
// Местоположение
if (navigator.geolocation) {
	navigator.geolocation.getCurrentPosition(function(pos) {
		document.getElementById('location-widget').innerText = `Широта: ${pos.coords.latitude}, Долгота: ${pos.coords.longitude}`;
	}, function() {
		document.getElementById('location-widget').innerText = 'Не удалось получить местоположение.';
	});
} else {
	document.getElementById('location-widget').innerText = 'Геолокация не поддерживается.';
}
</script>


