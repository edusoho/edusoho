<template>
  <div class="orders">
    <span class='orders-title'>我的订单</span>

    <div class="orders-container__empty">
      <img src="/static/images/orderEmpty.png" >
      <span>暂无订单记录</span>
    </div>

    <template>
      <!-- <e-course v-for="course in courses" :key="course.id" :course="course"
       type="order"></e-course> -->
    </template>
  </div>
</template>
<script>
import eCourse from '@/containers/components/e-course/e-course';
import Api from '@/api';

export default {
  components: {
    eCourse
  },
  data() {
    return {}
  },
  created() {
    Api.getMyOrder().then(res => {
      console.log(res, 'orders');
    })
  }
}
</script>
