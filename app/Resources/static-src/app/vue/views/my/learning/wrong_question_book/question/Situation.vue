<template>
  <div class="clearfix situation">
    <div class="pull-left situation-left">来源：计划1-考试任务、作业任务</div>

    <div class="pull-right clearfix situation-right">
      <div class="pull-left frequency">
        做错频次：
        <span class="frequency-error">3 </span>次
      </div>
      <div class="pull-left time">错题时间：2021-04-15 20:20:00</div>
    </div>
  </div>
</template>

<style lang="less" scoped>
.situation {
  margin-top: 16px;
  color: #666;

  .situation-left {
    color: #46c37b;
  }

  .frequency {
    margin-right: 40px;

    .frequency-error {
      color: #ff5c3b;
    }
  }
}

@media (max-width: 767px) {
  .situation {
    margin-top: 8px;

    .frequency {
      margin-right: 0;
    }

    .situation-left,
    .situation-right,
    .frequency,
    .time {
      float: none !important;
    }
  }
}
</style>

