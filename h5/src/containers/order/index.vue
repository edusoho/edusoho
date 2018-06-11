<template>
  <div class="order">
    <e-course type="confirmOrder" v-for="course in courses" :key="course.id" :course="course"></e-course>
    <div class="order-submit-bar">
      <span class='red'> 合计: {{ courses[0].totalPrice }}元 </span>
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
      courses: [{
        courseSet: {
          cover: {
            large:'https://mp1.cg-dev.cn/files/default/2018/04-13/14081939db25426228.jpg'
          },
        },
        totalPrice: '1',
        title: '收费课程0412',
        targetId: 1
      }]
    }
  },

}
</script>
