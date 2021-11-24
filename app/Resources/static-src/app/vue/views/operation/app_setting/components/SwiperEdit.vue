<template>
  <edit-layout>
    <template #title>轮播图</template>

    <div class="design-editor">
      <div class="design-editor__title">添加内容</div>
      <div class="design-editor__image">

        <div class="image-item">

        </div>

        <div class="add-btn-input" @click="handleClickAdd">
          +添加图片
        </div>

        <div class="image-tips">·建议图片尺寸为750x300px，支持 jpg/png/gif 格式，大小不超过2MB</div>
        <div class="image-tips">·最多添加5个图片，拖动选中的图片可对其排序</div>
      </div>
    </div>
  </edit-layout>
</template>

<script>
import EditLayout from './EditLayout.vue';

export default {
  name: 'SwiperEdit',

  components: {
    EditLayout
  },

  methods: {
    handleClickAdd() {
      this.$emit('update:data', );
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
      color: #31a1ff;
      cursor: pointer;
      text-align: center;
      background-color: #fff;
    }

    .image-tips {
      margin-top: 10px;
      font-size: 12px;
      color: #888;
    }
  }
}
</style>
