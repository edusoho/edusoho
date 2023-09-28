<template>
  <div class="ibs-reference">
    <div
      v-if="answer_mode === 'text'"
      class="ibs-reference-content__item  ibs-mt10"
    >
      <div class="ibs-reference-content__item_-title">参考答案:</div>
      <div class="ibs-reference-content__item-right ibs-ml5">
        <div
          v-for="(item, index) in answer"
          :key="`right${index}`"
          class="fill-answer"
        >
          （{{ index + 1 }}）
          <span v-html="item"></span>
        </div>
      </div>
    </div>

    <div v-else class="ibs-reference-content__item  ibs-mt10">
      <div class="ibs-reference-content__item_-title">参考答案:</div>
      <div
        class="ibs-reference-content__item-right ibs-ml5"
        v-html="formateAnswer()"
      ></div>
    </div>
  </div>
</template>
<script>
import { filterTF } from "@/src/utils/filters";
export default {
  name: "ibs-reference-answer",
  props: {
    answer: {
      type: Array,
      default: () => []
    },
    answer_mode: {
      type: String,
      default: ""
    }
  },
  data() {
    return {};
  },
  computed: {},
  methods: {
    formateAnswer() {
      if (this.answer_mode === "true_false") {
        return filterTF(this.answer[0]);
      }
      return this.answer.join(" ");
    }
  }
};
</script>
