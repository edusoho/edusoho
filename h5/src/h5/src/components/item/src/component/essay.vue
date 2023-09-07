<template>
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
</template>

<script>
import questionMixins from "@/src/mixins/questionMixins.js";
import { debounce } from "@/src/utils/debounce.js";
export default {
  name: "essay",
  mixins: [questionMixins],
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
