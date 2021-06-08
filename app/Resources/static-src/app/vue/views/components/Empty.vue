<template>
  <a-empty :description="description" :image="image" :image-style="imageStyle" />
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
