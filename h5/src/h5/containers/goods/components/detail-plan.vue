<template>
  <div class="detail-plan">
    <!-- 学习计划 -->
    <div class="detail-plan__plan clearfix" @click="showPopup">
      <div class="pull-left plan-left">教学计划</div>
      <div class="pull-left plan-right">
        {{ currentPlan.title }}
        <i class="iconfont icon-arrow-right plan-right__icon"></i>
      </div>
    </div>

    <!-- 学习计划弹出框 -->
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
          v-for="plan in plans"
          :key="plan.id"
          :class="{ active: plan.active }"
          @click="handleClick(plan)"
        >{{ plan.title }}</span>
      </div>

      <div class="plan-popup__other">
        <!-- 学习有效期 -->
        <div class="popup-other clearfix">
          <div class="pull-left popup-other__left">学习有效期</div>
          <div class="pull-left popup-other__right" v-html="currentPlan.expiryMode"></div>
        </div>
        <!-- 承诺服务 -->
        <div class="popup-other clearfix" v-if="currentPlan.services">
          <div class="pull-left popup-other__left">承诺服务</div>
          <div class="pull-left popup-other__right">
            <span class="popup-other__right__promise" v-for="(item, index) in currentPlan.services" :key="index">练</span>
          </div>
        </div>
      </div>
      <div class="plan-popup__buy">立即购买</div>
    </van-popup>

    <!-- 学习有效期 -->
    <div class="detail-plan__plan clearfix">
      <div class="pull-left plan-left">学习有效期</div>
      <div class="pull-left plan-right" v-html="currentPlan.expiryMode"></div>
    </div>

    <!-- 承诺服务 -->
    <div class="detail-plan__plan clearfix" v-if="currentPlan.services">
      <div class="pull-left plan-left">承诺服务</div>
      <div class="pull-left plan-right">
        <span class="plan-right__promise" v-for="(item, index) in currentPlan.services" :key="index">{{ item.shortName }}</span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      show: false // 是否显示弹出层
    };
  },
  props: {
    plans: {
      type: Array,
      default: () => {}
    },
    currentPlan: {
      type: Object,
      default: () => {}
    }
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
    handleClick(plan) {
      if (plan.id ==  this.$route.params.id) return;
      this.$emit('changePlan', plan.id);
      this.$router.push({ path: `/goods/${plan.id}/course`});
    }
  },
  computed: {
    // learnExpiryHtml() {
    //   if (!this.details.learningExpiryDate) return;
    //   console.log(this.details);
    //   const memberInfo = this.details.member;
    //   const learnExpiryData = this.details.learningExpiryDate;
    //   const expiryMode = learnExpiryData.expiryMode;

    //   if (!memberInfo) {
    //     switch (expiryMode) {
    //       case "forever":
    //         return "永久有效";
    //         break;
    //       case "end_date":
    //         return learnExpiryData.expiryEndDate.slice(0, 10) + "之前可学习";
    //         break;
    //       case "days":
    //         return learnExpiryData.expiryDays + "天内可学习";
    //         break;
    //       case "date":
    //         const startDateStr = learnExpiryData.expiryStartDate.slice(0, 10);
    //         const endDateStr = learnExpiryData.expiryEndDate.slice(0, 10);
    //         return (
    //           '<div class = "mt5">' +
    //           "开课日期：" +
    //           startDateStr +
    //           "&nbsp;&nbsp;&nbsp;" +
    //           "截止日期：" +
    //           endDateStr +
    //           "</div>"
    //         );
    //         break;
    //     }
    //   } else {
    //     if (expiryMode == "forever") {
    //       return "永久有效";
    //     }
    //     return memberInfo.deadline != 0
    //       ? memberInfo.deadline.slice(0, 10) + "之前可学习"
    //       : "永久有效";
    //   }
    // }
  }
};
</script>