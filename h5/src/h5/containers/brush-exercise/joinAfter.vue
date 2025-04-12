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
import {createNamespacedHelpers, mapMutations} from 'vuex';
import { Dialog } from 'vant';
import directory from './directory';
import reviewList from './review-list';
import introduction from './introduction';
import closedFixed from '@/components/closed-fixed.vue'
import Api from '@/api';
import aiAgent from '@/mixins/aiAgent';
import * as types from '@/store/mutation-types';

const { mapState } = createNamespacedHelpers('ItemBank');
export default {
  components: {
    directory,
    reviewList,
    introduction,
    closedFixed
  },
  props: ['details'],
  mixins: [aiAgent],
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
  mounted() {
    this.tryInitAIAgentSdk()
  },
  methods: {
    ...mapMutations('course', {
      setSourceType: types.SET_SOURCETYPE,
    }),
    tryInitAIAgentSdk() {
      Api.getItemBankExercise({
        query: {
          id: this.$route.params.id,
        }
      }).then(res => {
        if (res.aiTeacherDomain) {
          const sdk = this.initAIAgentSdk(this.$store.state.user.aiAgentToken, {
            domainId: res.aiTeacherDomain,
          }, 20, 20, null);
        }
        if (res.studyPlanGenerated) {
          sdk.removeShortcut('plan.create')
          sdk.addShortcut('plan.check', {
            name: '查看学习计划',
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">\n' +
              '<path d="M13 2H3C2.44772 2 2 2.44772 2 3V13C2 13.5523 2.44772 14 3 14H13C13.5523 14 14 13.5523 14 13V3C14 2.44772 13.5523 2 13 2Z" stroke="#333333" stroke-linejoin="round"/>\n' +
              '<path d="M7.00016 4.33301H4.3335V6.99967H7.00016V4.33301Z" stroke="#333333" stroke-linejoin="round"/>\n' +
              '<path d="M7.00016 9H4.3335V11.6667H7.00016V9Z" stroke="#333333" stroke-linejoin="round"/>\n' +
              '<path d="M9 9.33301H11.6667" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/>\n' +
              '<path d="M9 11.667H11.6667" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/>\n' +
              '<path d="M9 4.33301H11.6667" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/>\n' +
              '<path d="M9 6.66699H11.6667" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/>\n' +
              '</svg>',
            type: 'Send',
            data: {
              content: '查看学习计划'
            }
          });
          sdk.addShortcut('plan.recreate', {
            name: '重新制定学习计划',
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">\n' +
              '<path d="M12.2426 12.2426C11.1569 13.3284 9.65687 14 8 14C4.6863 14 2 11.3137 2 8C2 4.6863 4.6863 2 8 2C9.65687 2 11.1569 2.67157 12.2426 3.75737C12.7953 4.31003 14 5.66667 14 5.66667" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/>\n' +
              '<path d="M14 2.66699V5.66699H11" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"/>\n',
            type: 'Send',
            data: {
              content: '重新制定学习计划'
            }
          });
        }
      })
        .catch(err => {
          console.log(err);
        })
    },
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
