<template>
  <div class="ibs-subject">
    <van-radio-group
      v-model="answer"
      class="ibs-answer-paper"
      @change="changeAnswer"
      :disabled="disabled"
    >
      <van-radio
        v-for="(item, index) in itemData.question.response_points"
        :key="index"
        :name="item.radio.val"
        class="ibs-subject-option subject-option--determine"
      >
        <div class="ibs-subject-option__content">{{ item.radio.text }}</div>
        <i
          slot="icon"
          :class="[
            'wap-icon',
            getIcons(item.radio.val),
            'ibs-subject-option__order',
            reportAnswer(
              itemData.mode,
              item.radio.val,
              itemData.userAnwer,
              itemData.question.answer
            )
          ]"
        />
      </van-radio>
    </van-radio-group>
  </div>
</template>
<script>
import questionMixins from "@/src/mixins/questionMixins.js";
import reportAnswer from "@/src/mixins/reportAnswer.js";
export default {
  name: "judge",
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
    },
    getIcons(types) {
      if (types === "F") {
        return "wap-icon-no";
      }
      if (types === "T") {
        return "wap-icon-yes";
      }
    }
  }
};
</script>
