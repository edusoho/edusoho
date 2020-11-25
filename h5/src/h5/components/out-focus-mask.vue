<!-- 数据上报遮罩层 -->
<template>
  <div>
    <template v-if="type === 'ineffective_learning'">
      <div
        class="out-focus-mask"
        :class="
          reportType === 'video' || reportType === 'audio' ? 'player' : ''
        "
        v-if="isShow"
      >
        <div class="content">
          <div class="tips">
            <p>学累了可以休息一会 :D</p>
          </div>
          <van-button
            color="#43bc60"
            size="small"
            @click="onKeepLearning('ineffective_learning')"
          >
            继续学习
          </van-button>
        </div>
      </div>
    </template>

    <template v-else-if="type === 'kick_previous'">
      <div
        class="out-focus-mask"
        :class="
          reportType === 'video' || reportType === 'audio' ? 'player' : ''
        "
        v-if="isShow"
      >
        <div class="content">
          <div class="tips">
            <p>请勿同时多开任务学习</p>
            <p>不要一心多用哦！</p>
          </div>
          <van-button
            color="#43bc60"
            size="small"
            @click="onKeepLearning('kick_previous')"
          >
            继续学习
          </van-button>
        </div>
      </div>
    </template>

    <template v-else-if="type === 'reject_current'">
      <div
        class="out-focus-mask not-full"
        :class="
          reportType === 'video' || reportType === 'audio' ? 'player' : ''
        "
        v-if="isShow"
      >
        <div class="content">
          <div class="tips">
            <p>请勿同时多开任务学习</p>
            <p>不要一心多用哦！</p>
          </div>
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
