<template>
  <e-panel title="课程目录" class="directory layout-col">
    <!-- 暂无学习任务 -->
    <van-collapse v-model="activeNames" accordion>
      <van-collapse-item title="有赞微商城" name="1">
        提供多样店铺模板，快速搭建网上商城
      </van-collapse-item>
      <van-collapse-item title="有赞零售" name="2">
        网店吸粉获客、会员分层营销、一机多种收款，告别经营低效和客户流失
      </van-collapse-item>
      <van-collapse-item title="有赞美业" name="3">
        线上拓客，随时预约，贴心顺手的开单收银
      </van-collapse-item>
      <van-collapse-item title="有赞微商城" name="1">
        提供多样店铺模板，快速搭建网上商城
      </van-collapse-item>
      <van-collapse-item title="有赞零售" name="2">
        网店吸粉获客、会员分层营销、一机多种收款，告别经营低效和客户流失
      </van-collapse-item>
      <van-collapse-item title="有赞美业" name="3">
        线上拓客，随时预约，贴心顺手的开单收银
      </van-collapse-item>
    </van-collapse>

  </e-panel>
</template>
<script>
  import { Collapse, CollapseItem } from 'vant';

  export default {
    data() {
      return {
        activeNames: ['1']
      }
    }
  }
</script>
