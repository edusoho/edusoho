import LocalImageCrop from  'app/common/local-image/crop';

let isHorizontal = 'horizontal' === $('[name=styleType]').val();

new LocalImageCrop({
  cropImg: '#basemap-crop',
  saveBtn: '#save-btn',
  selectBtn: '#select-btn',
  group: 'system',
  imgs: {
    large: isHorizontal ? [3600, 2600] : [2600, 3600],
  },
  uploadInput: '.basemap-upload .js-upload-input',
});
