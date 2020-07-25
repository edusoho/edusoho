<template>
  <div class="">
    <e-loading v-if="isLoading" />
    <component :is="currentComp"></component>
  </div>
</template>

<script>
import joinAfter from './joinAfter';
import joinBefore from './joinBefore';
import { mapState, mapActions } from 'vuex';
export default {
  components: {},
  data() {
    return {
      currentComp: '',
    };
  },
  computed: {
    ...mapState('ItemBank', {
      isMember: state => state.ItemBankExercise.isMember,
    }),
    ...mapState({
      isLoading: state => state.isLoading,
    }),
  },
  watch: {
    isMember: {
      handler: 'joinStatusChange',
      // immediate: true,
      deep: true,
    },
  },
  created() {
    this.getData();
  },
  methods: {
    ...mapActions('ItemBank', ['setItemBankExercise']),
    getData() {
      const id = Number(this.$route.params.id);
      if (id) {
        this.setItemBankExercise(id);
      }
    },
    joinStatusChange(status) {
      this.currentComp = '';
      if (status) {
        this.currentComp = joinAfter;
      } else {
        this.currentComp = joinBefore;
      }
    },
  },
};
</script>
