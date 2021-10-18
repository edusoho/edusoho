<template>
  <layout>
    <template #title>
      <div class="clearfix">
        <div class="pull-left">xxx / xxx</div>
        <div class="pull-right tips">数据来源于直播间，自动在直播结束一天同步</div>
      </div>
    </template>

    <a-row>
      <a-col :span="6">
        col-6
      </a-col>
      <a-col :span="12">
        col-12
      </a-col>
      <a-col :span="6">
        col-6
      </a-col>
    </a-row>
  </layout>
</template>

<script>
import Layout from '../layout.vue';

export default {
  name: 'CourseManageLiveStatisticsDetails',

  components: {
    Layout
  }
}
</script>

<style lang="less" scoped>
.tips {
  font-size: 14px;
  color: #999;
}
</style>
