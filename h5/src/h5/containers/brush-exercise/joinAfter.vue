<template>
  <div>
  <div class="brush-exercise-detail-bank">
    <img :src="cover.large" class="brush-exercise-cover" />
    <van-tabs v-model="active" sticky>
      <van-tab :title="$t('questionBank.intro')">
        <introduction></introduction>
      </van-tab>
      <van-tab :title="$t('questionBank.catalogue')">
        <directory :exerciseId="Number(id)" />
      </van-tab>
      <van-tab :title="$t('questionBank.comment')" v-if="show_question_bank_review == 1">
        <review-list
          ref="review"
          :title="$t('questionBank.comment')"
          :defaul-value="$t('questionBank.noContent')"
          type="item_bank_exercise"
        />
      </van-tab>
    </van-tabs>
  </div>
  <div class="footer">
    <closedFixed v-if="ItemBankExercise.status == 'closed'" :isJoin="true" :title="$t('closed.exerciseTitle')" :content="$t('closed.exerciseContent')" />
  </div>
</div>
</template>

<script>
import { createNamespacedHelpers } from 'vuex';
import { Dialog } from 'vant';
import directory from './directory';
import reviewList from './review-list';
import introduction from './introduction';
import closedFixed from '@/components/closed-fixed.vue'

const { mapState } = createNamespacedHelpers('ItemBank');
export default {
  components: {
    directory,
    reviewList,
    introduction,
    closedFixed
  },
  props: ['details'],
  data() {
    return {
      active: 1,
      show_question_bank_review: this.$store.state.goods
        .show_question_bank_review,
      isOpen: true
    };
  },
  computed: {
    ...mapState({
      ItemBankExercise: state => state.ItemBankExercise,
      cover: state => state.ItemBankExercise.cover,
      id: state => state.ItemBankExercise.id,
    }),
  },
  watch: {},
  created() {
    this.signContractConfirm()
  },
  methods: {
    signContractConfirm() {
      const { contract, isContractSigned } = this.ItemBankExercise

      if (isContractSigned == 1 || contract.sign === 'no') return

      const { id, goodsKey, name } = contract

      Dialog.confirm({
        title: this.$t('contract.signContractTitle'),
        message: this.$t('contract.signContractTips', { name }),
        confirmButtonText: this.$t('contract.sign'),
      }).then(() => {
        this.$router.push({ name: 'signContract', params: { id, goodsKey } })
      }).catch(() => {
        this.$router.go(-1)
      });
    },
  },
};
</script>
<style scoped>
.footer {
  position: fixed;
  bottom: 0;
  width: 100%;
}
</style>
