<template>
  <div class="order">
    <span class='order-title'>我的订单</span>

    <div class="order-container__empty" v-if="empty">
      <img src="/static/images/orderEmpty.png" >
      <span>暂无订单记录</span>
    </div>
   
    <template v-else>
      <e-course v-for="course in courses" :key="course.id" :course="course"
       type="order"></e-course>
    </template>
  </div>
</template>
<script>
import eCourse from '@/containers/components/e-course/e-course';

export default {
  components: {
    eCourse
  },
  data() {
    return {
      empty: false,
      courses: [{
        courseSet: {
          cover: {
            large:'https://mp1.cg-dev.cn/files/default/2018/04-13/14081939db25426228.jpg'
          },
          title: '收费课程0412'
        },
        pay_amount: '1'
      }, {
        courseSet: {
          cover: {
            large:'https://mp1.cg-dev.cn/files/default/2018/04-13/14081939db25426228.jpg'
          },
          title: '收费课程0412'
        },
        pay_amount: '1'
      }]
    }
  },
  created() {
    this.courses.length > 0 ? this.empty = false : this.empty = true;
  }
}
</script>
