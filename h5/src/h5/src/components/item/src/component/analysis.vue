<template>
  <div class="ibs-analysis ">
    <van-collapse v-model="activeNames" @change="change">
      <van-collapse-item
        title="做题解析"
        name="1"
        title-class="ibs-analysis-title"
        :disabled="disabled"
      >
        <!-- 参考答案 做题查看解析需要显示参考答案-->
        <reference-answer
          v-if="this.mode === 'do'"
          :answer="answer"
          :answer_mode="answer_mode"
        ></reference-answer>
        <!-- 做题解析 -->
        <div v-if="analysis" class="ibs-analysis-content ibs-mt10">
          解析：<span v-html="analysis"></span>
        </div>
        <div v-else class="ibs-analysis-content ibs-mt10">
          无解析
        </div>
      </van-collapse-item>
    </van-collapse>

    <attachement-preview
      v-for="item in attachements"
      :attachment="item"
      :key="item.id"
    />
  </div>
</template>
<script>
import referenceAnswer from "./reference-answer.vue";
import attachementPreview from "./attachement-preview.vue";

export default {
  name: "ibs-analysis",
  components: {
    referenceAnswer,
    attachementPreview
  },
  props: {
    mode: {
      type: String,
      default: "do"
    },
    analysis: {
      type: String,
      default: ""
    },
    answer: {
      type: Array,
      default: () => []
    },
    answer_mode: {
      type: String,
      default: ""
    },
    attachements: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      activeNames: this.getActiveNames()
    };
  },
  computed: {
    disabled() {
      return this.mode !== "do";
    }
  },
  methods: {
    change(e) {
      this.$emit("showMaterialAnalysis", !!Object.keys(e).length);
    },
    getActiveNames() {
      if (this.mode !== "do") {
        return ["1"];
      }
      return [];
    }
  }
};
</script>
