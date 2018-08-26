<template>
  <div :class="containerClass">
    <slot name="preview"></slot>
    <slot name="setting"></slot>
    <slot name="modal"></slot>
  </div>
</template>

<script>
export default {
  props: {
    containerClass: ''
  }
}
</script>
