<template>
  <div class="">
    <img
      :src="ItemBankExercise.cover.large"
      style="width:100%;vertical-align: middle;"
    />
    <directory :exerciseId="Number(id)"></directory>
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
const { mapState, mapActions, mapMutations } = createNamespacedHelpers(
  'ItemBank',
);
export default {
  components: {
    directory,
  },
  data() {
    return {};
  },
  computed: {
    ...mapState({
      ItemBankExercise: state => state.ItemBankExercise,
      id: state => state.ItemBankExercise.id,
    }),
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
      const query = {
        exerciseId: this.id,
      };
      Api.joinItemBank({
        query,
      }).then(res => {
        if (Object.keys(res).length) {
          this.$toast('加入成功');
          setTimeout(() => {
            this.changJoinStatus(true);
          }, 1000);
        } else {
          this.getOrder();
          // 去下订单 节流处理
        }
      });
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
