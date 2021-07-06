<template>
  <div class="wrong-question-result">
    <div class="result-data">
      <div class="result-data__item">
        正确率
        <div class="result-data__bottom data-number-green">
          <span class="data-number">10</span>%
        </div>
      </div>
      <div class="result-data__item">
        做题用时
        <div class="result-data__bottom data-number-gray">
          <span class="data-number">3</span>分钟
        </div>
      </div>
    </div>

    <div class="result-tag">
      <div class="result-tag-item clearfix">
        <div class="result-tag-item__circle circle-green" />
        正确
      </div>
      <div class="result-tag-item clearfix">
        <div class="result-tag-item__circle circle-orange" />
        错误
      </div>
      <div class="result-tag-item clearfix">
        <div class="result-tag-item__circle circle-gray" />
        未作答
      </div>
    </div>

    <div class="result-panel">
      <ul class="result-list">
        <li :class="['result-list__item testpaper-number', `circle-green`]">
          2
        </li>
      </ul>
    </div>

    <div class="result-footer">
      <van-button
        class="result-footer__btn"
        type="primary"
        @click="viewAnalysis()"
        >查看解析</van-button
      >
    </div>
  </div>
</template>

<script>
export default {
  name: 'WrongQuestionResult',
  data() {},
  beforeRouteEnter(to, from, next) {
    document.getElementById('app').style.background = '#f6f6f6';
    next();
  },
  beforeRouteLeave(to, from, next) {
    document.getElementById('app').style.background = '';
    next();
  },
};
</script>
