<template>
  <div class="item clearfix">
    <div class="item-cover-image pull-left" @click="goToWrongQuestionDetail">
      <img v-if="question.target_data.cover" :src="question.target_data.cover.middle" alt="">
    </div>
    <div class="item-info pull-left">
      <h3 class="text-overflow title" @click="goToWrongQuestionDetail">{{ question.target_data.title | formatHtml }}</h3>
      <p class="number">{{ question.item_num }} 题</p>
    </div>
    <a-button
      type="primary"
      class="check-wrong-question pull-right"
      @click="goToWrongQuestionDetail"
    >
      查看错题
    </a-button>
  </div>
</template>

<script>
export default {
  props: {
    question: {
      type: Object,
      required: true
    }
  },

  methods: {
    goToWrongQuestionDetail() {
      const { target_type, id } = this.question;
      window.open(`/my/wrong_question_book/wrong_questions#/target_type/${target_type}/target_id/${id}`);
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
    cursor: pointer;

    img {
      width: 100%;
    }
  }

  .item-info {
    width: 45%;

    .title {
      display: inline-block;
      max-width: 100%;
      cursor: pointer;
    }
    .number {
      margin: 72px 0 0;
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
