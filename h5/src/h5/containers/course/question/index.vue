<template>
  <div class="question">
    <question-item v-for="question in 10" :key="question" />

    <div class="question-btn">
      <van-button type="primary" block>发起问答</van-button>
    </div>
  </div>
</template>

<script>
import QuestionItem from './components/QuestionItem.vue';

export default {
  name: 'Question',

  components: {
    QuestionItem
  }
}
</script>

<style lang="scss" scoped>
.question {
  padding-bottom: vw(80);

  &-btn {
    position: fixed;
    bottom: vw(16);
    left: 50%;
    transform: translateX(-50%);
    width: vw(340);

    .van-button {
      box-shadow: 0px 2px 6px 0px rgba(64, 143, 251, 0.5);
      border-radius: 8px;
      font-size: vw(16);
    }
  }
}
</style>
