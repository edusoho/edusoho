<!-- 数据上报遮罩层 -->
<template>
  <div class="video-report-mask" v-if="isShow">
    <template v-if="type === 'ineffective_learning' && reportType === 'video'">
      <div class="content">
        <div class="tips">
          <p class="ineffective-learning">学累了可以休息一会 :D</p>
        </div>
        <van-button
          color="#43bc60"
          size="small"
          @click="onKeepLearning('ineffective_learning')"
        >
          继续学习
        </van-button>
      </div>
    </template>

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
    /**
     * 继续学习
     */
    onKeepLearning(type) {
      this.$emit('outFocusMask', type);
    },
  },
};
</script>
