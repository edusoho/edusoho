<template>
  <div class="course-detail__head">
    <img :src="courseSet.cover.large" alt="">
    <e-panel :title="courseSet.title" v-show="status !== 'after'">
      <div class="course-detail__head-price">
        <span>¥{{price}}</span>
        <span>{{courseSet.studentNum}}人在学</span>
      </div>
    </e-panel>
  </div>
</template>
<script>
export default {
  // props: ['courseSet', 'price', 'status']
  props: {
    courseSet: {
      type: Object,
      default: () => ({
        cover: ''
      })
    },
    price: {
      type: String,
      default: ''
    },
    status: {
      type: String,
      default: ''
    }
  }
}
</script>
