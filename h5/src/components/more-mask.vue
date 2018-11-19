<template>
  <div class="more-mask">
    <div class="more-mask__body" :style="heightStyle">
      <slot></slot>
    </div>
    <div v-if="!disabled" class="more-mask__footer" v-show="exccedHeight" :style="paddingStyle" @touchstart="maskLoadMore">
      {{ textContent || '点击查看更多' }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'more-mask',
  props: {
    maxHeight: {
      default: 288,
    },
    textContent: {
      default: '',
    },
    paddingTop: {
      default: 100,
    },
    asyncLoaded: {
      default: false,
    },
    disabled: {
      default: false,
    }
  },
  data() {
    return {
      realHeight: 0,
    };
  },
  computed: {
    exccedHeight() {
      return this.realHeight > this.maxHeight;
    },
    heightStyle() {
      if (!this.exccedHeight || this.disabled) {
        return { maxHeight: 'none'};
      }
      return { maxHeight: `${this.maxHeight}px`};
    },
    paddingStyle() {
      return { paddingTop: `${this.paddingTop}px`};
    }
  },
  mounted() {
    //dom已更新
    this.realHeight = this.$el.getBoundingClientRect().height;
  },
  watch: {
    asyncLoaded: {
      handler(value) {
        this.$nextTick(function () {
          //dom已更新
          this.realHeight = this.$el.getBoundingClientRect().height;
        })
      }
    }
  },
  methods: {
    maskLoadMore() {
      this.$emit('maskLoadMore');
    }
  }
}
</script>
