<!-- 数据上报遮罩层 -->
<template>
  <div class="out-focus-mask" v-if="isShow">
    <template v-if="type === 'ineffective_learning'">
      <div class="content">
        <div class="tips">
          <p>请勿同时多开任务学习</p>
          <p>不要一心多用哦！</p>
        </div>
        <van-button color="#43bc60" size="small" @click="onKeepLearning">
          继续学习
        </van-button>
      </div>
    </template>

    <template v-else-if="type === 'kick_previous'">
      <div class="content">
        <div class="tips">
          <p>请勿同时多开任务学习</p>
          <p>不要一心多用哦！</p>
        </div>
        <van-button color="#43bc60" size="small" @click="onKeepLearning">
          继续学习
        </van-button>
      </div>
    </template>

    <template v-else-if="type === 'reject_current'">
      <div class="content">
        <div class="tips">
          <p>请勿同时多开任务学习</p>
          <p>不要一心多用哦！</p>
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
  },
};
</script>
