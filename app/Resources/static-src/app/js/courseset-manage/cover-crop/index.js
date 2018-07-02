import LocalImageCrop from  'app/common/local-image/crop';

new LocalImageCrop({
  cropImg: '#course-crop',
  saveBtn: '#save-btn',
  selectBtn: '#select-btn',
  group: 'course',
  imgs: {
    large: [480, 270],
    middle: [304, 171],
    small: [96, 54],
  }
});

