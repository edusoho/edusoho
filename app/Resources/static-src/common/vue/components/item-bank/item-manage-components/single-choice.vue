<template>
  <a-form :form="form">
    <answer-model
      @getFromInfo="getFromInfo"
      :questions="questions"
      :mode="mode"
      :form="form"
      :isSubItem="isSubItem"
      v-bind="$attrs"
      :isDisable="isDisable"
      :errorList="errorList"
      @changeEditor="changeEditor"
      @renderFormula="renderFormula"
      @getInitRepeatQuestion="getInitRepeatQuestion"
    >
      <template v-slot:response_points>
        <div class="ibs-choice-wrap">
          <a-radio-group name="radioGroup" @change="setAnswer" v-model="answer">
            <a-form-item
              v-for="(k, index) in form.getFieldValue('keys')"
              :key="k.radio.val"
              class="choice-item"
              :label="getChoiceLabelNum(optionKey[index])"
              required
              :label-col="{ span: 4 }"
              :wrapper-col="{ span: 16 }"
              :validate-status="
                optionsEditorRule[k.radio.val].validateStatus || ''
              "
              :help="optionsEditorRule[k.radio.val].errorMsg || ''"
            >
              <a-textarea
                :data-image-download-url="
                  showCKEditorData.filebrowserImageDownloadUrl
                "
                v-decorator="[
                  `options[${k.radio.val}]`,
                  {
                    initialValue: k.radio.text,
                    rules: [{ required: true }]
                  }
                ]"
                placeholder
                :rows="0"
              />
              <a-radio :value="k.radio.val">{{ t("RightAnswer") }}</a-radio>
              <a-button
                class="ibs-delete-btn"
                size="small"
                @click="() => remove(index, k)"
              >
                <i class="ib-icon ib-icon-delete"></i>
              </a-button>
            </a-form-item>
          </a-radio-group>
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
      radio: {
        val: 0,
        text: ""
      }
    },
    {
      radio: {
        val: 1,
        text: ""
      }
    },
    {
      radio: {
        val: 2,
        text: ""
      }
    },
    {
      radio: {
        val: 3,
        text: ""
      }
    }
  ],
  answer: [""],
  analysis: "",
  answer_mode: "single_choice",
  attachments: []
};
export default {
  name: "single-choice",
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
      answer: "",
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
    mode: {
      type: String,
      default: "create"
    },
    optionsConfiguration: {
      type: Object,
      default: () => {
        return { maxItem: 20, minItem: 2 };
      }
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
    setAnswer(e) {
      this.answer = e.target.value;
    },
    getQuestionsSelect() {
      let response_points = JSON.parse(
        JSON.stringify(this.questions.response_points)
      );
      if (this.mode === "edit") {
        response_points.forEach((item, index) => {
          item.radio.val = index;
        });
      }
      return response_points;
    },
    getAnswer() {
      if (this.mode === "edit") {
        return this.optionsIndex[this.questions.answer[0]];
      }
      return "";
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
        this.initOptionsEditorRule(item.radio.val);
        if (this.optionsEditor[`${item.radio.val}`]) {
          return;
        }
        this.optionsEditor[`${item.radio.val}`] = window.CKEDITOR.replace(
          `base-question-type_options[${item.radio.val}]`,
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
        this.optionsEditor[`${item.radio.val}`].on("change", () => {
          let data = this.optionsEditor[`${item.radio.val}`].getData();
          this.form.setFieldsValue({ [`options[${item.radio.val}]`]: data });
          this.checkOptionText(item.radio.val, data);
        });
        this.optionsEditor[`${item.radio.val}`].on("blur", () => {
          let data = this.optionsEditor[`${item.radio.val}`].getData();
          this.form.setFieldsValue({ [`options[${item.radio.val}]`]: data });
          this.checkOptionText(item.radio.val, data);
        });
      });
    },
    //选项校验规则
    initOptionsEditorRule() {
      this.form.getFieldValue("keys").forEach(item => {
        if (this.optionsEditorRule[`${item.radio.val}`]) {
          return;
        }
        this.optionsEditorRule[`${item.radio.val}`] = {
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
        let data = this.form.getFieldValue(`options[${item.radio.val}]`);
        this.checkOptionText(item.radio.val, data);
      });
    },
    checkAnswer() {
      if (!this.answer && this.answer !== 0) {
        this.$message.error(this.t("itemManage.chooseRule.two"));
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
      let val = keys[keys.length - 1].radio.val + 1;
      const nextKeys = keys.concat({
        radio: {
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
        keys: keys.filter(key => key.radio.val !== k.radio.val)
      });
      if (this.answer === k.radio.val) {
        this.answer = "";
      }
      this.optionsEditor[k.radio.val] = null;
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
    formatetAnswer(keys) {
      let arr = [];
      keys.forEach((item, index) => {
        if (this.answer === item.radio.val) {
          arr.push(this.optionKey[index]);
        }
      });
      return arr;
    },
    //格式化选项
    formateOptions(options) {
      let response_points = [];
      options = options.filter(key => key); //过滤空数据
      options.forEach((item, index) => {
        response_points.push({
          radio: {
            val: this.optionKey[index],
            text: item
          }
        });
      });
      return response_points;
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
        question.response_points = this.formateOptions(values.options);
        question.answer = this.formatetAnswer(values.keys);
        data.questions = [question];
        this.$emit("patchData", data);
      } else {
        let data = JSON.parse(JSON.stringify(this.subject));
        data = Object.assign(data, values.base);
        question = Object.assign(question, values.questions);
        question.response_points = this.formateOptions(values.options);
        question.answer = this.formatetAnswer(values.keys);
        question.answer_mode = "single_choice";
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
