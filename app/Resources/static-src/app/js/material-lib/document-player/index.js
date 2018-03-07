import DocPlayer from 'app/common/doc-player';

let $player = $('#document-player');
let params = $player.data('params');
new DocPlayer({
  element: '#document-player',
  swfUrl: params.swf,
  pdfUrl: params.pdf
});
