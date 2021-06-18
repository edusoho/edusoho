<template>
  <div class="question-layout">
    <div class="prevent-click">
      <slot />
    </div>

    <slot name="situation" />
  </div>
</template>

<script>


export default {
  name: 'QuestionLayout'
}
</script>

<style lang="less" scoped>
.question-layout {
  position: relative;
  padding-left: 54px;
}

.prevent-click {
  pointer-events: none;
}
</style>
