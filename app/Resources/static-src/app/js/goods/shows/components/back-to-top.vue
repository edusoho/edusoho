<template>
  <div class="back-to-top-pc" @click="backToTop">
    <i class="es-icon es-icon-sticky"></i>
  </div>
</template>

<script>
export default {
  methods: {
    backToTop() {
      document.documentElement.scrollTop = 0;
    }
  }
}
</script>