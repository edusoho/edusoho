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
import Api from '@/api';
import { Toast } from 'vant';
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
    this.getDataItemBank();
  },
  methods: {
    ...mapActions('ItemBank', [
      'setItemBankExercise',
      'getDataItemBankReviews',
    ]),
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
    // 获取题库数据
    getDataItemBank() {
      const targetId = Number(this.$route.params.id);
      const targetType = 'item_bank_exercise';
      this.getDataItemBankReviews({ targetId, targetType });
    },
  },
};
</script>
