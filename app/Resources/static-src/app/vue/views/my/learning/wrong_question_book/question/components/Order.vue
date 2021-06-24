<template>
  <a-space
    class="question-order"
    direction="vertical"
    align="center"
    :size="2"
  >
    {{ question.seq }}
    <a-tag style="margin-right: 0;">{{ question.score }}分</a-tag>
  </a-space>
</template>

<script>
export default {
  props: {
    question: {
      type: Object,
      required: true
    }
  }
}
</script>

<style lang="less" scoped>
.question-order {
  position: absolute;
  top: 16;
  left: 0;
}
</style>
