<template>
  <div
    class="duplicate-question-item"
    @click="changeOption"
    :class="active ? 'duplicate-question-active' : ''"
  >
    <div class="duplicate-question-title">
      {{ title | stripTags }}
    </div>
    <span class="duplicate-question-check-count">{{ count }}{{ 'question.bank.unit'|trans }}</span>
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
    count: {
      type: String,
      default: "",
    },
    title: {
      type: String,
      default: "",
    },
  },
  methods: {
    // 切换课程选项
    changeOption() {
      this.$emit("changeOption", this.id);
    }
  },
};
</script>