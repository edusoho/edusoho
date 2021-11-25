<template>
  <edit-layout>
    <template #title>轮播图</template>

    <div class="design-editor">
      <div class="design-editor__title">添加内容</div>
      <div class="design-editor__image">

        <div class="image-item clearfix">
          <div class="image-item__img pull-left">
            <img src="" alt="">
            <a-upload
              accept="image/*"
              :file-list="[]"
              :customRequest="() => {}"
              @change="uploadPictureEdit"
            >
              <div class="re-upload">修改</div>
            </a-upload>
          </div>
          <div class="image-item__content pull-left">
            <a-dropdown>
              <a class="ant-dropdown-link" @click="e => e.preventDefault()">
                选择跳转到的页面<a-icon type="down" />
              </a>
              <a-menu slot="overlay" @click="onClick">
                <a-menu-item key="1">
                  选择课程
                </a-menu-item>
                <a-menu-item key="2">
                  选择班级
                </a-menu-item>
                <a-menu-item key="3">
                  选择会员
                </a-menu-item>
                <a-menu-item key="4">
                  自定义链接
                </a-menu-item>
              </a-menu>
            </a-dropdown>
          </div>
        </div>

        <div class="add-btn-input">
          <a-upload
            accept="image/*"
            :file-list="[]"
            :customRequest="() => {}"
            @change="handleChangeAdd"
          >
            <div class="add-btn-input">
              +添加图片
            </div>
          </a-upload>
        </div>

        <div class="image-tips">·建议图片尺寸为750x300px，支持 jpg/png/gif 格式，大小不超过2MB</div>
        <div class="image-tips">·最多添加5个图片，拖动选中的图片可对其排序</div>
      </div>
    </div>

    <picture-cropper-modal
      ref="pictureCropperModal"
      :aspect-ratio="5 / 2"
      @success="cropperSuccess"
    />
  </edit-layout>
</template>

<script>
import EditLayout from './EditLayout.vue';
import PictureCropperModal from 'app/vue/components/PictureCropperModal.vue';

export default {
  name: 'SwiperEdit',

  components: {
    EditLayout,
    PictureCropperModal
  },

  methods: {
    handleChangeAdd(info) {
      const reader = new FileReader();

      reader.onload = (event) => {
        const imgUrl = event.target.result;
        const imgName = info.file.originFileObj.name;
        this.$refs.pictureCropperModal.showModal({ imgUrl, imgName });
      };
      reader.readAsDataURL(info.file.originFileObj);
    },

    cropperSuccess(data) {
      const { url } = data;
    }
  }
}
</script>

<style lang="less" scoped>
.design-editor {
  &__title {
    margin-bottom: 10px;
    color: #a3a0a0;
  }

  &__image {
    width: 100%;
    padding: 8px;
    background: rgba(237, 237, 237, 0.53);

    .image-item {
      padding: 10px 6px;
      margin-bottom: 10px;
      width: 100%;
      border-radius: 2px;
      border: 1px solid #eee;
      background-color: #fff;
      font-size: 12px;
      cursor: move;

      &__img {
        position: relative;
        margin-right: 10px;
        width: 150px;
        height: 60px;
        border-radius: 2px;

        img {
          width: 100%;
        }

        .re-upload {
          position: absolute;
          bottom: 0;
          left: 0;
          width: 100%;
          height: 20px;
          line-height: 20px;
          cursor: pointer;
          opacity: 0.5;
          font-size: 12px;
          text-align: center;
          color: #fff;
          background: black;
        }
      }
    }

    .add-btn-input {
      width: 100%;
      height: 74px;
      line-height: 74px;
      cursor: pointer;
      text-align: center;
      background-color: #fff;

      /deep/ .ant-upload {
        color: #31a1ff;
        width: 100%;
      }
    }

    .image-tips {
      margin-top: 10px;
      font-size: 12px;
      color: #888;
    }
  }
}
</style>
