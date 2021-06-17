<template>
  <a-row>
    <a-col :xs="4" :sm="2" style="text-align: center;">
      <div class="order">1.</div>
      <a-tag style="margin-right: 0;">2 分</a-tag>
    </a-col>

    <a-col :xs="20" :sm="22">
      <div class="keyword mb-space">科目汇总表的总范围是?</div>

      <div class="mb-space prevent-click">
        <slot name="answer" />
      </div>

      <a-divider style="margin: 16px 0;" />

      <div class="clearfix mb-space">
        <div class="pull-left keyword">参考答案：</div>
        <div class="pull-left result">正确答案是C</div>
      </div>

      <div class="clearfix mb-space">
        <div class="pull-left keyword">答题结果：</div>
        <div class="pull-left result">正确答案是<span class="right">C</span></div>
      </div>

      <div class="clearfix mb-space">
        <div class="pull-left keyword">答题结果：</div>
        <div class="pull-left result">正确<span class="error">答案是C</span></div>
      </div>

      <div class="clearfix analysis">
        <div class="pull-left keyword" style="text-indent: 2em;">解析：</div>
        <div class="pull-left result">正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C正确答案是C</div>
      </div>

      <div v-if="showSituation" class="clearfix situation">
        <div class="pull-left right mt-space situation__item">来源：计划1-考试任务、作业任务</div>

        <div class="pull-right clearfix situation__item">
          <div class="pull-left mt-space frequency">做错频次：<span class="error">3</span>次</div>
          <div class="pull-left mt-space time">错题时间：2021-04-15 20:20:00</div>
        </div>
      </div>
    </a-col>
  </a-row>
</template>

<script>
export default {
  props: {
    showSituation: {
      type: Boolean,
      default: true
    }
  }
}
</script>

<style lang="less" scoped>
.prevent-click {
  pointer-events: none;
}

.mb-space {
  margin-bottom: 16px;
}

.mt-space {
  margin-top: 16px;
}

.right {
  color: #46c37b;
}

.error {
  color: #ff5c3b;
}

.keyword {
  color: #333;
}

.result {
  width: calc(100% - 72px);
  color: #666;
}

.analysis {
  padding: 16px 26px 16px 0;
  background: rgba(70, 195, 123, 0.05);
  border-radius: 4px;
}

.situation {

  .frequency {
    margin-right: 40px;
  }
}

@media (max-width: 767px) {
  .mb-space {
    margin-bottom: 8px;
  }

  .mt-space {
    margin-top: 8px;
  }

  .analysis {
    padding: 8px 16px 6px 0;
  }

  .situation {
    .situation__item,
    .frequency,
    .time {
      float: none !important;
    }

    .frequency {
      margin-right: 0;
    }
  }
}
</style>
