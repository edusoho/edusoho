<template>
  <edit-layout>
    <template #title>轮播图</template>

    <div class="design-editor">
      <div class="design-editor__title">添加内容</div>
      <div class="design-editor__image">

        <swiper-edit-item
          v-for="(item, index) in moduleData"
          :key="index"
          :index="index"
          :item="item"
          @update:image="showCropperModal"
        />

        <div class="add-btn-input">
          <a-upload
            accept="image/*"
            :file-list="[]"
            :customRequest="() => {}"
            @change="handleAddSwiper"
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
import _ from 'lodash';
import EditLayout from './EditLayout.vue';
import SwiperEditItem from './SwiperEditItem.vue';
import PictureCropperModal from 'app/vue/components/PictureCropperModal.vue';

export default {
  name: 'SwiperEdit',

  props: {
    moduleInfo: {
      type: Array,
      required: true
    }
  },

  components: {
    EditLayout,
    PictureCropperModal,
    SwiperEditItem
  },

  data() {
    return {
      moduleData: [],
      currentCropperIndex: 0,
      currentCropperType: ''
    }
  },

  watch: {
    moduleInfo: function() {
      this.moduleData = this.moduleInfo;
    }
  },

  mounted() {
    this.moduleData = this.moduleInfo;
  },

  methods: {
    handleAddSwiper(info) {
      const reader = new FileReader();

      reader.onload = (event) => {
        const params = {
          type: 'add',
          imgUrl: event.target.result,
          imgName: info.file.originFileObj.name
        };
        this.showCropperModal(params);
      };
      reader.readAsDataURL(info.file.originFileObj);
    },

    showCropperModal(params) {
      const { index, type, imgUrl, imgName } = params;
      this.currentCropperIndex = index;
      this.currentCropperType = type;
      this.$refs.pictureCropperModal.showModal({ imgUrl, imgName });
    },

    cropperSuccess(data) {
      const { url } = data;

      if (this.currentCropperType === 'add') {
        this.moduleData.push({
          image: url,
          link: {
            type: '',
            target: 'javascript:;',
            url: ''
          },
          responsive: '1'
        });
        this.upateEdit();
        return;
      }

      if (this.currentCropperType === 'edit') {
        this.moduleData[this.currentCropperIndex].image = url;
        this.upateEdit();
      }
    },

    upateEdit() {
      this.$emit('update:edit', {
        type: 'swiper',
        data: this.moduleData
      });
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
