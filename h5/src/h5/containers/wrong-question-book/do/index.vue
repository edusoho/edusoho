<template>
  <div>do</div>
</template>

<script>
import _ from 'lodash';
import Api from '@/api';

export default {
  name: 'WrongQuestionDo',

  data() {
    return {
      poolId: this.$route.query.id,
      questionList: [],
    };
  },

  created() {
    this.fetchQuestion();
  },

  methods: {
    fetchQuestion() {
      const params = _.assign({}, this.$route.query);
      delete params.id;
      Api.getWrongQuestionStartAnswer({
        query: {
          poolId: this.poolId,
        },
        data: params,
      }).then(res => {
        console.log(res);
      });
    },
  },
};
</script>
