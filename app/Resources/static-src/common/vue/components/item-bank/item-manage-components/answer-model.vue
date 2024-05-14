<template>
  <base-type
    @getFromInfo="getFromInfo"
    :form="form"
    v-bind="$attrs"
    :mode="mode"
    :isDisable="isDisable"
    :errorList="errorList"
    @clickConfirm="clickConfirm"
    @closeConfirm="closeConfirm"
    @renderFormula="renderFormula"
    @getInitRepeatQuestion="getInitRepeatQuestion"
  >
    <template v-slot:questions>
      <slot name="stem">
        <a-form-item
          :label="t('Stem')"
          :label-col="{ span: 4 }"
          :wrapper-col="{ span: 16 }"
          required
          :validate-status="stem.validateStatus"
          :help="stem.errorMsg"
        >
          <div :class="{ 'is-wrong': isWrong }">
            <a-textarea
              :data-image-download-url="
                showCKEditorData.filebrowserImageDownloadUrl
              "
              :rows="0"
              v-decorator="[
                `questions['stem']`,
                {
                  initialValue: questions.stem,
                  rules: [{ required: true }]
                }
              ]"
            />
            <div v-if="isWrong" class="wrong-text">
              {{ t("wrongText") }}
            </div>
          </div>
        </a-form-item>
      </slot>
      <a-form-item
        v-if="showAttachment"
        :label="t('itemManage.StemAttachment')"
        :label-col="{ span: 4 }"
        :wrapper-col="{ span: 16 }"
      >
        <attachment-upload
          bodyDom="item-bank-create"
          :mode="mode"
          module="stem"
          :fileShowData="getAttachmentTypeData('stem')"
          :cdnHost="cdnHost"
          :uploaderData="uploadSDKInitData"
          @deleteFile="deleteFile"
          @onFileSort="onFileSort"
          @getFileInfo="getFileInfo"
        ></attachment-upload>
      </a-form-item>

      <slot name="response_points"></slot>

      <a-collapse :bordered="false">
        <template v-slot:expandIcon="props">
          <div :rotate="props.isActive ? 90 : 0"></div>
        </template>
        <a-collapse-panel
          :header="t('itemManage.ToggleShowChoose')"
          key="1"
          :style="customStyle"
          :forceRender="true"
        >
          <a-form-item
            :label="t('Explain')"
            :label-col="{ span: 4 }"
            :wrapper-col="{ span: 16 }"
          >
            <a-textarea
              :rows="4"
              :data-image-download-url="
                showCKEditorData.filebrowserImageDownloadUrl
              "
              v-decorator="[
                `questions['analysis']`,
                { initialValue: questions.analysis }
              ]"
            />
          </a-form-item>

          <a-form-item
            v-if="showAttachment"
            :label="t('ExplainAttachment')"
            :label-col="{ span: 4 }"
            :wrapper-col="{ span: 16 }"
          >
            <attachment-upload
              bodyDom="item-bank-create"
              :mode="mode"
              module="analysis"
              :cdnHost="cdnHost"
              :uploaderData="uploadSDKInitData"
              :fileShowData="getAttachmentTypeData('analysis')"
              @deleteFile="deleteFile"
              @onFileSort="onFileSort"
              @getFileInfo="getFileInfo"
            ></attachment-upload>
          </a-form-item>

          <a-form-item
            v-if="itemType === 'fill'"
            :label="t('Score')"
            :label-col="{ span: 4 }"
            :wrapper-col="{ span: 16 }"
          >
            <a-input
              style="width: 200px"
              type="number"
              v-decorator="[
                `questions['otherScore']`,
                {
                  rules: [{ validator: checkScore }],
                  initialValue: questions.otherScore || 2
                }
              ]"
            />
          </a-form-item>

          <a-form-item
            v-else
            :label="t('Score')"
            :label-col="{ span: 4 }"
            :wrapper-col="{ span: 16 }"
          >
            <a-input
              style="width: 200px"
              type="number"
              v-decorator="[
                `questions['score']`,
                {
                  rules: [{ validator: checkScore }],
                  initialValue: questions.score || 2
                }
              ]"
            />
          </a-form-item>

          <a-form-item
            v-if="['choice', 'uncertain_choice'].includes(itemType)"
            :wrapper-col="{ span: 16, offset: 4 }"
            :help="scoreTypeHelp"
            :validate-status="scoreTypeValidateStatus"
          >
            <a-radio-group
              name="scoreType"
              v-decorator="[
                `questions['scoreType']`,
                {
                  rules: [{ validator: checkOtherScore }],
                  initialValue: questions.scoreType
                }
              ]"
            >
              <a-radio value="question">
                漏选得分
                <a-input-number
                  :min="0"
                  v-decorator="[
                    `questions['otherScore1']`,
                    {
                      initialValue:
                        questions.scoreType === 'question'
                          ? questions.otherScore
                          : 0
                    }
                  ]"
                />
              </a-radio>
              <a-radio value="option">
                每个正确选项得分
                <a-input-number
                  :min="0"
                  v-decorator="[
                    `questions['otherScore2']`,
                    {
                      initialValue:
                        questions.scoreType === 'option'
                          ? questions.otherScore
                          : 0
                    }
                  ]"
                />
              </a-radio>
            </a-radio-group>
          </a-form-item>

          <a-form-item
            v-if="itemType === 'fill'"
            :wrapper-col="{ span: 16, offset: 4 }"
          >
            <a-radio-group
              name="scoreType"
              v-decorator="[
                `questions['scoreType']`,
                {
                  initialValue: questions.scoreType || 'question'
                }
              ]"
            >
              <a-radio value="question">
                每题得分
              </a-radio>
              <a-radio value="option">
                每空得分
              </a-radio>
            </a-radio-group>
          </a-form-item>
        </a-collapse-panel>
      </a-collapse>
    </template>
  </base-type>
</template>

<script>
import baseType from "./base";
import attachmentUpload from "../attachment-upload";
import loadScript from "load-script";
import Emitter from "common/vue/mixins/emitter";
import Locale from "common/vue/mixins/locale";

const base = {
  stem: "",
  seq: 5,
  score: "2.0",
  answer: [],
  analysis: "",
  attachments: []
};
export default {
  name: "answer-model",
  components: { baseType, attachmentUpload },
  mixins: [Emitter, Locale],
  data() {
    return {
      stem: {
        validateStatus: "",
        errorMsg: ""
      },
      customStyle: "background: #ffffff;border: 0",
      publicPath: process.env.BASE_URL,
      stemEditor: "",
      analysisEditor: "",
      inheritAttrs: false,
      scoreTypeHelp: "",
      scoreTypeValidateStatus: "success",
      isWrong: false
    };
  },
  props: {
    questions: {
      type: Object,
      default: () => base
    },
    form: {
      type: Object,
      default: () => {}
    },
    itemType: {
      type: String,
      default: ""
    },
    mode: {
      type: String,
      default: "create"
    },
    isDisable: {
      type: Boolean,
      default: false
    },
    answer: {
      type: Array,
      default: () => []
    },
    errorList: {
      type: Array,
      default() {
        return [];
      }
    }
  },
  inject: [
    "showCKEditorData",
    "showAttachment",
    "uploadSDKInitData",
    "cdnHost"
  ],
  created() {},
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
          // 填空题自定义题干
          if (this.itemType !== "fill") {
            this.initBaseStem();
          }
          this.initBaseAnalysis();
        });
      });
    });
  },
  methods: {
    initBaseStem() {
      this.stemEditor = window.CKEDITOR.replace(
        "base-question-type_questions['stem']",
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
      this.stemEditor.setData(this.questions.stem);
      this.stemEditor.on("change", () => {
        const data = this.stemEditor.getData();
        if (this.errorList.length == 0) {
          this.$emit("changeEditor", data);
        }
        this.form.setFieldsValue({ [`questions['stem']`]: data });
        this.checkStem(data);
      });
      this.stemEditor.on("blur", () => {
        const data = this.stemEditor.getData();
        this.form.setFieldsValue({ [`questions['stem']`]: data });
        this.checkStem(data);
      });
    },
    initBaseAnalysis() {
      this.analysisEditor = window.CKEDITOR.replace(
        "base-question-type_questions['analysis']",
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
      this.analysisEditor.setData(this.questions.analysis);
      this.analysisEditor.on("change", () => {
        this.form.setFieldsValue({
          [`questions['analysis']`]: this.analysisEditor.getData()
        });
        this.outanalysis = this.analysisEditor.getData();
      });
      this.analysisEditor.on("blur", () => {
        this.form.setFieldsValue({
          [`questions['analysis']`]: this.analysisEditor.getData()
        });
        this.outanalysis = this.analysisEditor.getData();
      });
    },
    checkScore(rule, value, callback) {
      const fractionRule = /^(([1-9]{1}\d{0,2})|([0]{1}))(\.(\d){1})?$/;
      if (value < 0) {
        callback(this.t("ScoreRule.one"));
      } else if (value > 999) {
        callback(this.t("ScoreRule.two"));
      } else if (!fractionRule.test(value)) {
        callback(this.t("ScoreRule.three"));
      } else {
        callback();
      }
    },

    checkOtherScore(rule, value, callback) {
      const fieldNames = [
        "questions['score']",
        "questions['otherScore1']",
        "questions['otherScore2']",
        "questions['scoreType']"
      ];
      const values = this.form.getFieldsValue(fieldNames);
      const { score, otherScore1, otherScore2, scoreType } = values.questions;

      // 漏选得分校验
      if (scoreType === "question" && otherScore1 > score) {
        const errorText = "漏选得分不得大于总分";
        this.scoreTypeHelp = errorText;
        this.scoreTypeValidateStatus = "error";
        callback(errorText);
        return;
      }

      // 每个正确选项得分
      const tempScore = otherScore2 * this.answer.length;
      if (scoreType === "option" && tempScore > score) {
        const errorText = "每项得分乘以正确选项数不得大于总分";
        this.scoreTypeHelp = errorText;
        this.scoreTypeValidateStatus = "error";
        callback(errorText);
        return;
      }

      this.scoreTypeHelp = "";
      this.scoreTypeValidateStatus = "success";
      callback();
    },

    checkStem(value) {
      if (!value) {
        this.stem.validateStatus = "error";
        this.stem.errorMsg = this.t("itemManage.StemRule");
      } else {
        this.stem.validateStatus = "";
        this.stem.errorMsg = "";
      }
    },
    getFromInfo() {
      this.checkStem(this.form.getFieldValue(`questions['stem']`));
      this.$emit("getFromInfo");
    },
    getFileInfo(file) {
      this.questions.attachments.push(file);
      this.$emit("getUploadFile", file);
    },
    onFileSort(attachments) {
      this.$set(this.questions, "attachments", attachments);
    },
    deleteFile(fileId) {
      const attachments = this.questions.attachments.filter(item => {
        return item.id !== fileId;
      });

      this.$set(this.questions, "attachments", attachments);
    },
    getAttachmentTypeData(type) {
      if (this.mode === "create") {
        return [];
      }

      const result = this.questions.attachments.filter(item => {
        return item.module == type;
      });

      return result;
    },
    clickConfirm() {
      this.isWrong = true;
    },
    closeConfirm() {
      this.isWrong = false;
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
