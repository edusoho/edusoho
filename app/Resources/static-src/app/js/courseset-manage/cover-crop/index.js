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

  <img class="img-full" src="" id="course-crop"/>
    <button type="button" class="btn cd-btn cd-btn-ghost-default cd-btn-lg" id="select-btn">
      重新选择
    </button>
    <button type="button" class="btn cd-btn cd-btn-primary cd-btn-lg" id="save-btn"
      data-loading-text="正在提交...">
      保存图片
    </button>
  </div>
</div>
          </div>