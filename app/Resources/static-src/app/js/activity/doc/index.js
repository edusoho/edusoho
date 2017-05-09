import DocPlayer from '../../../common/doc-player';

let $element = $('#document-content');
let watermarkUrl = $element.data('watermark-url');

if(watermarkUrl) {
  $.get(watermarkUrl, function(watermark) {
    console.log(watermark);
    initDocPlayer(watermark);
  });
}else {
  initDocPlayer('');
}

function initDocPlayer(contents) {
  // let doc = new DocPlayer({
  //   element: $element,
  //   swfUrl: $element.data('swf'),
  //   pdfUrl: $element.data('pdf'),
  //   watermarkOptions: {
  //     contents,
  //     xPosition: 'center',
  //     yPosition: 'center',
  //     rotate: 45,
  //   }
  // });
  let doc = new DocPlayerSDK({
      id: 'document-content',
      src: 'http://7xqnui.com1.z0.glb.clouddn.com/v7aes-Scalability/12eb6eca36dd6ce2_html',
      key: 'fkjwofojdjjiweio',
      iv: '4321876521096543'
      // iv: '1234567890123456'
    })
}
