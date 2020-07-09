<template>
  <div class="goods-specs">
    <div class="goods-plan">
      <!-- 教学计划 -->
      <div class="goods-plan__plan clearfix">
        <div class="pull-left plan-left">教学计划</div>
        <div class="pull-left plan-right" @click="onPopup(true)">
          {{ currentPlan.title }}
          <i class="es-icon es-icon-chevronright plan-right__icon"></i>
        </div>
      </div>

      <!-- 有效期 -->
      <div class="goods-plan__plan clearfix">
        <div class="pull-left plan-left">学习有效期</div>
        <div class="pull-left plan-right">{{ currentPlan.expiryMode }}</div>
      </div>

      <!-- 承诺服务 -->
      <div class="goods-plan__plan clearfix" v-if="currentPlan.services">
        <div class="pull-left plan-left">承诺服务</div>
        <div class="pull-left plan-right">
          <span class="plan-right__promise" v-for="(val, key, index) in currentPlan.services" :key="index">练</span>
        </div>
      </div>
    </div>

    <!-- 切换教学计划 -->
    <div class="popup-mask" v-show="show" @click="onPopup(false)"></div>
    <div class="specs-popup" :class="{ 'active': show }">
      <div class="specs-popup__title">
        <span></span>
        选择教学计划
      </div>
      <div class="specs-popup__type">
        <span
          class="specs-popup__type__item"
          v-for="plan in details.specs"
          :key="plan.id"
          :class="{ active: plan.active }"
          @click="handleClick(plan)"
        >{{ plan.title }}</span>
      </div>

      <div class="specs-popup__other">
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
      <div class="specs-popup__buy">立即购买</div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      show: false
    }
  },
  props: {
    details: {
      type: Object,
      default: () => {}
    },
    currentPlan: {
      type: Object,
      default: () => {}
    }
  },
  methods: {
    onPopup(value) {
      this.show = value;
      if (value) {
        document.body.style.overflow = 'hidden';
      } else {
        document.body.style.overflow = 'auto';
      }
    },
    handleClick(plan) {
    //   if (plan.id ==  this.$route.params.id) return;
      this.$emit('changePlan', plan.id);
    //   this.$router.push({ path: `/goods/${plan.id}/course`});
    }
  }
}
</script>