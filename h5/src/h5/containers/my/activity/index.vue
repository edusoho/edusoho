<template>
  <div class="orders">
    <div v-if="list.length === 0 && isFirstRequestCompile" class="orders-container__empty">
      <img src="static/images/orderEmpty.png" >
      <span>暂无活动记录</span>
    </div>

    <div v-else class="activity">
      <van-list v-model="loading" :finished="finished" class="tab-list" @load="onLoad">
        <activity-item v-for="(item, index) in list" :key="index" :activity="item"/>
      </van-list>
    </div>
  </div>
</template>
<script>
import activityItem from './item'
import Api from '@/api'
import { Toast } from 'vant'

export default {
  components: {
    activityItem
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
  mounted() {
    this.onLoad()
  },
  methods: {
    onLoad() {
      const params = { offset: this.offset }
      Api.myActivities({ params }).then(({ data, paging }) => {
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
