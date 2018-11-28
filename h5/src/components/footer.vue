<!-- 职能：底部的 fixed 盒子 -->
<template>
  <footer class="footer-bar" :class="disabled ? 'gray' : ''">
    <slot></slot>
  </footer>
</template>
<script>
export default {
  props: {
    disabled: {
      type: Boolean,
      default: false
    }
  },
}
</script>
