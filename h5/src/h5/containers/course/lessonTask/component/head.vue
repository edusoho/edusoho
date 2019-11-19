<template>
  <div class="head-top">
    <div class="head-left">
      {{ subject }}
      <span v-show="showScore" class="left-color">[{{ score }}分]</span>
    </div>
    <div class="head-right">
      <span class="right-color">{{ current }}</span>/{{ all }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'HeadTop',
  props: {
    all: {
      type: Number,
      default: 0
    },
    current: {
      type: Number,
      default: 0
    },
    subject: {
      type: String,
      default: ''
    },
    score: {
      type: String,
      default: '0'
    },
    showScore: {
      type: Boolean,
      default: true
    }
  }
}
</script>

<style>
</style>
