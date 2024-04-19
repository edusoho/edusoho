const QRCode = require('qrcode')
const appQrcode = document.querySelector(".qrcode-canvas")
const { origin } = window.location

QRCode.toCanvas(
    appQrcode, `${origin}/mobile/downloadMiddlePage`,
    {
     width:190,
     height:190,
    }
)