<template>
  <a-empty
    class="custom-empty"
    :image="image"
    :image-style="imageStyle"
  >
    <span class="custom-empty__description" slot="description">{{ description }}</span>
  </a-empty>
</template>

<script>
export default {
  name: 'Empty',

  props: {
    description: {
      type: String,
      default: '暂无数据'
    },

    image: {
      type: String,
      default: '/static-dist/app/img/vue/empty.png'
    },

    imageStyle: {
      type: Object,
      default() {
        return {
          height: '200px'
        }
      }
    }
  }
}
</script>

<style lang="less">
.custom-empty {
  position: relative;
  margin-top: 60px;

  .custom-empty__description {
    position: absolute;
    bottom: 60px;
    left: 50%;
    margin: 0;
    transform: translateX(-50%);
    color: #bebebe;
  }
}
</style>
