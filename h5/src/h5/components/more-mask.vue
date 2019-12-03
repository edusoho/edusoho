<template>
  <div class="more-mask">
    <div :style="heightStyle" class="more-mask__body">
      <slot/>
    </div>
    <div v-if="!disabled" v-show="exccedHeight" :style="textStyle" class="more-mask__footer" @touchstart.prevent="maskLoadMore">
      {{ text.content || '点击查看更多' }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'MoreMask',
  props: {
    maxHeight: {
      default: 288
    },
    disabled: {
      default: false
    },
    forceShow: {
      type: Boolean,
      default: false
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
      intervalId: undefined
    }
  },
  computed: {
    exccedHeight() {
      return this.realHeight > this.maxHeight || this.forceShow
    },
    heightStyle() {
      const maxHeight = (!this.exccedHeight || this.disabled || this.forceShow)
        ? 'none' : `${this.maxHeight}px`
      const paddingBottom = this.forceShow || (this.exccedHeight && !this.disabled)
        ? '25px' : '0'
      return { maxHeight, paddingBottom }
    },
    textStyle() {
      return {
        paddingTop: `${this.text.paddingTop}px`,
        lineHeight: `${this.text.lineHeight}px`,
        textAlign: `${this.text.align}`
      }
    }
  },
  mounted() {
    // dom异步更新，但不能保证异步dom 中图片等资源加载完成，这里取最长5秒内的结果
    const segmentTime = 500
    this.intervalId = setInterval(() => {
      this.intervalTime -= segmentTime
      if (this.intervalTime < 0) {
        clearInterval(this.intervalId)
        return
      }
      this.realHeight = this.$el.getBoundingClientRect().height
    }, segmentTime)
  },
  methods: {
    maskLoadMore() {
      this.$emit('maskLoadMore')
    }
  }
}
</script>
