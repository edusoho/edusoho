import QrcodeImageCrop from  'app/common/local-image/qrcode-crop';

new QrcodeImageCrop({
  cropImg: '#qrcode-crop',
  saveBtn: '#qrcode-save-btn',
  selectBtn: '#qrcode-select-btn',
  group: 'user',
  imgs: {
    large: [200, 200]
  }
});
