<!-- 数据上报遮罩层 -->
<template>
  <div class="out-focus-mask" v-if="isShow">
    <div class="report-go-back" @click="$router.go(-1)">
      <van-icon name="arrow-left" size="25" color="#43bc60" />
    </div>
    <template v-if="type === 'kick_previous'">
      <div class="content">
        <div class="tips">
          <p class="kick-each-other">请勿同时多开任务学习</p>
          <p class="kick-each-other">不要一心多用哦！</p>
        </div>
        <van-button
          color="#43bc60"
          size="small"
          @click="onKeepLearning('kick_previous')"
        >
          继续学习
        </van-button>
      </div>
    </template>

    <template v-if="type === 'reject_current'">
      <div class="content">
        <div class="tips">
          <p class="kick-each-other">请勿同时多开任务学习</p>
          <p class="kick-each-other">不要一心多用哦！</p>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
export default {
  props: {
    type: {
      type: String,
      default: '',
    },
    isShow: {
      type: Boolean,
      default: false,
    },
    reportType: {
      type: String,
      default: '',
    },
  },
  methods: {
    onKeepLearning(type) {
      this.$emit('outFocusMask', type);
    },
  },
};
</script>
