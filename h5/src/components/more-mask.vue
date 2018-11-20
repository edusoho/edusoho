<template>
  <div class="more-mask">
    <div class="more-mask__body" :style="heightStyle">
      <slot></slot>
    </div>
    <div v-if="!disabled" class="more-mask__footer" v-show="exccedHeight" :style="textStyle" @touchstart.prevent="maskLoadMore">
      {{ text.content || '点击查看更多' }}
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
    asyncLoaded: {
      default: false,
    },
    disabled: {
      default: false,
    },
    forceShow: {
      type: Boolean,
      default: false,
    },
    text: {
      type: Object,
      default: () => {
        return {
          content: '',
          lineHeight: 17,
          paddingTop: 100,
          align: 'center'
        }
      }
    }
  },
  data() {
    return {
      realHeight: 0,
      intervalTime: 5 * 1000,
      intervalId: undefined,
    };
  },
  computed: {
    exccedHeight() {
      return this.realHeight > this.maxHeight || this.forceShow;
    },
    heightStyle() {
      const maxHeight = (!this.exccedHeight || this.disabled || this.forceShow) ?
        'none' : `${this.maxHeight}px`;
      const paddingBottom = this.forceShow || (this.exccedHeight && !this.disabled) ?
        '25px' : '0';
      return { maxHeight, paddingBottom };
    },
    textStyle() {
      return {
        paddingTop: `${this.text.paddingTop}px`,
        lineHeight: `${this.text.lineHeight}px`,
        textAlign: `${this.text.align}`,
      };
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
          //dom异步更新，但不能保证异步dom 中图片等资源加载完成，这里取最长5秒内的结果
          if (this.intervalId) return;

          const segmentTime = 500
          this.intervalId = setInterval(() => {
            this.intervalTime -= segmentTime;
            if (this.intervalTime < 0) {
              clearInterval(this.intervalId);
              return;
            }
            this.realHeight = this.$el.getBoundingClientRect().height;
          }, segmentTime)
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
