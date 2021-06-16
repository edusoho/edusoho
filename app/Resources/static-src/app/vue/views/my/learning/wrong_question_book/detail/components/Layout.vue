<template>
  <a-row>
    <a-col :xs="4" :sm="2" style="text-align: center;">
      <div class="order">1</div>
      <a-tag style="margin-right: 0;">2 分</a-tag>
    </a-col>
    <a-col :xs="20" :sm="22">
      <div class="topic mb16">科目汇总表的总范围是?</div>

      <div class="mb16">
        <slot name="answer" />
      </div>

      <div class="reference-answer">
        <span>参考答案：</span>
        <div>正确答案是C</div>
      </div>

      <div class="result">
        <span>答题结果：</span>
        <div>正确答案是C</div>
      </div>

      <div>
        <span>答题结果：</span>
        <div>正确答案是C</div>
      </div>
    </a-col>
  </a-row>
</template>

<style lang="less" scoped>
.mb16 {
  margin-bottom: 16px;
}

.topic {
}

.reference-answer {

}
.analysis {
  padding: 16px 26px;
  background: rgba(70, 195, 123, 0.05);
  border-radius: 4px;
}
</style>
