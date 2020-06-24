<template>
  <div class="detail-plan">
    <!-- 学习计划 -->
    <div class="detail-plan__plan clearfix" @click="showPopup">
      <div class="pull-left plan-left">教学计划</div>
      <div class="pull-left plan-right">
        已选某某教学计划
        <i class="iconfont icon-arrow-right plan-icon"></i>
      </div>
    </div>
    <!-- 学习计划弹出框 -->
    <van-popup v-model="show" round position="bottom" @close="onClose" class="detail-plan__popup plan-popup">
      <div class="plan-popup__title">
        <span></span>
        选择教学计划
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
        <span class="plan-promise">练</span>
        <span class="plan-promise">试</span>
        <span class="plan-promise">问</span>
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