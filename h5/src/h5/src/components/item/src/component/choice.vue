<template>
  <div class="ibs-subject">
    <van-checkbox-group
      v-model="answer"
      class="ibs-answer-paper"
      @change="changeAnswer"
    >
      <van-checkbox
        v-for="(item, index) in itemData.question.response_points"
        :key="index"
        :name="item.checkbox.val"
        :disabled="disabled"
        class="ibs-subject-option"
      >
        <div class="ibs-subject-option__content" v-html="item.checkbox.text" />
        <span
          slot="icon"
          :class="[
            'ibs-subject-option__order',
            'ibs-subject-option__order--square',
            reportAnswer(
              itemData.mode,
              item.checkbox.val,
              itemData.userAnwer,
              itemData.question.answer
            )
          ]"
          >{{ item.checkbox.val }}</span
        >
      </van-checkbox>
    </van-checkbox-group>
  </div>
</template>
<script>
import questionMixins from "@/src/mixins/questionMixins.js";
import reportAnswer from "@/src/mixins/reportAnswer.js";

export default {
  mixins: [reportAnswer, questionMixins],
  data() {
    return {
      answer: this.itemData.userAnwer
    };
  },
  methods: {
    changeAnswer(e) {
      if (this.itemData.mode !== "do") {
        return;
      }
      this.$emit("changeAnswer", e, this.itemData.keys);
    }
  }
};
</script>
