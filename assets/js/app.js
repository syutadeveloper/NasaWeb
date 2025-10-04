var map = L.map('map').setView([55.751244, 37.618423], 5); // Москва по умолчанию
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom: 18,
}).addTo(map);

// Пример запроса к NASA FIRMS (замените на ваш API)
fetch(
  'https://firms.modaps.eosdis.nasa.gov/api/area/country/Россия?key=ВАШ_API_KEY'
)
  .then((r) => r.json())
  .then((data) => {
    data.fires.forEach((fire) => {
      L.marker([fire.latitude, fire.longitude])
        .addTo(map)
        .bindPopup('Пожар! ' + fire.date);
    });
  });
