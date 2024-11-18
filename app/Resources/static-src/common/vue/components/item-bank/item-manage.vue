<template>
  <div
    class="item-bank-sdk-message ibs-background-color item-bank-create-form"
    id="item-bank-create"
  >
    <judge-type
      v-if="type === 'determine'"
      :mode="mode"
      :showModelBtn="showModelBtn"
      @patchData="patchData"
      :isDisable="isDisable"
      :aiAnalysisEnable="aiAnalysisEnable"
      :errorList="errorList"
      @changeEditor="changeEditor"
      @renderFormula="renderFormula"
      @getInitRepeatQuestion="getInitRepeatQuestion"
    ></judge-type>
    <single-choice
      v-if="type === 'single_choice'"
      :mode="mode"
      :showModelBtn="showModelBtn"
      @patchData="patchData"
      :isDisable="isDisable"
      :aiAnalysisEnable="aiAnalysisEnable"
      :errorList="errorList"
      @changeEditor="changeEditor"
      @renderFormula="renderFormula"
      @getInitRepeatQuestion="getInitRepeatQuestion"
    ></single-choice>
    <fill-type
      v-if="type === 'fill'"
      :mode="mode"
      :showModelBtn="showModelBtn"
      @patchData="patchData"
      :isDisable="isDisable"
      :aiAnalysisEnable="aiAnalysisEnable"
      :errorList="errorList"
      @changeEditor="changeEditor"
      @renderFormula="renderFormula"
      @getInitRepeatQuestion="getInitRepeatQuestion"
    ></fill-type>
    <essay-type
      v-if="type === 'essay'"
      :mode="mode"
      :showModelBtn="showModelBtn"
      @patchData="patchData"
      :isDisable="isDisable"
      :aiAnalysisEnable="aiAnalysisEnable"
      :errorList="errorList"
      @changeEditor="changeEditor"
      @renderFormula="renderFormula"
      @getInitRepeatQuestion="getInitRepeatQuestion"
    ></essay-type>
    <material-type
      v-if="type === 'material'"
      :showMaterial="true"
      :showAnalysis="true"
      :mode="mode"
      :showModelBtn="showModelBtn"
      :errorList="errorList"
      @previewMaterialAttachment="previewAttachment"
      @patchData="patchData"
      :isDisable="isDisable"
      :aiAnalysisEnable="aiAnalysisEnable"
      @changeEditor="changeEditor"
      @renderFormula="renderFormula"
      @getInitRepeatQuestion="getInitRepeatQuestion"
    ></material-type>
    <choice
      v-if="type === 'choice' || type === 'uncertain_choice'"
      :type="type"
      :mode="mode"
      :showModelBtn="showModelBtn"
      @patchData="patchData"
      :isDisable="isDisable"
      :aiAnalysisEnable="aiAnalysisEnable"
      :errorList="errorList"
      @changeEditor="changeEditor"
      @renderFormula="renderFormula"
      @getInitRepeatQuestion="getInitRepeatQuestion"
    ></choice>
  </div>
</template>

<script>
import judgeType from "./item-manage-components/judge";
import singleChoice from "./item-manage-components/single-choice";
import materialType from "./item-manage-components/material";
import fillType from "./item-manage-components/fill";
import essayType from "./item-manage-components/essay";
import choice from "./item-manage-components/choice";
const subject = {
  id: 1,
  bank_id: 1,
  category_id: 0,
  difficulty: "normal", // simple|normal|difficulty
  material: "", // or ''
  analysis: "",
  attachments: [],
  type: "",
  questions: []
};
const baseCKEditorData = {
  publicPath: `${process.env.BASE_URL}/es-ckeditor/ckeditor.js`,
  fileSingleSizeLimit: 10,
  filebrowserImageUploadUrl: "",
  filebrowserImageDownloadUrl: "",
  language: "zh-cn",
  jqueryPath: "https://cdn.bootcss.com/jquery/3.4.1/jquery.js"
};

export default {
  name: "item-manage",
  components: {
    judgeType,
    singleChoice,
    materialType,
    choice,
    essayType,
    fillType
  },
  inheritAttrs: false,
  props: {
    //题库id
    bank_id: {
      type: String,
      default: ""
    },
    // 模式 create:创建模式 edit:编辑模式
    mode: {
      type: String,
      default: "create"
    },
    //分类
    category: {
      type: Array,
      default: () => []
    },
    //题目信息，在mode=edit 状态下需要传
    subject: {
      type: Object,
      default: () => subject
    },
    type: {
      type: String,
      default: "choice"
    },
    showCKEditorData: {
      type: Object,
      default() {
        return {};
      }
    },
    showModelBtn: {
      type: Boolean,
      default: false
    },
    showAttachment: {
      type: String,
      default: "0"
    },
    cdnHost: {
      type: String,
      default: "service-cdn.qiqiuyun.net"
    },
    uploadSDKInitData: {
      type: Object,
      default() {
        return {};
      }
    },
    deleteAttachmentCallback: {
      type: Function,
      default() {
        return new Promise(resolve => {
          resolve();
        });
      }
    },
    previewAttachmentCallback: {
      type: Function,
      default() {
        return new Promise(resolve => {
          resolve();
        });
      }
    },
    downloadAttachmentCallback: {
      type: Function,
      default() {
        return new Promise(resolve => {
          resolve();
        });
      }
    },
    isDownload: {
      type: Boolean,
      default: false
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
  data() {
    return {
      isAgain: false //是否继续添加
    };
  },
  computed: {
    CKEditorData: function() {
      console.log(this.showCKEditorData);
      if (Object.keys(this.showCKEditorData).length) {
        return Object.assign(baseCKEditorData, this.showCKEditorData);
      }
      return baseCKEditorData;
    }
  },
  provide() {
    return {
      bank_id: this.bank_id,
      category: this.category,
      subject: this.subject,
      showCKEditorData: this.CKEditorData,
      showAttachment: Number(this.showAttachment),
      cdnHost: this.cdnHost,
      uploadSDKInitData: this.uploadSDKInitData,
      deleteAttachmentCallback: this.deleteAttachmentCallback,
      previewAttachmentCallback: this.previewAttachmentCallback,
      downloadAttachmentCallback: this.downloadAttachmentCallback,
      isDownload: this.isDownload,
      modeOrigin: "create"
    };
  },
  created() {
    this.$on("toGoBack", this.toGoBack);
    this.$on("changeAgain", this.changeAgain);
    this.$on("getDeleteFile", this.deleteAttachmentData);
    this.$on("previewFile", this.previewAttachment);
    this.$on("downloadFile", this.downloadAttachment);
  },
  methods: {
    changeAgain(value) {
      this.isAgain = value;
    },
    //获取参数
    patchData(value) {
      const data = {
        data: value,
        isAgain: this.isAgain
      };
      this.$emit("getData", data);
    },
    deleteAttachmentData({ fileId }) {
      this.$emit("deleteAttachment", fileId);
    },
    deleteAttachment(fileId, flag) {
      this.$emit("deleteAttachment", fileId, flag);
    },
    downloadAttachment(fileId) {
      this.$emit("downloadAttachment", fileId);
    },
    //返回
    toGoBack() {
      this.$emit("goBack");
    },
    previewAttachment(fileId) {
      this.$emit("previewAttachment", fileId);
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
  }
};
</script>
