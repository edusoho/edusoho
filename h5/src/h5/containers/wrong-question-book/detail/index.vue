<template>
  <div>fdsf</div>
</template>

<script>
import { mapMutations } from 'vuex';
import * as types from '@/store/mutation-types';

export default {
  name: 'WrongQuestionBookDetail',

  data() {
    return {
      targetType: this.$route.params.type,
      targetId: this.$route.params.id,
    };
  },

  created() {
    this.setNavbarTitle(this.$route.query.title);
  },

  methods: {
    ...mapMutations({
      setNavbarTitle: types.SET_NAVBAR_TITLE,
    }),
  },
};
</script>
