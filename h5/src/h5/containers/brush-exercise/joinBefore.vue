<template>
  <div class="brush-exercise-detail-bank brush-exercise-joinbefore">
    <img :src="ItemBankExercise.cover.large" class="brush-exercise-cover" />
    <van-tabs v-model="active" sticky>
      <van-tab title="课程简介">
        <introduction></introduction>
      </van-tab>
      <van-tab title="课程目录">
        <directory :exerciseId="Number(id)"></directory
      ></van-tab>
      <van-tab title="学员评价" v-if="show_question_bank_review == 1">
        <review-list
          ref="review"
          title="学员评价"
          defaul-value="暂无评价"
          type="item_bank_exercise"
        />
      </van-tab>
    </van-tabs>
    <!-- 加入学习 -->
    <e-footer>
    <closedFixed v-if="ItemBankExercise.status == 'closed'" :isJoin="false" :title="$t('closed.exerciseTitle')" />
      <div @click="handleJoin">加入题库</div>
    </e-footer>
  </div>
</template>

<script>
import { createNamespacedHelpers } from 'vuex';
import directory from './directory';
import Api from '@/api';
import * as types from '@/store/mutation-types';
import { Toast } from 'vant';
import reviewList from './review-list';
import introduction from './introduction';
import { learnExpiry } from '@/utils/itemBank-status';
import { closedToast } from '@/utils/on-status.js';
import closedFixed from '@/components/closed-fixed.vue'

const { mapState, mapActions, mapMutations } = createNamespacedHelpers(
  'ItemBank',
);
export default {
  components: {
    directory,
    reviewList,
    introduction,
    closedFixed
  },
  data() {
    return {
      timer: null,
      active: 0,
      show_question_bank_review: this.$store.state.goods
        .show_question_bank_review,
    };
  },
  computed: {
    ...mapState({
      ItemBankExercise: state => state.ItemBankExercise,
      id: state => state.ItemBankExercise.id,
    }),
  },
  beforeRouteLeave(to, from, next) {
    clearTimeout(this.timer);
  },
  watch: {},
  created() {},
  methods: {
    ...mapActions(['setItemBankExercise']),
    ...mapMutations({ changJoinStatus: types.CHANGE_ITEMBANK_JOINSTATUS }),
    handleJoin() {
      if (this.ItemBankExercise?.status == 'closed') {
        this.$router.push({ path: '/goods/closed', query: { type: 'exercise' } });
        return 
      }

      if (!this.$store.state.token) {
        this.$router.push({
          name: 'login',
          query: {
            redirect: this.$route.path,
          },
        });
        return;
      }
      const code = this.ItemBankExercise.access.code;
      if (code !== 'success') {
        this.$toast(this.ItemBankExercise.access.msg);
      } else {
        this.joinItemBank();
      }
    },
    joinItemBank() {
      if (!this.timer) {
        this.timer = setTimeout(() => {
          const query = {
            exerciseId: this.id,
          };
          Toast.loading({
            duration: 0,
            forbidClick: true,
            message: '提交中',
          });
          Api.joinItemBank({
            query,
          })
            .then(res => {
              this.judgeIsJoin(res);
            })
            .catch(err => {
              this.$toast(err.message);
            });
          this.timer = null;
        }, 1000);
      }
    },
    judgeIsJoin(res) {
      Toast.clear();
      if (Object.keys(res).length) {
        this.$toast('加入成功');
        setTimeout(() => {
          this.changJoinStatus(true);
          Toast.clear();
        }, 1000);
      } else {
        this.getOrder();
      }
    },
    learnExpiryHtml() {
      const obj = {
        expiryMode: this.ItemBankExercise.expiryMode,
        expiryDays: this.ItemBankExercise.expiryDays,
        expiryStartDate: this.ItemBankExercise.expiryStartDate,
        expiryEndDate: this.ItemBankExercise.expiryEndDate,
      };
      return learnExpiry(obj);
    },
    // 创建订单
    getOrder() {
      const expiryStr = this.learnExpiryHtml();
      this.$router.push({
        name: 'order',
        params: {
          id: this.ItemBankExercise.id,
        },
        query: {
          expiryScope: expiryStr,
          targetType: 'item_bank_exercise',
        },
      });
    },
  },
};
</script>
