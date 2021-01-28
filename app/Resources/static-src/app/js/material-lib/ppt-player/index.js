import PptPlayer from 'app/common/ppt-player';

let $player = $('#ppt-player');
let params =  $player.data('params');
new PptPlayer({
  element: '#ppt-player',
  slides: params.images,
});
