let $player = $('#image-player');
let params =  $player.data('params');
$player.html(`<img src="${params.preview}" style="width: 100%; height:auto; display:block">`);
