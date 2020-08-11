<template>
  <div class="brush-exercise-detail-bank brush-exercise-joinbefore">
    <img :src="ItemBankExercise.cover.large" class="brush-exercise-cover" />
    <van-tabs v-model="active" sticky>
      <van-tab title="课程目录">
        <directory :exerciseId="Number(id)"></directory
      ></van-tab>
      <van-tab title="学员评价">
        <review-list
          ref="review"
          title="学员评价"
          defaul-value="暂无评价"
          type="item_bank_exercise"
        />
      </van-tab>
    </van-tabs>
    <!-- 加入学习 -->
    <e-footer @click.native="handleJoin">
      加入学习
    </e-footer>
  </div>
</template>

<script>
import { createNamespacedHelpers } from 'vuex';
import directory from './directory';
import Api from '@/api';
import * as types from '@/store/mutation-types';
import { formatFullTime } from '@/utils/date-toolkit';
import { Toast } from 'vant';
import reviewList from './review-list';
const { mapState, mapActions, mapMutations } = createNamespacedHelpers(
  'ItemBank',
);
export default {
  components: {
    directory,
    reviewList,
  },
  data() {
    return {
      timer: null,
      active: 0,
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
          }).then(res => {
            this.judgeIsJoin(res);
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
    learnExpiry() {
      const expiryMode = this.ItemBankExercise.expiryMode;
      const expiryDays = this.ItemBankExercise.expiryDays;
      const startDateStr = formatFullTime(
        new Date(this.ItemBankExercise.expiryStartDate * 1000),
      );
      const endDateStr = formatFullTime(
        new Date(this.ItemBankExercise.expiryEndDate * 1000),
      );
      switch (expiryMode) {
        case 'forever':
          return '永久有效';
        case 'end_date':
          return endDateStr + '之前可学习';
        case 'days':
          return expiryDays + '天内可学习';
        case 'date':
          return `${startDateStr} 至 ${endDateStr}`;
      }
    },
    // 创建订单
    getOrder() {
      const expiryStr = this.learnExpiry();
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
