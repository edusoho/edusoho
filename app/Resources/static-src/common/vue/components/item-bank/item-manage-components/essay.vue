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
      :errorList="errorList"
      @changeEditor="changeEditor"
      @renderFormula="renderFormula"
      @getInitRepeatQuestion="getInitRepeatQuestion"
    >
      <template v-slot:response_points>
        <a-form-item
          :label="t('Answer')"
          :label-col="{ span: 4 }"
          :wrapper-col="{ span: 16 }"
          required
          :validate-status="answerText.validateStatus"
          :help="answerText.errorMsg"
        >
          <a-textarea
            :rows="0"
            :data-image-download-url="
              showCKEditorData.filebrowserImageDownloadUrl
            "
            v-decorator="[
              `questions['answer']`,
              {
                initialValue: answer,
                rules: [{ required: true }]
              }
            ]"
          />
        </a-form-item>
      </template>
    </answer-model>
  </a-form>
</template>

<script>
import loadScript from "load-script";
import answerModel from "./answer-model";
import Emitter from "common/vue/mixins/emitter";
import Locale from "common/vue/mixins/locale";
const base = {
  stem: "",
  score: 2,
  response_points: [
    {
      rich_text: {}
    }
  ],
  answer: [],
  analysis: "",
  answer_mode: "rich_text",
  attachments: []
};
export default {
  name: "essay",
  mixins: [Emitter, Locale],
  components: {
    answerModel
  },
  data() {
    return {
      answerText: {
        validateStatus: "",
        errorMsg: ""
      },
      publicPath: process.env.BASE_URL,
      form: this.$form.createForm(this, { name: "base-question-type" })
    };
  },
  inject: ["bank_id", "subject", "showCKEditorData"],
  props: {
    subQuestions: {
      type: Object,
      default: () => base
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
      return base;
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
  mounted() {
    this.$nextTick(() => {
      loadScript(this.showCKEditorData.jqueryPath, err => {
        if (err) {
          console.log(err);
        }
        loadScript(this.showCKEditorData.publicPath, err => {
          if (err) {
            console.log(err);
          }
          this.initAnswer();
        });
      });
    });
  },
  methods: {
    initAnswer() {
      this.answerEditor = window.CKEDITOR.replace(
        "base-question-type_questions['answer']",
        {
          toolbar: "Minimal",
          fileSingleSizeLimit: this.showCKEditorData.fileSingleSizeLimit,
          filebrowserImageUploadUrl: this.showCKEditorData
            .filebrowserImageUploadUrl,
          filebrowserImageDownloadUrl: this.showCKEditorData
            .filebrowserImageDownloadUrl,
          language: this.showCKEditorData.language
        }
      );
      this.answerEditor.setData(this.answer);
      this.answerEditor.on("change", () => {
        const data = this.answerEditor.getData();
        this.form.setFieldsValue({
          [`questions['answer']`]: data
        });
        this.checkAnswer(data);
      });
      this.answerEditor.on("blur", () => {
        const data = this.answerEditor.getData();
        this.form.setFieldsValue({
          [`questions['answer']`]: data
        });
        this.checkAnswer(data);
      });
    },

    checkAnswer(value) {
      if (!value) {
        this.answerText.validateStatus = "error";
        this.answerText.errorMsg = this.t("itemManage.essayRule");
      } else {
        this.answerText.validateStatus = "";
        this.answerText.errorMsg = "";
      }
    },
    getFromInfo() {
      this.checkAnswer(this.form.getFieldValue(`questions['answer']`));
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
        const tempArr = [];
        question.answer = tempArr.concat(question.answer);
        data.questions = [question];
        this.$emit("patchData", data);
      } else {
        let data = JSON.parse(JSON.stringify(this.subject));
        data = Object.assign(data, values.base);
        question = Object.assign(question, values.questions);
        const tempArr = [];
        question.answer = tempArr.concat(question.answer);
        question.answer_mode = "rich_text";
        data.questions = [question];
        this.$emit("patchData", data);
      }
    },
    changeEditor(data) {
      this.$emit("changeEditor", data);
    },
    getInitRepeatQuestion() {
      this.$emit("getInitRepeatQuestion");
    },
    renderFormula() {
      this.$emit("renderFormula");
    }
  }
};
</script>
