<template>
  <div class="order">
    <e-course type="confirmOrder" v-for="course in courses" :key="course.id" :course="course"></e-course>
    <div class="order-submit-bar">
      <span class='red'> 合计: 元 </span>
      <van-button class="primary-btn submit-btn" size="small">提交订单</van-button>
    </div>
  </div>
</template>
<script>
import eCourse from '@/containers/components/e-course/e-course.vue';

export default {
  components: {
    eCourse
  },
  data () {
    return {

    }
  }
}
</script>
