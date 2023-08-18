<template>
  <div class="ibs-subject ibs-fill">
    <div class="ibs-answer-paper">
      <div v-for="(i, index) in itemData.question.answer" :key="index">
        <div class="ibs-fill-subject">填空题（{{ index + 1 }}）</div>
        <van-field
          v-model="answer[index]"
          :placeholder="placeholder"
          class="ibs-fill-input"
          label-width="0px"
          type="textarea"
          rows="1"
          autosize
          :disabled="disabled"
          @input="changeAnswer"
        />
      </div>
    </div>
  </div>
</template>
<script>
import questionMixins from "@/src/mixins/questionMixins.js";

export default {
  name: "fill",
  mixins: [questionMixins],
  data() {
    return {
      answer: this.itemData.userAnwer
    };
  },
  computed: {
    placeholder: {
      get() {
        if (this.disabled) {
          return "未作答";
        } else {
          return "请填写答案";
        }
      }
    }
  },
  methods: {
    filterFillHtml(text) {
      const reg = /\[\[\]\]/g;
      if (!text.match(reg)) {
        return text;
      }
      let index = 1;
      return text.replace(reg, function() {
        return `<span class="ibs-fill-bank">(${index++}）</span>`;
      });
    },
    changeAnswer() {
      this.$emit("changeAnswer", this.answer, this.itemData.keys);
    }
  }
};
</script>
