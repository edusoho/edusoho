<template>
  <div
    class="duplicate-question-item"
    @click="changeOption"
    :class="active ? 'duplicate-question-active' : ''"
  >
    <div class="duplicate-question-title">
      {{ question.displayMaterial | stripTags }}
    </div>
    <span class="duplicate-question-check-count">{{ question.frequency }}{{ 'question.bank.unit'|trans }}</span>
  </div>
</template>
<script>
export default {
  props: {
    // 题干
    active: {
      type: Boolean,
      default: false,
    },
    id: {
      type: Number,
      default: 0,
    },
    question: {
      type: Object,
      default: () => {}
    }
  },
  methods: {
    // 切换课程选项
    changeOption() {
      this.$emit("changeOption", this.id);
    }
  },
};
</script>