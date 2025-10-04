<?php 

require_once __DIR__ . '/../config/db.php';

$TitleName = "Главная страница";














?>
<?php
// require_once __DIR__ . '/../../config/nasa.php';


?>

<?php require_once __DIR__ . '/../layout/header.php' ?>
<?php require_once __DIR__ . '/../layout/nav.php' ?>
<h1 class="translatable"></h1>  Привет это главная страница </h1>


<h1 class="translatable">NASA Web — Новости, Погода, Переводчик</h1>



<body class="p-3">
  <div class="dropdown mb-3">
    <button class="btn btn-primary dropdown-toggle" type="button" id="langDropdown" data-bs-toggle="dropdown" aria-expanded="false">
      <span class="bi bi-translate"></span> Перевести сайт
    </button>
    <ul class="dropdown-menu" aria-labelledby="langDropdown">
      <li><a class="dropdown-item" href="#" onclick="setLang('ru')">Русский</a></li>
      <li><a class="dropdown-item" href="#" onclick="setLang('en')">English</a></li>
      <li><a class="dropdown-item" href="#" onclick="setLang('fr')">Français</a></li>
    </ul>
  </div>

  <h1 class="translatable">Привет, это тестовый сайт!</h1>
  <p class="translatable">Этот текст должен перевестись при смене языка.</p>
  <p class="translatable">Добро пожаловать на наш проект NASA Alert!</p>

  <script>
function setLang(lang) {
  document.querySelectorAll('.translatable').forEach(el => {
    const text = el.innerText.trim();
    if (!text) return;

    fetch('/api/translate/index.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: new URLSearchParams({text: text, from: 'auto', to: lang})
    })
    .then(r => r.text()) // <--- сначала читаем как текст
    .then(t => {
      try {
        const res = JSON.parse(t);
        if (res.result) el.innerText = res.result;
        else console.error('Ошибка перевода:', res.error || t);
      } catch (e) {
        console.error('Ошибка парсинга JSON:', t);
      }
    })
    .catch(err => console.error('Ошибка запроса:', err));
  });
}

  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


<?php require_once __DIR__ . '/../layout/footer.php' ?>


