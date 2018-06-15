<template>
  <div class="course-detail__head">
    <img :src="courseSet.cover.large" alt="">
    <e-panel :title="courseSet.title">
      <div class="course-detail__head-price">
        <span>¥{{price}}</span>
        <span>{{courseSet.studentNum}}人在学</span>
      </div>
    </e-panel>
  </div>
</template>
<script>
export default {
  props: ['courseSet', 'price']
}
</script>
