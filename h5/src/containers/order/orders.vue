<template>
  <div class="orders">
    <span class='orders-title'>我的订单</span>
    <div class="orders-container__empty" v-if="isEmptyOrder && isFirstRequestCompile">
      <img src="/static/images/orderEmpty.png" >
      <span>暂无订单记录</span>
    </div>

    <div class="order" v-else>
      <e-course v-for="order in orderList" :key="order.id" :order="order"
       type="order"></e-course>
    </div>
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
    return {
      orderList: [],
      isEmptyOrder: true,
      isFirstRequestCompile: false
    }
  },
  created() {
    Api.getMyOrder().then((res) => {
      this.orderList = res.data;
      if (this.orderList.length) this.isEmptyOrder = false;
      this.isFirstRequestCompile = true
    })
  }
}
</script>
