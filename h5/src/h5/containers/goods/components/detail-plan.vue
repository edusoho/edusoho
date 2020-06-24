<template>
  <div class="detail-plan">
    <!-- 学习计划 -->
    <div class="detail-plan__plan clearfix" @click="showPopup">
      <div class="pull-left plan-left">教学计划</div>
      <div class="pull-left plan-right">
        已选某某教学计划
        <i class="iconfont icon-arrow-right plan-right__icon"></i>
      </div>
    </div>
    <!-- 学习计划弹出框 -->
    <van-popup v-model="show" round position="bottom" @close="onClose" class="detail-plan__popup plan-popup">
      <div class="plan-popup__title">
        <span></span>
        选择教学计划
      </div>
      <div class="plan-popup__type">
        <span class="plan-popup__type__item active">某某教学计划</span>
        <span class="plan-popup__type__item">某某教学计划</span>
        <span class="plan-popup__type__item">某某教学计划</span>
        <span class="plan-popup__type__item">某某教学计划</span>
        <span class="plan-popup__type__item">某某教学计划</span>
        <span class="plan-popup__type__item">某某教学计划</span>
      </div>
      <div class="plan-popup__other">
         <!-- 学习有效期 -->
        <div class="popup-other clearfix">
          <div class="pull-left popup-other__left">学习有效期</div>
          <div class="pull-left popup-other__right">长期有效</div>
        </div>
        <!-- 承诺服务 -->
        <div class="popup-other clearfix">
          <div class="pull-left popup-other__left">承诺服务</div>
          <div class="pull-left popup-other__right">
            <span class="popup-other__right__promise">练</span>
            <span class="popup-other__right__promise">试</span>
            <span class="popup-other__right__promise">问</span>
          </div>
        </div>
      </div>
      <div class="plan-popup__buy">
        立即购买
      </div>
    </van-popup>
    <!-- 学习有效期 -->
    <div class="detail-plan__plan clearfix">
      <div class="pull-left plan-left">学习有效期</div>
      <div class="pull-left plan-right">长期有效</div>
    </div>
    <!-- 承诺服务 -->
    <div class="detail-plan__plan clearfix">
      <div class="pull-left plan-left">承诺服务</div>
      <div class="pull-left plan-right">
        <span class="plan-right__promise">练</span>
        <span class="plan-right__promise">试</span>
        <span class="plan-right__promise">问</span>
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
  methods: {
    // 点击显示弹窗
    showPopup() {
      this.show = true;
    },
    // 关闭弹窗时触发
    onClose() {
      this.show = false;
    }
  }
};
</script>