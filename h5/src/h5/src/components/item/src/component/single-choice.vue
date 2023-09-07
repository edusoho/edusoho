<template>
  <div class="ibs-subject">
    <van-radio-group
      v-model="answer"
      class="ibs-answer-paper"
      :disabled="disabled"
      @change="changeAnswer"
    >
      <van-radio
        class="ibs-subject-option"
        v-for="(item, index) in itemData.question.response_points"
        :key="index"
        :name="item.radio.val"
      >
        <div class="ibs-subject-option__content" v-html="item.radio.text" />
        <template #icon="props">
          <span
            :class="[
              'ibs-subject-option__order',
              props,
              reportAnswer(
                itemData.mode,
                item.radio.val,
                itemData.userAnwer,
                itemData.question.answer
              )
            ]"
            >{{ item.radio.val }}</span
          >
        </template>
      </van-radio>
    </van-radio-group>
  </div>
</template>
<script>
import questionMixins from "@/src/mixins/questionMixins.js";
import reportAnswer from "@/src/mixins/reportAnswer.js";
export default {
  name: "single-choice",
  mixins: [questionMixins, reportAnswer],
  data() {
    return {
      answer: this.itemData.userAnwer[0]
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
