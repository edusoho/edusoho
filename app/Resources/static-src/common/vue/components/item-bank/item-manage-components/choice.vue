<template>
  <a-form :form="form">
    <answer-model
      @getFromInfo="getFromInfo"
      :questions="questions"
      :form="form"
      :mode="mode"
      :isSubItem="isSubItem"
      :answer="answer"
      v-bind="$attrs"
      :itemType="type"
      :isDisable="isDisable"
      :errorList="errorList"
      @changeEditor="changeEditor"
      @renderFormula="renderFormula"
      @getInitRepeatQuestion="getInitRepeatQuestion"
    >
      <template v-slot:response_points>
        <div class="ibs-choice-wrap">
          <a-checkbox-group v-model="answer">
            <a-form-item
              v-for="(k, index) in form.getFieldValue('keys')"
              :key="k.checkbox.val"
              class="choice-item"
              :label="getChoiceLabelNum(optionKey[index])"
              required
              :label-col="{ span: 4 }"
              :wrapper-col="{ span: 16 }"
              :validate-status="
                optionsEditorRule[k.checkbox.val].validateStatus || ''
              "
              :help="optionsEditorRule[k.checkbox.val].errorMsg || ''"
            >
              <a-textarea
                :data-image-download-url="
                  showCKEditorData.filebrowserImageDownloadUrl
                "
                v-decorator="[
                  `options[${k.checkbox.val}]`,
                  {
                    initialValue: k.checkbox.text,
                    rules: [{ required: true }]
                  }
                ]"
                placeholder=""
                :rows="0"
              />
              <a-checkbox :value="k.checkbox.val">{{
                t("RightAnswer")
              }}</a-checkbox>
              <a-button
                class="ibs-delete-btn"
                size="small"
                @click="() => remove(index, k)"
                ><i class="ib-icon ib-icon-delete"></i
              ></a-button>
            </a-form-item>
          </a-checkbox-group>
          <a-row>
            <a-col :offset="4" :span="16" class="ibs-text-right">
              <a-button type="primary" @click="add">{{
                t("itemManage.newChoice")
              }}</a-button>
            </a-col>
          </a-row>
        </div>
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
      checkbox: {
        val: 0,
        text: ""
      }
    },
    {
      checkbox: {
        val: 1,
        text: ""
      }
    },
    {
      checkbox: {
        val: 2,
        text: ""
      }
    },
    {
      checkbox: {
        val: 3,
        text: ""
      }
    }
  ],
  answer: [],
  analysis: "",
  answer_mode: "choice",
  attachments: [],
  scoreType: "question",
  otherScore: 0
};
export default {
  name: "choice",
  mixins: [Emitter, Locale],
  components: {
    answerModel
  },
  inject: ["subject", "showCKEditorData"],
  data() {
    return {
      publicPath: process.env.BASE_URL,
      questions: {},
      form: this.$form.createForm(this, { name: "base-question-type" }),
      optionsEditor: {},
      optionsEditorRule: {},
      answer: [],
      optionsIndex: {
        A: 0,
        B: 1,
        C: 2,
        D: 3,
        E: 4,
        F: 5,
        G: 6,
        H: 7,
        I: 8,
        J: 9,
        K: 10,
        L: 11,
        M: 12,
        N: 13,
        O: 14,
        P: 15,
        Q: 16,
        R: 17,
        S: 18,
        T: 19
      },
      optionKey: [
        "A",
        "B",
        "C",
        "D",
        "E",
        "F",
        "G",
        "H",
        "I",
        "J",
        "K",
        "L",
        "M",
        "N",
        "O",
        "P",
        "Q",
        "R",
        "S",
        "T"
      ]
    };
  },
  props: {
    subQuestions: {
      type: Object,
      default: () => base
    },
    isSubItem: {
      type: Boolean,
      default: false
    },
    optionsConfiguration: {
      type: Object,
      default: () => {
        return { maxItem: 20, MinItem: 2 };
      }
    },
    type: {
      type: String,
      default: "choice"
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
  created() {
    this.initData();
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
          this.initEdit();
        });
      });
    });
  },
  methods: {
    getQuestions() {
      if (this.mode === "edit") {
        if (this.isSubItem) {
          return this.subQuestions;
        }
        return this.subject.questions[0];
      }
      return base;
    },
    getQuestionsSelect() {
      let response_points = JSON.parse(
        JSON.stringify(this.questions.response_points)
      );
      if (this.mode === "edit") {
        response_points.forEach((item, index) => {
          item.checkbox.val = index;
        });
      }
      return response_points;
    },
    getAnswer() {
      if (this.mode === "edit") {
        const answer = JSON.parse(JSON.stringify(this.questions.answer));
        answer.forEach((item, index) => {
          answer[index] = this.optionsIndex[item];
        });
        return answer;
      }
      return [];
    },
    initData() {
      this.questions = this.getQuestions();
      this.answer = this.getAnswer();
      this.form.getFieldDecorator("keys", {
        initialValue: this.getQuestionsSelect(),
        preserve: true
      });
      this.initOptionsEditorRule();
    },
    //选项编辑器
    initEdit() {
      this.form.getFieldValue("keys").forEach(item => {
        this.initOptionsEditorRule(item.checkbox.val);
        if (this.optionsEditor[`${item.checkbox.val}`]) {
          return;
        }
        this.optionsEditor[`${item.checkbox.val}`] = window.CKEDITOR.replace(
          `base-question-type_options[${item.checkbox.val}]`,
          {
            toolbar: "Minimal",
            fileSingleSizeLimit: this.showCKEditorData.fileSingleSizeLimit,
            filebrowserImageUploadUrl: this.showCKEditorData
              .filebrowserImageUploadUrl,
            filebrowserImageDownloadUrl: this.showCKEditorData
              .filebrowserImageDownloadUrl,
            language: this.showCKEditorData.language,
            height: 120
          }
        );
        this.optionsEditor[`${item.checkbox.val}`].on("change", () => {
          let data = this.optionsEditor[`${item.checkbox.val}`].getData();
          this.form.setFieldsValue({ [`options[${item.checkbox.val}]`]: data });
          this.checkOptionText(item.checkbox.val, data);
        });
        this.optionsEditor[`${item.checkbox.val}`].on("blur", () => {
          let data = this.optionsEditor[`${item.checkbox.val}`].getData();
          this.form.setFieldsValue({ [`options[${item.checkbox.val}]`]: data });
          this.checkOptionText(item.checkbox.val, data);
        });
      });
    },
    //选项校验规则
    initOptionsEditorRule() {
      this.form.getFieldValue("keys").forEach(item => {
        if (this.optionsEditorRule[`${item.checkbox.val}`]) {
          return;
        }
        this.optionsEditorRule[`${item.checkbox.val}`] = {
          validateStatus: "",
          errorMsg: ""
        };
      });
    },
    checkOptionText(k, value) {
      let validate = {
        validateStatus: "",
        errorMsg: ""
      };
      if (!value) {
        validate.validateStatus = "error";
        validate.errorMsg = this.t("itemManage.chooseRule.one");
      }
      this.$set(this.optionsEditorRule, k, validate);
    },
    checkAllOptionText() {
      this.form.getFieldValue("keys").forEach(item => {
        let data = this.form.getFieldValue(`options[${item.checkbox.val}]`);
        this.checkOptionText(item.checkbox.val, data);
      });
    },
    checkAnswer() {
      const minChoice = this.type === "choice" ? 2 : 1;
      if (this.answer.length < minChoice) {
        const littleChooseText = this.t("itemManage.littleChoose");
        const afterAnswerText = this.t("itemManage.answers");
        this.$message.error(
          `${littleChooseText}${minChoice}${afterAnswerText}`
        );
        return false;
      }
      return true;
    },
    add() {
      const keys = this.form.getFieldValue("keys");
      if (keys.length >= this.optionsConfiguration.maxItem) {
        this.$message.error(this.t("itemManage.chooseRule.three"));
        return;
      }
      let val = keys[keys.length - 1].checkbox.val + 1;
      const nextKeys = keys.concat({
        checkbox: {
          val: val,
          text: ""
        }
      });
      this.form.setFieldsValue({
        keys: nextKeys
      });
      this.initOptionsEditorRule();
      this.$nextTick(() => {
        this.initEdit();
      });
    },
    remove(index, k) {
      const keys = this.form.getFieldValue("keys");
      if (keys.length <= this.optionsConfiguration.minItem) {
        this.$message.error(this.t("itemManage.chooseRule.four"));
        return;
      }
      this.form.setFieldsValue({
        keys: keys.filter(key => key.checkbox.val !== k.checkbox.val)
      });
      this.answer = this.answer.filter(key => key !== k.checkbox.val);
      this.optionsEditor[k.checkbox.val] = null;
    },
    //获取表单数据
    getFromInfo() {
      this.checkAllOptionText();
      this.form.validateFieldsAndScroll((err, values) => {
        if (!err) {
          if (this.checkAnswer()) {
            this.formateData(values);
          }
        }
      });
    },
    //格式化选项
    formateOptions(options) {
      let response_points = [];
      options = options.filter(key => key); //过滤空数据
      options.forEach((item, index) => {
        response_points.push({
          checkbox: {
            val: this.optionKey[index],
            text: item
          }
        });
      });
      return response_points;
    },
    formatetAnswer(keys) {
      let arr = [];
      keys.forEach((item, index) => {
        if (this.answer.indexOf(item.checkbox.val) > -1) {
          arr.push(this.optionKey[index]);
        }
      });
      return arr;
    },
    formateData(values) {
      let question = JSON.parse(JSON.stringify(this.questions));
      // 处理计算方式得分
      const { otherScore1, otherScore2, scoreType } = values.questions;
      const otherScore = scoreType === "question" ? otherScore1 : otherScore2;
      question.otherScore = otherScore || 0;
      //word导入错误删除
      if (question.errors) {
        delete question.errors;
      }
      if (this.isSubItem) {
        let data = {};
        question = Object.assign(question, values.questions);
        question.response_points = this.formateOptions(values.options);
        question.answer = this.formatetAnswer(values.keys);
        question.answer_mode = this.type;
        data.questions = [question];
        this.$emit("patchData", data);
      } else {
        let data = JSON.parse(JSON.stringify(this.subject));
        data = Object.assign(data, values.base);
        question = Object.assign(question, values.questions);
        question.response_points = this.formateOptions(values.options);
        question.answer = this.formatetAnswer(values.keys);
        question.answer_mode = this.type;
        data.questions = [question];
        this.$emit("patchData", data);
        console.log(data);
      }
      // window.location.reload();
    },
    getChoiceLabelNum(index) {
      const localeText = this.t("itemManage.chooseItem");
      return `${localeText}${index}`;
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
