let $player = $('#image-player');
$.get($player.data('url'), (response) => {
  $player.html(`<img src="${response.preview}" style="width: 100%; height:auto; display:block">`);
})