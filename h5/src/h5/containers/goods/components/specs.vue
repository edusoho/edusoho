<template>
  <div class="detail-plan" v-if="goods.id">
    <div
      class="detail-plan__plan clearfix"
      v-if="goods.type === 'course' && goods.specs.length > 1"
      @click="showPopup"
    >
      <div class="pull-left plan-left">{{ $t('goods.plan') }}</div>
      <div class="pull-left plan-right">
        {{ currentSku.title }}
        <i
          v-if="goods.specs.length > 1"
          class="iconfont icon-arrow-right plan-right__icon"
        ></i>
      </div>
    </div>

    <van-popup
      v-model="show"
      round
      position="bottom"
      @close="onClose"
      class="detail-plan__popup plan-popup"
    >
      <div class="plan-popup__title">
        <span></span>
        {{ $t('goods.choosePlan') }}
      </div>
      <div class="plan-popup__type">
        <span
          class="plan-popup__type__item"
          v-for="specs in goods.specs"
          :key="specs.id"
          :class="{ active: specs.active }"
          @click="handleClick(specs)"
          >{{ specs.title }}</span
        >
      </div>

      <div
        class="plan-popup__other"
        v-if="!(currentSku.services === null) && currentSku.services.length"
      >
        <!-- 学习有效期 -->
        <!-- <div class="popup-other clearfix">
          <div class="pull-left popup-other__left">学习有效期</div>
          <div
            class="pull-left popup-other__right"
            v-html="buyableModeHtml"
          ></div>
        </div> -->
        <!-- 承诺服务 -->
        <div class="popup-other clearfix">
          <div class="pull-left popup-other__left">{{ $t('goods.services') }}</div>
          <div class="pull-left popup-other__right">
            <span
              class="popup-other__right__promise"
              v-for="(item, index) in currentSku.services"
              :key="index"
              >{{ $t('goods.practice') }}</span
            >
          </div>
        </div>
      </div>
      <!--      <div class="plan-popup__buy">立即购买</div>-->
    </van-popup>

    <!-- <div
      v-if="currentSku.vipLevelInfo && vipSwitch"
      class="detail-plan__plan clearfix"
    >
      <div class="pull-left plan-left">会员免费</div>
      <div class="pull-left plan-right">
        <img class="vip-icon" :src="currentSku.vipLevelInfo.icon" alt="" />
        <router-link
          :to="{ path: '/vip', query: { id: this.currentSku.vipLevelInfo.id } }"
          class="color-primary"
        >
          {{ currentSku.vipLevelInfo.name }}免费学</router-link
        >
      </div>
    </div> -->

    <!-- <div class="detail-plan__plan clearfix">
      <div class="pull-left plan-left">学习有效期</div>
      <div class="pull-left plan-right" v-html="buyableModeHtml"></div>
    </div> -->

    <div
      class="detail-plan__plan clearfix"
      v-if="!(currentSku.services === null) && currentSku.services.length"
    >
      <div class="pull-left plan-left">{{ $t('goods.services') }}</div>
      <div class="pull-left plan-right">
        <span
          class="plan-right__promise"
          v-for="(item, index) in currentSku.services"
          :key="index"
          >{{ item.shortName }}</span
        >
      </div>
    </div>
    <!-- 优惠活动 -->
    <div v-if="showOnsale">
      <div
        class="detail-plan__plan clearfix"
        v-if="marketingActivities.groupon"
        @click="activityHandle(marketingActivities.groupon.id)"
      >
        <div class="pull-left plan-left">{{ $t('goods.groupPurchase') }}</div>
        <div class="pull-left plan-right">
          <van-tag class="van-tag--primary">{{ $t('goods.groupPurchase') }}</van-tag>
          <span class="text-12 dark">{{ $t('goods.buyWithFriends') }}</span>
        </div>
      </div>

      <div
        class="detail-plan__plan clearfix"
        v-if="marketingActivities.cut"
        @click="activityHandle(marketingActivities.cut.id)"
      >
        <div class="pull-left plan-left">{{ $t('goods.bargain') }}</div>
        <div class="pull-left plan-right">
          <van-tag class="van-tag--success">{{ $t('goods.bargain') }} </van-tag>
          <span class="text-12 dark">{{ $t('goods.theMinimumCanBeCutTo1Point') }}</span>
        </div>
      </div>

      <div
        class="detail-plan__plan clearfix"
        v-if="marketingActivities.seckill"
        @click="activityHandle(marketingActivities.seckill.id)"
      >
        <div class="pull-left plan-left">{{ $t('goods.flashSale') }}</div>
        <div class="pull-left plan-right">
          <van-tag class="van-tag--warning">{{ $t('goods.flashSale') }} </van-tag>
          <span class="text-12 dark">{{ $t('goods.flashSale2') }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import Api from '@/api';
import activityMixin from '@/mixins/activity/index';

export default {
  data() {
    return {
      show: false, // 是否显示弹出层
      marketingActivities: {},
    };
  },
  mixins: [activityMixin],
  props: {
    goods: {
      type: Object,
      default: () => {},
    },
    currentSku: {
      type: Object,
      default: () => {},
    },
  },
  methods: {
    // 去会员页
    gotoVip() {
      this.$router.push({
        path: '/vip',
        query: { id: this.currentSku.vipLevelInfo.id },
      });
    },
    // 点击显示弹窗
    showPopup() {
      if (this.goods && this.goods.specs.length == 1) {
        return;
      }
      this.show = true;
    },
    // 关闭弹窗时触发
    onClose() {
      this.show = false;
    },
    handleClick(specs) {
      this.$emit('changeSku', specs.targetId);
      this.show = false;
    },
    formatDate(time, fmt = 'yyyy-MM-dd') {
      time = time * 1000;
      const date = new Date(time);
      if (/(y+)/.test(fmt)) {
        fmt = fmt.replace(
          RegExp.$1,
          (date.getFullYear() + '').substr(4 - RegExp.$1.length),
        );
      }
      const o = {
        'M+': date.getMonth() + 1,
        'd+': date.getDate(),
        'h+': date.getHours(),
        'm+': date.getMinutes(),
        's+': date.getSeconds(),
      };
      for (const k in o) {
        if (new RegExp(`(${k})`).test(fmt)) {
          const str = o[k] + '';
          fmt = fmt.replace(
            RegExp.$1,
            RegExp.$1.length === 1 ? str : ('00' + str).substr(str.length),
          );
        }
      }
      return fmt;
    },
  },
  mounted() {
    // 获取营销活动
    if (this.goods.type === 'classroom') {
      Api.classroomsActivities({
        query: { id: this.currentSku.targetId },
      })
        .then(res => {
          console.log(res);
          this.marketingActivities = res;
        })
        .catch(err => {
          console.error(err);
        });
    } else if (this.goods.type === 'course') {
      Api.coursesActivities({
        query: { id: this.currentSku.targetId },
      })
        .then(res => {
          console.log(res);
          this.marketingActivities = res;
        })
        .catch(err => {
          console.error(err);
        });
    }
  },
  computed: {
    ...mapState(['vipSwitch']),
    showOnsale() {
      return (
        Number(this.currentSku.price) !== 0 &&
        !!Object.keys(this.marketingActivities).length
      );
    },
    buyableModeHtml() {
      const memberInfo = this.goods.member;
      if (!memberInfo) {
        switch (this.currentSku.usageMode) {
          case 'forever':
            return this.$t('goods.longTermEffective');
          case 'end_date':
            return (
              this.formatDate(this.currentSku.usageEndTime.slice(0, 10)) +
              `&nbsp;${this.$t('goods.canLearnBefore')}`
            );
          case 'days':
            return  this.$t('goods.studyWithinDay', { number: this.currentSku.usageDays });
          case 'date':
            return (
              this.formatDate(this.currentSku.usageStartTime.slice(0, 10)) +
              '&nbsp;~&nbsp;' +
              this.formatDate(this.currentSku.usageEndTime.slice(0, 10))
            );
          default:
            return '';
        }
      } else {
        if (this.currentSku.usageMode == 'forever') {
          return this.$t('goods.longTermEffective');
        }
        return memberInfo.deadline != 0
          ? memberInfo.deadline.slice(0, 10) + this.$t('goods.canLearnBefore')
          : this.$t('goods.longTermEffective');
      }
    },
  },
};
</script>
