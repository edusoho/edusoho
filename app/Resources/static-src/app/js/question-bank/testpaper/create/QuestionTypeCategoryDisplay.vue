<script>

export default {
  name: 'QuestionTypeCategoryDisplay',
  components: {}
}

</script>
<template>
  <div class="question-type-display">
    <div class="question-type-display-header">
      <div class="question-type-display-header-top">题型设置</div>
      <div class="question-type-display-header-normal">题目数量</div>
      <div class="question-type-display-header-normal">每题分值</div>
      <div class="question-type-display-header-bottom">
        <span class="question-type-display-header-bottom-title">合计</span>
        <span class="question-type-display-header-bottom-description">（题数/总分）</span>
      </div>
    </div>
    <div v-for="type in questionDisplayTypes">
      <div class="question-type-display-header-top">{{ type.name }}</div>
      <div class="question-type-display-cell-number">
                <span class="question-type-display-cell-number-edit">
                  <input type="number" value="0"/>
                </span>
        <span class="question-type-display-cell-number-total">/{{ questionCounts[type.type].total }}</span>
      </div>
      <div class="question-type-display-cell-score">
        <input type="number" value="2"/>
      </div>
      <div class="question-type-display-cell-sum">0 / 0.0</div>
    </div>
  </div>
</template>
