<template>
  <div class="more-mask">
    <div class="more-mask__body" :class="bodyClass" :style="heightStyle">
      <slot></slot>
    </div>
    <div class="more-mask__footer" v-show="bodyClass" :style="paddingStyle" @touchstart="maskLoadMore">
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
    }
  },
  data() {
    return {
      realHeight: 0,
    };
  },
  computed: {
    bodyClass() {
      return this.realHeight > this.maxHeight ? 'hidden' : '';
    },
    heightStyle() {
      if (!this.bodyClass) {
        return;
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
        if (value) {
          this.$nextTick(function () {
            //dom已更新
            this.realHeight = this.$el.getBoundingClientRect().height;
          })
        }
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
