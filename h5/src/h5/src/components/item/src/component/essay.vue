<template>
  <div class="ibs-subject-card">
    <div class="ibs-subject-stem">
      <span class="ibs-tags">
        {{ subject }}
      </span>
      <div>
        <div class="ibs-serial-number">{{ Number(commonData.current) }}、</div>
        <div class="ibs-rich-text" v-html="getStem()" />
      </div>
    </div>
    <div class="ibs-subject ibs-essay">
      <div class="ibs-answer-paper">
        <van-field
          v-model="answer"
          :placeholder="placeholder"
          :autosize="{ maxHeight: 200, minHeight: 200 }"
          :disabled="disabled"
          class="ibs-essay-input"
          label-width="0px"
          type="textarea"
          @input="changeAnswer"
        />
      </div>
    </div>
  </div>
</template>

<script>
import questionMixins from "@/src/mixins/questionMixins.js";
import answerMode from "@/src/utils/filterAnswerMode";
import itemBankMixins from "@/src/mixins/itemBankMixins.js"

import { debounce } from "@/src/utils/debounce.js";
export default {
  name: "essay",
  mixins: [questionMixins, itemBankMixins],
  data() {
    return {
      answer: this.itemData.userAnwer[0]
    };
  },
  computed: {
    placeholder: {
      get() {
        if (this.disabled) {
          return "未作答";
        } else {
          return "请填写你的答案......";
        }
      }
    },
    subject() {
      return `${answerMode(this.commonData.questionsType)}`;
    }
  },
  methods: {
    changeAnswer(data) {
      const that = this;
      debounce(
        function() {
          that.$emit("changeAnswer", data, that.itemData.keys);
        },
        500,
        true
      )();
    }
  }
};
</script>
