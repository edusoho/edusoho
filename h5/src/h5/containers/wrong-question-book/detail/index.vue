<template>
  <div>fdsf</div>
</template>

<script>
import { mapMutations } from 'vuex';
import * as types from '@/store/mutation-types';
import Api from '@/api';

export default {
  name: 'WrongQuestionBookDetail',

  data() {
    return {
      targetType: this.$route.params.type,
      targetId: this.$route.params.id,
      wrongQuestionList: [],
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
        this.wrongQuestionList = res.data;
      });
    },
  },
};
</script>
