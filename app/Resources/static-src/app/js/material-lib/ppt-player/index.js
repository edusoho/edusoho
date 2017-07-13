import PptPlayer from 'app/common/ppt-player';

let $player = $("#ppt-player");

$.get($player.data('url'), (response) => {
  new PptPlayer({
    element: '#ppt-player',
    slides: response.images,
  });
})