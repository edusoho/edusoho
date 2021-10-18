<template>
  <layout>
    <template #title>
      <div class="clearfix">
        <div class="pull-left">xxx / xxx</div>
        <div class="pull-right tips">数据来源于直播间，自动在直播结束一天同步</div>
      </div>
    </template>
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
