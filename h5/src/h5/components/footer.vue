<!-- 职能：底部的 fixed 盒子 -->
<template>
  <footer :class="classList" class="footer-bar">
    <slot/>
  </footer>
</template>
<script>
export default {
  props: {
    disabled: {
      type: Boolean,
      default: false
    },
    half: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    classList() {
      return {
        disabled: this.disabled,
        half: this.half
      }
    }
  }
}
</script>
