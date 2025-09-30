<template>
  <div class="ibs-preview-section">
    <div class="ibs-sub-item ibs-pr16">
      <judge-type
        v-if="showQuestion.answer_mode === 'true_false'"
        :question="showQuestion"
        :mode="mode"
      >
      </judge-type>
      <single-choice
        v-if="showQuestion.answer_mode === 'single_choice'"
        :question="showQuestion"
        :mode="mode"
      ></single-choice>
      <choice
        v-if="
          showQuestion.answer_mode === 'choice' ||
            showQuestion.answer_mode === 'uncertain_choice'
        "
        :question="showQuestion"
        :mode="mode"
      ></choice>
      <essay
        v-if="showQuestion.answer_mode === 'rich_text'"
        :question="showQuestion"
        :mode="mode"
      ></essay>
      <fill
        v-if="showQuestion.answer_mode === 'text'"
        :question="showQuestion"
        :mode="mode"
      ></fill>
      <a-row>
        <a-col :span="12" :offset="2">
          <div class="ibs-sub-operation">
            <a-button
              type="link"
              class="ibs-mr8 ibs-pl0"
              :data-seq="showQuestion.seq"
              @click="showEditModal"
            >
              <i class="ib-icon ib-icon-setting"></i>{{ t("Edit") }}</a-button
            >
            <a-button
              type="link"
              class="ibs-mr8"
              :data-seq="showQuestion.seq"
              @click="deleteSub"
              ><i class="ib-icon ib-icon-delete"></i>{{ t("Delete") }}
            </a-button>
          </div>
        </a-col>
      </a-row>
    </div>
    <hr class="ibs-sub-divider" />
  </div>
</template>

<script>
import Locale from "common/vue/mixins/locale";
import judgeType from "../../item-engine-components/judge";
import singleChoice from "../../item-engine-components/single-choice";
import choice from "../../item-engine-components/choice";
import essay from "../../item-engine-components/essay";
import fill from "../../item-engine-components/fill";

const base = {
  stem: "",
  seq: 5,
  score: "2.0",
  answer: [],
  analysis: "",
  answer_mode: ""
};
export default {
  name: "preview-model",
  components: {
    judgeType,
    singleChoice,
    choice,
    essay,
    fill
  },
  mixins: [Locale],
  data() {
    return {
      subItem: "",
      groupStyle: {
        pointerEvents: "none"
      },
      showQuestion: {},
      mode: "preview"
    };
  },
  provide() {
    return {
      modeOrigin: "preview"
    };
  },
  props: {
    questions: {
      type: Object,
      default: () => base
    },
    index: {
      type: Number,
      default: () => 0
    }
  },
  watch: {
    questions: {
      handler(n) {
        this.showQuestion = Object.assign({}, n);
        if (this.showQuestion.answer_mode === "text") {
          this.fillTypeData();
        }
      }
    }
  },
  mounted() {
    this.showQuestion = Object.assign({}, this.questions);
    if (this.showQuestion.answer_mode === "text") {
      this.fillTypeData();
    }
  },
  methods: {
    filterFillHtml(text) {
      // stem 是否包含答案形式
      if (!text.match(/\[\[.+?\]\]/g)) {
        return;
      }
      let index = 1;
      return text.replace(/\[\[.+?\]\]/g, function() {
        return `<span class="ibs-stem-fill-blank">(${index++})</span>`;
      });
    },
    filterFillAnswer(data) {
      return data.map(item => {
        console.log(item);
        return item.replace(/\|/g, this.t("Or"));
      });
    },
    fillTypeData() {
      this.showQuestion.stem = this.filterFillHtml(this.showQuestion.stem);
      this.showQuestion.answer = this.filterFillAnswer(
        this.showQuestion.answer
      );
    },
    showEditModal(e) {
      const target = e.target;
      const currentSeq = target.getAttribute("data-seq");
      this.$emit("getSeq", {
        seq: currentSeq - 1,
        operate: "edit"
      });
    },
    deleteSub(e) {
      const target = e.target;
      const currentSeq = target.getAttribute("data-seq");
      this.$emit("getSeq", { seq: currentSeq - 1, operate: "delete" });
    }
  }
};
</script>
