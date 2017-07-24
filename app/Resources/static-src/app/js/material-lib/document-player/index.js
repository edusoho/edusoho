import DocPlayer from 'app/common/doc-player';

let $player = $("#document-player");

$.get($player.data('url'), (response) => {
  new DocPlayer({
    element: '#document-player',
    swfUrl: response.swf,
    pdfUrl: response.pdf
  });
}, 'json');