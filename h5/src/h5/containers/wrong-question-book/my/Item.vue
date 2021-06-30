<template>
  <div class="list-item" @click="goToWrongQuestionDetail">
    <div class="list-item__image">
      <img :src="question.target_data.cover.middle" />
    </div>
    <div class="list-item__detail">
      <h3 class="title text-overflow">{{ question.target_data.title }}</h3>
      <p class="number text-overflow">{{ question.item_num }}道题</p>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    question: {
      type: Object,
      required: true,
    },
  },

  methods: {
    goToWrongQuestionDetail() {
      this.$router.push({
        name: 'myWrongQuestionBookDetail',
        params: {
          type: this.question.target_type,
          id: this.question.id,
        },
        query: {
          title: this.question.target_data.title,
        },
      });
    },
  },
};
</script>

<style lang="scss" scoped>
@import '../../../assets/styles/mixins.scss';

.list-item {
  display: flex;
  margin-bottom: vw(8);

  &__image {
    width: vw(128);
    height: vw(72);
    margin-right: vw(12);

    img {
      width: 100%;
      height: 100%;
    }
  }

  &__detail {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    width: vw(200);

    .title {
      width: 200px;
      margin: 0;
      font-size: 14px;
      font-weight: 500;
      color: #333;
      line-height: 20px;
    }

    .number {
      margin-top: vw(8);
      font-size: 14px;
      color: #ff4040;
      line-height: 20px;
    }
  }
}
</style>
