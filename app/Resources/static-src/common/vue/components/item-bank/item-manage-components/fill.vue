<template>
  <a-form :form="form">
    <answer-model
      @getFromInfo="getFromInfo"
      :questions="questions"
      :form="form"
      :mode="mode"
      :isSubItem="isSubItem"
      v-bind="$attrs"
      :itemType="type"
      :isDisable="isDisable"
      :aiAnalysisEnable="aiAnalysisEnable"
      :errorList="errorList"
      @changeEditor="changeEditor"
      @renderFormula="renderFormula"
      @getInitRepeatQuestion="getInitRepeatQuestion"
      @prepareTeacherAiAnalysis="prepareTeacherAiAnalysis"
    >
      <template v-slot:stem>
        <a-form-item
          :label="t('Stem')"
          :label-col="{ span: 4 }"
          :wrapper-col="{ span: 16 }"
          required
          :validate-status="stemText.validateStatus"
          :help="stemText.errorMsg"
        >
          <a-textarea
            :rows="0"
            :data-image-download-url="
              showCKEditorData.filebrowserImageDownloadUrl
            "
            v-decorator="[
              `questions['stem']`,
              {
                initialValue: questions.stem,
                rules: [{ required: true }]
              }
            ]"
          />
          <div class="ibs-dark-assist" v-show="!stemText.validateStatus">
            {{ t("itemManage.clickEditOrButton")
            }}<span class="ibs-danger-color"> [ ] </span
            >{{ t("itemManage.insertByself") }}
          </div>
          <div v-for="(answer, index) in answers" :key="index">
            <span class="font-medium">{{ `答案[${index + 1}]:` }} </span>
            <span style="color: #999">{{ answer }}</span
            ><br />
          </div>
        </a-form-item>

        <a-form-item
          :label="t('caseSensitiveShow')"
          :label-col="{ span: 4 }"
          :wrapper-col="{ span: 16 }"
        >
          <a-checkbox
            ref="initMessage"
            v-decorator="[
              `questions['case_sensitive']`,
              {
                initialValue: questions.case_sensitive == 0 ? false : true
              }
            ]"
            :default-checked="questions.case_sensitive == 0 ? false : true"
            @click="checkItem"
          ></a-checkbox>
          <div class="ibs-dark-assist" id="checkbox" v-html="message"></div>
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
  response_points: [],
  answer: [],
  analysis: "",
  answer_mode: "text",
  attachments: [],
  case_sensitive: true,
  scoreType: "question",
  otherScore: 2
};
export default {
  name: "fill",
  mixins: [Emitter, Locale],
  components: {
    answerModel
  },
  data() {
    return {
      stemText: {
        validateStatus: "",
        errorMsg: ""
      },
      message: this.t("itemManage.caseSensitiveClues"),
      checked: true,
      type: "fill",
      publicPath: process.env.BASE_URL,
      form: this.$form.createForm(this, { name: "base-question-type" }),
      answers: [],
    };
  },
  inject: ["bank_id", "subject", "showCKEditorData", "showAttachment"],
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
      return base;
    },
    answer: function() {
      return this.getFillAnswer(this.stem.value);
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
          this.initBaseStem();
        });
      });
      this.initMessage();
    });
  },
  methods: {
    initMessage() {
      this.message = this.$refs.initMessage.value
        ? this.t("itemManage.caseSensitiveClues")
        : this.t("itemManage.ignoringCase");
    },
    initBaseStem() {
      this.stemEditor = window.CKEDITOR.replace(
        "base-question-type_questions['stem']",
        {
          toolbar: "Question",
          fileSingleSizeLimit: this.showCKEditorData.fileSingleSizeLimit,
          filebrowserImageUploadUrl: this.showCKEditorData
            .filebrowserImageUploadUrl,
          filebrowserImageDownloadUrl: this.showCKEditorData
            .filebrowserImageDownloadUrl,
          language: this.showCKEditorData.language
        }
      );
      if (this.mode == "edit" && !this.isSubItem) {
        this.questions.stem = this.getEditFillStem(this.questions.stem);
        this.answers = this.getFillAnswer(this.questions.stem);
      }
      this.stemEditor.setData(this.questions.stem);
      this.stemEditor.on("change", () => {
        const data = this.stemEditor.getData();
        this.answers = this.getFillAnswer(data);
        if (this.errorList.length == 0) {
          this.$emit("changeEditor", data);
        }
        this.form.setFieldsValue({
          [`questions['stem']`]: data
        });
        this.checkFillRule(data);
        this.form.setFieldsValue({
          [`questions['stem']`]: this.stemEditor.getData()
        });
      });
    },

    checkItem(event) {
      if (event.target.checked) {
        this.subQuestions.case_sensitive = true;
        this.message = this.t("itemManage.caseSensitiveClues");
      } else {
        this.subQuestions.case_sensitive = false;
        this.message = this.t("itemManage.ignoringCase");
      }
    },
    checkFillRule(value) {
      if (/(\[\[(.+?)\]\])/i.test(value)) {
        this.stemText.validateStatus = "";
        this.stemText.errorMsg = "";
        return true;
      } else {
        this.stemText.validateStatus = "error";
        this.stemText.errorMsg = this.t("itemManage.fillRule");
        return false;
      }
    },
    reverseStr(value) {
      let newStr = "";
      for (let i = 0; i < value.length; i++) {
        let s = value.charAt(value.length - 1 - i);
        newStr += s;
      }

      return newStr;
    },
    getFillAnswer(value) {
      let result = [];
      let regex = /\[\[(?:[^[\]]+|\[[^[\]]+\])+\]\]/g;
      let match;

      while ((match = regex.exec(value)) !== null) {
        let content = match[0];
        result.push(content.substring(2, content.length - 2));
      }

      return result;
    },
    getEditFillStem(str) {
      let index = 0;
      const self = this;
      return str.replace(/\[\[(?:[^[\]]+|\[[^[\]]+\])+\]\]/g, function() {
        return `[[${self.questions.answer[index++]}]]`;
      });
    },
    getFromInfo() {
      if (!this.checkFillRule(this.form.getFieldValue(`questions['stem']`))) {
        return;
      }
      this.form.validateFieldsAndScroll((err, values) => {
        if (!err) {
          this.formateData(values);
        }
      });
    },
    formateData(values) {
      let question = JSON.parse(JSON.stringify(this.questions));
      const { stem, otherScore, scoreType } = values.questions;
      const answer = this.getFillAnswer(stem);
      values.questions.score =
        scoreType === "question"
          ? otherScore
          : otherScore * (answer.length || 0);
      //word导入错误删除
      if (question.errors) {
        delete question.errors;
      }
      if (this.isSubItem) {
        let data = {};
        question = Object.assign(question, values.questions);
        question.answer = this.getFillAnswer(question.stem);
        const temp = {
          text: []
        };
        const response_points = [];
        question.answer.forEach(function() {
          response_points.push(temp);
        });
        question.response_points = response_points;
        data.questions = [question];
        this.$emit("patchData", data);
      } else {
        let data = JSON.parse(JSON.stringify(this.subject));
        data = Object.assign(data, values.base);
        question = Object.assign(question, values.questions);
        question.answer = this.getFillAnswer(question.stem);
        question.answer_mode = "text";
        const temp = {
          text: []
        };
        const response_points = [];
        question.answer.forEach(function() {
          response_points.push(temp);
        });
        question.response_points = response_points;
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
    prepareTeacherAiAnalysis(gen) {
      let data = {};
      this.form.validateFieldsAndScroll((err, values) => {
        if (!err) {
          let question = JSON.parse(JSON.stringify(this.questions));
          question = Object.assign(question, values.questions);
          data.stem = question.stem;
          data.answers = this.getFillAnswer(question.stem);
          if (this.isSubItem) {
            data.type = "material-fill";
            this.$emit('prepareTeacherAiAnalysis', data, gen);
          } else {
            data.type = "fill";
            gen(data);
          }
        }
      });
    }
  }
};
</script>
