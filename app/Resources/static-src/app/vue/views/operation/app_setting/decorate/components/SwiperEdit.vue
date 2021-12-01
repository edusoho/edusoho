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
          @update-image="showCropperModal"
          @select-link="handleSelectLink"
          @remove="handleClickRemove"
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

    <custom-link-modal ref="customLink" @update-link="handleUpdateLink" />
    <course-link-modal ref="courseLink" @update-link="handleUpdateLink" />
    <classroom-link-modal ref="classroomLink" @update-link="handleUpdateLink" />
  </edit-layout>
</template>

<script>
import _ from 'lodash';
import EditLayout from './EditLayout.vue';
import SwiperEditItem from './SwiperEditItem.vue';
import PictureCropperModal from 'app/vue/components/PictureCropperModal.vue';
import CustomLinkModal from './CustomLinkModal.vue';
import CourseLinkModal from './CourseLinkModal.vue';
import ClassroomLinkModal from './ClassroomLinkModal.vue';

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
    SwiperEditItem,
    PictureCropperModal,
    CustomLinkModal,
    CourseLinkModal,
    ClassroomLinkModal
  },

  data() {
    return {
      moduleData: [],
      currentIndex: 0,
      currentType: ''
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
      this.currentIndex = index;
      this.currentType = type;
      this.$refs.pictureCropperModal.showModal({ imgUrl, imgName });
    },

    cropperSuccess(data) {
      if (this.currentType === 'add') {
        this.moduleData.push({
          image: data,
          link: {
            type: '',
            target: null,
            url: 'javascript:;'
          }
        });
        this.upateEdit();
        return;
      }

      if (this.currentType === 'edit') {
        this.moduleData[this.currentIndex].image = data;
        this.upateEdit();
      }
    },

    handleClickRemove(params) {
      const { index } = params;
      this.moduleData.splice(index, 1);
      this.upateEdit();
    },

    handleSelectLink(params) {
      const { type, index } = params;
      this.currentIndex = index;
      if (type === 'vip') {
        const params = {
          target: null,
          type: 'vip',
          url: ''
        };
        this.handleUpdateLink(params);
        return;
      }

      if (type === 'custom') {
        this.$refs.customLink.showModal();
        return;
      }

      if (type === 'course') {
        this.$refs.courseLink.showModal();
        return;
      }

      if (type === 'classroom') {
        this.$refs.classroomLink.showModal();
        return;
      }
    },

    handleUpdateLink(params) {
      this.moduleData[this.currentIndex].link = params;
      this.upateEdit();
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
      height: 54px;
      line-height: 54px;
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
