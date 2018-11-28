<template>
  <div class="orders">
    <span class='orders-title'>我的订单</span>
    <div class="orders-container__empty" v-if="isEmptyOrder && isFirstRequestCompile">
      <img src="static/images/orderEmpty.png" >
      <span>暂无订单记录</span>
    </div>

    <div class="order" v-else>
      <van-list style="padding-bottom: 40px; margin-top: 0;" v-model="loading" :finished="finished" @load="onLoad">
        <e-course v-for="order in orderList" :key="order.id" :order="order"
          type="order" :typeList="order.targetType"></e-course>
      </van-list>
    </div>
  </div>
</template>
<script>
import eCourse from '@/containers/components/e-course/e-course';
import Api from '@/api';
import { Toast } from 'vant';

export default {
  components: {
    eCourse
  },
  data() {
    return {
      orderList: [],
      isEmptyOrder: true,
      isFirstRequestCompile: false,
      loading: false,
      finished: false,
    }
  },
  created() {
  },
  methods: {
    onLoad() {
      const params = { offset: this.offset }
      Api.getMyOrder({params}).then(({data, paging}) => {
        this.orderList = [...this.orderList, ...data];
        this.offset = this.orderList.length

        if (this.orderList.length) this.isEmptyOrder = false;

        if (this.orderList.length == paging.total) {
          this.finished = true
        }
        this.loading = false
      }).catch(err => {
        Toast.fail(err.message)
        this.loading = false
      });
    }
  }
}
</script>
