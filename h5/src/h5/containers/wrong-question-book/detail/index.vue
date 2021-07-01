<template>
  <div>
    <question
      v-for="(question, index) in questionList"
      :key="question.id + index"
      :question="question"
    />
  </div>
</template>

<script>
import { mapMutations } from 'vuex';
import * as types from '@/store/mutation-types';
import Api from '@/api';
import Question from '../components/Question.vue';

export default {
  name: 'WrongQuestionBookDetail',

  components: {
    Question,
  },

  data() {
    return {
      targetType: this.$route.params.type,
      targetId: this.$route.params.id,
      questionList: [],
    };
  },

  created() {
    this.setNavbarTitle(this.$route.query.title);
    this.fetchWrongQuestion();
  },

  methods: {
    ...mapMutations({
      setNavbarTitle: types.SET_NAVBAR_TITLE,
    }),

    fetchWrongQuestion() {
      Api.getWrongBooksQuestionShow({
        query: {
          poolId: this.targetId,
        },
        params: {
          targetType: this.targetType,
        },
      }).then(res => {
        this.questionList = res.data;
      });
    },
  },
};
</script>
