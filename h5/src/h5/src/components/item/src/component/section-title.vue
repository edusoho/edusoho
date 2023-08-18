<template>
  <div class="ibs-head-top">
    <div class="ibs-head-left">
      {{ subject }}
      <span v-show="showScore" class="ibs-left-color">[{{ score }}分]</span>
    </div>
    <div class="ibs-head-right">
      <span class="ibs-right-color">{{ current }}</span
      >/{{ all }}
    </div>
  </div>
</template>
<script>
import answerMode from "@/src/utils/filterAnswerMode";
export default {
  name: "sectionTitle",
  props: {
    all: {
      type: Number,
      default: 0
    },
    current: {
      type: Number,
      default: 0
    },
    itemType: {
      type: String,
      default: ""
    },
    questionsType: {
      type: String,
      default: ""
    },
    score: {
      type: Number,
      default: 0
    },
    showScore: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    subject() {
      return `${answerMode(this.questionsType)}`;
    }
  }
};
</script>
