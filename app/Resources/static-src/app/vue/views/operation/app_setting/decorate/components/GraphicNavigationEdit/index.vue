<template>
  <edit-layout>
    <template #title>图文导航设置</template>

    <div class="design-editor">
      <div class="design-editor__item" v-for="(item, index) in moduleData" :key="index">
        <item
          :item="item"
          :index="index"
          @modity="handleModity"
        />
      </div>

      <div class="design-editor__item" v-if="moduleData.length < 8">
        <a-button type="primary" block @click="handleClickAdd">
          添加图文导航
        </a-button>
      </div>
    </div>

    <modity-image-modal
      ref="modal"
      @update-image="handleUpdateImage"
      @upload-image="handleUploadImage"
    />

    <picture-cropper-modal
      ref="pictureCropperModal"
      :aspect-ratio="1 / 1"
      @success="cropperSuccess"
    />
  </edit-layout>
</template>

<script>
import _ from 'lodash';
import EditLayout from '../EditLayout.vue';
import Item from './Item.vue';
import ModityImageModal from './ModityImageModal.vue';
import PictureCropperModal from 'app/vue/components/PictureCropperModal.vue';

export default {
  name: 'GraphicNavigationEdit',

  props: {
    moduleData: {
      type: Array,
      required: true
    }
  },

  components: {
    EditLayout,
    Item,
    ModityImageModal,
    PictureCropperModal
  },

  data() {
    return {
      currentIndex: null
    }
  },

  methods: {
    handleClickAdd() {
      const params = {
        title: '',
        image: {},
        link: {}
      };

      this.update({ key: 'add', value: params });
    },

    handleModity(params) {
      const { index, type, value } = params;
      this.currentIndex = index;

      if (type === 'image') {
        this.$refs.modal.showModal();
        return;
      }

      this.update({
        key: type,
        value
      });
    },

    handleUpdateImage({ url }) {
      const params = {
        key: 'image',
        value: { url }
      };
      this.update(params);
    },

    handleUploadImage(params) {
      this.$refs.pictureCropperModal.showModal(params);
    },

    cropperSuccess(data) {
      this.handleUpdateImage({ url: data.url });
    },

    update(params) {
      this.$emit('update-edit', {
        type: 'graphic_navigation',
        index: this.currentIndex,
        ...params
      });
    }
  }
}
</script>
