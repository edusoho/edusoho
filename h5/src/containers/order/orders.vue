<template>
  <div class="orders">
    <div class="orders-container__empty" v-if="list.length === 0 && isFirstRequestCompile">
      <img src="static/images/orderEmpty.png" >
      <span>暂无订单记录</span>
    </div>

    <div class="order" v-else>
      <van-list class="tab-list" v-model="loading" :finished="finished" @load="onLoad">
        <e-course v-for="order in list"
          :key="order.id"
          :order="order"
          type="order"
          :typeList="order.targetType"/>
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
      list: [],
      isFirstRequestCompile: false,
      loading: false,
      finished: false,
      offset: 0,
    }
  },
  created() {
  },
  methods: {
    onLoad() {
      const params = { offset: this.offset }
      Api.getMyOrder({params}).then(({data, paging}) => {
        this.isFirstRequestCompile = true;
        this.list = [...this.list, ...data];
        this.offset = this.list.length

        if (this.list.length == paging.total) {
          this.finished = true
        }
        this.loading = false
      }).catch(err => {
        Toast.fail(err.message)
        this.isFirstRequestCompile = true;
        this.loading = false
      });
    }
  }
}
</script>
