<template>
  <div class="more-mask">
    <div class="more-mask__body" :style="heightStyle">
      <slot></slot>
    </div>
    <div v-if="!disabled" class="more-mask__footer" v-show="exccedHeight" :style="textStyle" @touchstart="maskLoadMore">
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
