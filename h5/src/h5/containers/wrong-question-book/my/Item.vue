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
