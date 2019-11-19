<template>
  <div class="orders">
    <div v-if="list.length === 0 && isFirstRequestCompile" class="orders-container__empty">
      <img src="static/images/orderEmpty.png" >
      <span>暂无订单记录</span>
    </div>

    <div v-else class="order">
      <van-list v-model="loading" :finished="finished" class="tab-list" @load="onLoad">
        <e-course
          v-for="order in list"
          :key="order.id"
          :order="order"
          :type-list="order.targetType"
          type="order"/>
      </van-list>
    </div>
  </div>
</template>
<script>
import eCourse from '&/components/e-course/e-course'
import Api from '@/api'
import { Toast } from 'vant'

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
      offset: 0
    }
  },
  created() {
  },
  methods: {
    onLoad() {
      const params = { offset: this.offset }
      Api.getMyOrder({ params }).then(({ data, paging }) => {
        this.isFirstRequestCompile = true
        this.list = [...this.list, ...data]
        this.offset = this.list.length

        if (this.list.length == paging.total) {
          this.finished = true
        }
        this.loading = false
      }).catch(err => {
        Toast.fail(err.message)
        this.isFirstRequestCompile = true
        this.loading = false
      })
    }
  }
}
</script>
