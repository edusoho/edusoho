import LocalImageCrop from 'app/common/local-image/crop';

new LocalImageCrop({
  cropImg: '#classroom-crop',
  saveBtn: '#save-btn',
  selectBtn: '#select-btn',
  group: 'course',
  imgs: {
    large: [540, 304],
    middle: [354, 200],
    small: [219, 124],
  }
});

