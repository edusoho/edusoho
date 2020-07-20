<template>
  <div class="detail-plan" v-if="goods.id">
    <div class="detail-plan__plan clearfix" @click="showPopup">
      <div class="pull-left plan-left">教学计划</div>
      <div class="pull-left plan-right">
        {{ currentSku.title }}
        <i class="iconfont icon-arrow-right plan-right__icon"></i>
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
        选择教学计划
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

      <div class="plan-popup__other">
        <!-- 学习有效期 -->
        <div class="popup-other clearfix">
          <div class="pull-left popup-other__left">学习有效期</div>
          <div
            class="pull-left popup-other__right"
            v-html="currentSku.expiryMode"
          ></div>
        </div>
        <!-- 承诺服务 -->
        <div class="popup-other clearfix" v-if="currentSku.services">
          <div class="pull-left popup-other__left">承诺服务</div>
          <div class="pull-left popup-other__right">
            <span
              class="popup-other__right__promise"
              v-for="(item, index) in currentSku.services"
              :key="index"
              >练</span
            >
          </div>
        </div>
      </div>
      <div class="plan-popup__buy">立即购买</div>
    </van-popup>

    <div class="detail-plan__plan clearfix">
      <div class="pull-left plan-left">学习有效期</div>
      <div class="pull-left plan-right" v-html="buyableModeHtml"></div>
    </div>

    <div
      class="detail-plan__plan clearfix"
      v-if="currentSku.services.length > 0"
    >
      <div class="pull-left plan-left">承诺服务</div>
      <div class="pull-left plan-right">
        <span
          class="plan-right__promise"
          v-for="(item, index) in currentSku.services"
          :key="index"
          >{{ item.shortName }}</span
        >
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      show: false, // 是否显示弹出层
    };
  },
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
    // 点击显示弹窗
    showPopup() {
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
  },
  computed: {
    buyableModeHtml() {
      const memberInfo = this.goods.member;
      if (!memberInfo) {
        switch (this.currentSku.buyableMode) {
          case 'forever':
            return '永久有效';
          case 'end_date':
            return (
              this.currentSku.buyableEndTime.slice(0, 10) + '&nbsp;之前可学习'
            );
          case 'days':
            return 'x天内可学习';
          case 'date':
            return (
              this.currentSku.buyableStartTime.slice(0, 10) +
              '&nbsp;~&nbsp;' +
              this.currentSku.buyableEndTime.slice(0, 10)
            );
          default:
            return '';
        }
      } else {
        if (this.currentSku.buyableMode == 'forever') {
          return '永久有效';
        }
        return memberInfo.deadline != 0
          ? memberInfo.deadline.slice(0, 10) + '之前可学习'
          : '永久有效';
      }
    },
  },
};
</script>
