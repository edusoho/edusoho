<template>
  <div class="item clearfix">
    <div class="item-cover-image float-left">
      <img src="/files/course/2020/08-12/093700c063c8447618.jpg" alt="">
    </div>
    <div class="item-info float-left">
      <h3 class="text-overflow">标题标题标题标题标题标题标题标题标题标题标题标题标题标题标题标题标题标题标题标题标题标题标题标题</h3>
      <p class="number">20 题</p>
    </div>
    <a-button
      type="primary"
      class="check-wrong-question float-right"
      @click="goToWrongQuestionDetail"
    >
      查看错题
    </a-button>
  </div>
</template>

<script>
export default {
  methods: {
    goToWrongQuestionDetail() {
      window.location.href = `/my/wrong_question_book/target_type/course/target_id/1`
    }
  }
}
</script>

<style lang="less" scoped>
.item {
  position: relative;
  padding: 8px 24px 8px 8px;
  margin-top: 16px;
  border: 1px solid #eee;

  .item-cover-image {
    margin-right: 24px;
    width: 227px;

    img {
      width: 100%;
    }
  }

  .item-info {
    width: 45%;

    .number {
      margin: 86px 0 0;
      color: #ff4040;
    }
  }

  .check-wrong-question {
    margin-top: 45px;
  }
}

@media (max-width: 767px) {
  .list-item {
    padding: 8px;

    .item-cover-image {
      width: 100%;
    }

    .item-info {
      margin-top: 16px;
      width: 100%;

      .number {
        margin: 8px 0 0;
      }
    }

    .check-wrong-question {
      margin-top: 16px;
      width: 100%;
    }
  }
}
</style>
