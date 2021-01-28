import LocalImageCrop from  'app/common/local-image/crop';

new LocalImageCrop({
  cropImg: '#stamp-crop',
  saveBtn: '#save-btn',
  selectBtn: '#select-btn',
  group: 'system',
  imgs: {
    large: [650, 650],
  },
  uploadInput: '.stamp-upload .js-upload-input',
  fileInput: '.js-stamp-value',
});
