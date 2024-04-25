const QRCode = require('qrcode');
const appQrcode = document.querySelector(".qrcode-canvas");
const { origin } = window.location;

const currentURL = window.location.href;

const courseId = $('input[name=courseId]').val();

$.get({
    url: "/api/pages/h5/courses/"+courseId,
    headers: {
      'Accept': 'application/vnd.edusoho.v2+json'
    }
  }).then(function(data) {
    const goodsId = data.goodsId;
    QRCode.toCanvas(
        appQrcode, `${origin}/mobile/downloadMiddlePage?courseId=${courseId}&goodsId=${goodsId}`,
        {
         width:190,
         height:190,
        }
    )
  });

