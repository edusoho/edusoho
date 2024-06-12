<template>
  <!-- 左右滑动按钮 -->
  <div class="ibs-slide-btn">
    <div
      :class="['left-slide__btn', isFrist ? 'slide-disabled' : '']"
      @click="slidePrev()"
    >
      <i class="wap-icon wap-icon-arrow-left" />
    </div>
    <div
      :class="['right-slide__btn', isLast ? 'slide-disabled' : '']"
      @click="slideNext()"
    >
      <i class="wap-icon wap-icon-arrow-right" />
    </div>
  </div>
</template>

<script>
export default {
  name: "ibs-slide-btn",
  components: {},
  props: {
    isLast: {
      type: Boolean,
      default: false
    },
    isFrist: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {};
  },
  computed: {},
  watch: {},
  created() {},
  methods: {
    // 左滑动
    slidePrev() {
      this.$emit("slidePrev");
    },
    // 右滑动
    slideNext() {
      this.$emit("slideNext");
    }
  }
};
</script>
