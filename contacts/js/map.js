ymaps.ready(init);

function init() {
  const map = new ymaps.Map("yandex-map", {
    center: [55.184531, 61.285359],
    zoom: 17
  });

  const myPlacemark = new ymaps.Placemark(
    [55.184531, 61.285359],
    {
      hintContent: 'Уральский региональный колледж',
      balloonContent: 'г. Челябинск, Комсомольский проспект, 113А'
    }, {

      iconLayout: 'default#image',
      iconImageHref: 'https://yandex.ru/map-widget/v1/images/map_pin_red.png',
      iconImageSize: [32, 32],
      iconImageOffset: [-16, -32]
    }
  );

  map.geoObjects.add(myPlacemark);

  myPlacemark.balloon.open();
}