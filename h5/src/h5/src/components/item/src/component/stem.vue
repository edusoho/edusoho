<template>
  <div>
    <div class="ibs-subject-stem">
      <div class="ibs-serial-number">{{ Number(questionSeq) }}、</div>
      <div class="ibs-rich-text" v-html="getStem()" />
    </div>
  </div>
</template>
<script>
export default {
  name: "",
  props: {
    questionSeq: {
      type: Number,
      default: 0
    },
    questionStem: {
      type: String,
      default: ""
    },
    questionsType: {
      type: String,
      default: ""
    }
  },
  methods: {
    getStem() {
      if (this.questionsType === "text") {
        return this.filterFillHtml(this.questionStem);
      }
      return this.questionStem;
    },
    filterFillHtml(text) {
      const reg = /\[\[\]\]/g;
      if (!text.match(reg)) {
        return text;
      }
      let index = 1;
      return text.replace(reg, function() {
        return `<span class="ibs-fill-bank">(${index++}）</span>`;
      });
    }
  }
};
</script>
