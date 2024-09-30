<template>
  <a-form :form="form">
    <answer-model
      @getFromInfo="getFromInfo"
      :questions="questions"
      :form="form"
      :mode="mode"
      :isSubItem="isSubItem"
      v-bind="$attrs"
      :isDisable="isDisable"
      :aiAnalysisEnable="aiAnalysisEnable"
      :errorList="errorList"
      @changeEditor="changeEditor"
      @renderFormula="renderFormula"
      @getInitRepeatQuestion="getInitRepeatQuestion"
      @getAiAnalysis="getAiAnalysis"
    >
      <template v-slot:response_points>
        <a-form-item
          :label="t('Answer')"
          :label-col="{ span: 4 }"
          :wrapper-col="{ span: 16 }"
          required
        >
          <a-radio-group
            name="radioGroup"
            @change="setAnswer"
            v-decorator="[
              'answer',
              {
                initialValue: answer,
                rules: [{ validator: checkJudge }],
                trigger: 'blur'
              }
            ]"
          >
            <a-radio
              v-for="(item, index) in questionsSelect"
              :value="item.radio.val"
              :key="index"
              >{{ item.radio.text }}
            </a-radio>
          </a-radio-group>
        </a-form-item>
      </template>
    </answer-model>
  </a-form>
</template>

<script>
import answerModel from "./answer-model";
import Emitter from "common/vue/mixins/emitter";
import Locale from "common/vue/mixins/locale";
const base = {
  stem: "",
  score: 2,
  answer: [],
  analysis: "",
  answer_mode: "true_false",
  attachments: []
};
export default {
  name: "judge",
  mixins: [Emitter, Locale],
  components: {
    answerModel
  },
  data() {
    return {
      form: this.$form.createForm(this, { name: "base-question-type" }),
      responseoints: {
        response_points: [
          {
            radio: {
              val: "T",
              text: this.t("Right")
            }
          },
          {
            radio: {
              val: "F",
              text: this.t("Wrong")
            }
          }
        ]
      }
    };
  },
  inject: ["bank_id", "subject"],
  props: {
    subQuestions: {
      type: Object,
      default: function() {
        return Object.assign(base, this.responseoints);
      }
    },
    isSubItem: {
      type: Boolean,
      default: false
    },
    mode: {
      type: String,
      default: "create"
    },
    isDisable: {
      type: Boolean,
      default: false
    },
    aiAnalysisEnable: {
      type: Boolean,
      default: false
    },
    errorList: {
      type: Array,
      default() {
        return [];
      }
    }
  },
  computed: {
    questions: function() {
      if (this.mode === "edit") {
        if (this.isSubItem) {
          return this.subQuestions;
        }
        return this.subject.questions[0];
      }
      return Object.assign(base, this.responseoints);
    },
    answer: function() {
      if (this.questions.answer.length) {
        return this.questions.answer[0];
      }
      return "";
    },
    questionsSelect: function() {
      return this.questions.response_points;
    }
  },
  methods: {
    setAnswer(e) {
      this.form.setFieldsValue({ answer: e.target.value });
    },
    checkJudge(rule, value, callback) {
      if (!value) {
        callback(this.t("itemManage.judgeRule"));
      } else {
        callback();
      }
    },
    getFromInfo() {
      this.form.validateFieldsAndScroll((err, values) => {
        if (!err) {
          this.formateData(values);
        }
      });
    },
    formateData(values) {
      let question = JSON.parse(JSON.stringify(this.questions));
      //word导入错误删除
      if (question.errors) {
        delete question.errors;
      }
      if (this.isSubItem) {
        let data = {};
        question = Object.assign(question, values.questions);
        question.answer = [values.answer];
        data.questions = [question];
        this.$emit("patchData", data);
      } else {
        let data = JSON.parse(JSON.stringify(this.subject));
        data = Object.assign(data, values.base);
        question = Object.assign(question, values.questions);
        question.answer = [values.answer];
        question.answer_mode = "true_false";
        data.questions = [question];
        console.log(data);
        this.$emit("patchData", data);
      }
      // window.location.reload();
    },
    changeEditor(data) {
      this.$emit("changeEditor", data);
    },
    getInitRepeatQuestion() {
      this.$emit("getInitRepeatQuestion");
    },
    renderFormula() {
      this.$emit("renderFormula");
    },
    getAiAnalysis(disable, enable, complete, finish) {
      let data = {};
      this.form.validateFieldsAndScroll((err, values) => {
        if (!err) {
          let question = JSON.parse(JSON.stringify(this.questions));
          question = Object.assign(question, values.questions);
          data.stem = question.stem;
          data.answer = values.answer === "T" ? "正确" : "错误";
          if (this.isSubItem) {
            data.type = "material-determine";
          } else {
            data.type = "determine";
          }
        }
      });
      this.$emit("getAiAnalysis", data, disable, enable, complete, finish);
    },
  }
};
</script>
