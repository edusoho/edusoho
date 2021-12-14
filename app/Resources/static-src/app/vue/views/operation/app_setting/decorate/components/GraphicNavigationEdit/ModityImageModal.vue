<template>
  <a-modal
    title="选择图片"
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
      <a-button @click="handleCancel">取消</a-button>
      <a-upload
        accept="image/*"
        :file-list="[]"
        :customRequest="() => {}"
        @change="handleUploadImage"
      >
        <a-button type="primary">上传图片</a-button>
      </a-upload>
    </template>
  </a-modal>
</template>
<script>
import _ from 'lodash';

const images = [
  'gn_classification.png',
  'gn_free.png',
  'gn_collection.png',
  'gn_hot.png',
  'gn_live.png',
  'gn_question.png',
  'gn_vip.png',
  'gn_wrong.png'
];

export default {
  name: 'ModityImageModal',

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
        url: value
      });
      this.visible = false;
    },

    handleUploadImage(info) {
      const reader = new FileReader();

      reader.onload = (event) => {
        const params = {
          imgUrl: event.target.result,
          imgName: info.file.originFileObj.name
        };
        this.$emit('upload-image', params);
        this.visible = false;
      };
      reader.readAsDataURL(info.file.originFileObj);
    }
  }
};
</script>

<style lang="less" scoped>
.gn-image-list {
  padding: 0 20px;

  img {
    overflow: hidden;
    margin-right: 16px;
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
