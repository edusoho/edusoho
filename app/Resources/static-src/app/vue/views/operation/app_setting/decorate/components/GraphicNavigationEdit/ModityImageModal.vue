<template>
  <a-modal
    :title="'decorate.select_image' | trans"
    :visible="visible"
    @cancel="handleCancel"
  >
    <div class="gn-image-list">
      <img
        v-for="(item, index) in images"
        :key="index"
        @click="handleClickSeleted(`/static-dist/app/img/vue/decorate/${item}`)"
        :src="`/static-dist/app/img/vue/decorate/${item}`"
      >
    </div>
    <template #footer>
      <a-button @click="handleCancel">{{ 'site.cancel' | trans }}</a-button>
      <upload-image :aspect-ratio="1 / 1" @success="handleUploadSuccess" />
    </template>
  </a-modal>
</template>
<script>
import _ from 'lodash';
import UploadImage from 'app/vue/components/UploadFile/Image.vue';

const images = [
  'gn_classroom.png',
  'gn_opencourse.png',
  'gn_class.png',
  'gn_vip.png',
  'gn_collection.png',
  'gn_hot.png',
  'gn_live.png',
  'gn_question.png',
  'gn_wrong.png'
];

export default {
  name: 'ModityImageModal',

  components: {
    UploadImage
  },

  data() {
    return {
      visible: false,
      images
    }
  },

  methods: {
    showModal() {
      _.assign(this, {
        visible: true
      });
    },

    handleCancel() {
      this.visible = false;
    },

    handleClickSeleted(value) {
      this.$emit('update-image', {
        uri: `${window.location.origin}${value}`
      });
      this.visible = false;
    },

    handleUploadSuccess(data) {
      this.$emit('upload-image', data);
      this.visible = false;
    }
  }
};
</script>

<style lang="less" scoped>
.gn-image-list {
  padding: 0 20px;

  img {
    overflow: hidden;
    margin-right: 8px;
    width: 40px;
    height: 40px;
    border-radius: 16px;
    cursor: pointer;

    &:last-child {
      margin-right: 0;
    }
  }
}
</style>
