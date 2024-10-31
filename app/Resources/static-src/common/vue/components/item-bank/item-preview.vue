<template>
  <div class="ibs-pb24 ibs-white-bg ibs-preview-wrap">
    <material-title
      v-show="item.type === 'material'"
      :material="item.material"
      :attachments="item.attachments"
    ></material-title>
    <!-- ------------题目区域------------ -->
    <div class="ibs-preview-list">
      <div
        class="ibs-pl16 ibs-pr16 ibs-preview-item"
        v-for="(question, questionIndex) in item.questions"
        :key="questionIndex"
      >
        <judge-type
          v-if="question.answer_mode === 'true_false'"
          :needScore="needScore"
          :question="question"
          :item="item"
          :mode="mode"
        >
        </judge-type>
        <single-choice
          v-if="question.answer_mode === 'single_choice'"
          :needScore="needScore"
          :question="question"
          :item="item"
          :mode="mode"
        ></single-choice>
        <choice
          v-if="
            question.answer_mode === 'choice' ||
              question.answer_mode === 'uncertain_choice'
          "
          :needScore="needScore"
          :question="question"
          :item="item"
          :mode="mode"
        ></choice>
        <essay
          v-if="question.answer_mode === 'rich_text'"
          :needScore="needScore"
          :question="question"
          :item="item"
          :mode="mode"
        ></essay>
        <fill
          v-if="question.answer_mode === 'text'"
          :needScore="needScore"
          :question="question"
          :item="item"
          :mode="mode"
          previewType="item"
        ></fill>
      </div>
    </div>
    <!-- ------------题目区域------------ -->

    <!-- 材料题解析，后续考虑和材料题题干放在一起 -->
    <material-analysis
      v-show="item.type === 'material' && item.analysis"
      :needScore="needScore"
      :analysis="item.analysis"
      :attachments="item.attachments"
    ></material-analysis>
  </div>
</template>

<script>
import judgeType from "./item-engine-components/judge";
import singleChoice from "./item-engine-components/single-choice";
import choice from "./item-engine-components/choice";
import essay from "./item-engine-components/essay";
import fill from "./item-engine-components/fill";
import materialTitle from "./item-engine-components/material-title";
import materialAnalysis from "./item-engine-components/material-analysis";

const baseCKEditorData = {
  publicPath: `${process.env.BASE_URL}/es-ckeditor/ckeditor.js`,
  fileSingleSizeLimit: 10,
  filebrowserImageUploadUrl: "",
  filebrowserImageDownloadUrl: "",
  language: "zh-cn",
  jqueryPath: "https://cdn.bootcss.com/jquery/3.4.1/jquery.js"
};

export default {
  name: "item-preview",
  data() {
    return {
      mode: "preview"
    };
  },
  components: {
    judgeType,
    singleChoice,
    choice,
    essay,
    fill,
    materialTitle,
    materialAnalysis
  },
  computed: {
    CKEditorData: function() {
      if (Object.keys(this.showCKEditorData).length) {
        return Object.assign(baseCKEditorData, this.showCKEditorData);
      }
      return baseCKEditorData;
    }
  },
  provide() {
    return {
      showCKEditorData: this.CKEditorData,
      cdnHost: this.cdnHost,
      showAttachment: this.showAttachment,
      deleteAttachmentCallback: this.deleteAttachmentCallback,
      previewAttachmentCallback: this.previewAttachmentCallback,
      downloadAttachmentCallback: this.downloadAttachmentCallback,
      isDownload: this.isDownload
    };
  },
  props: {
    needScore: {
      type: Number,
      default() {
        return 1;
      }
    },
    item: {
      type: Object,
      default() {
        return {};
      }
    },
    showCKEditorData: {
      type: Object,
      default() {
        return {};
      }
    },
    cdnHost: {
      type: String,
      default: "service-cdn.qiqiuyun.net"
    },
    showAttachment: {
      type: String,
      default: "0"
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
    }
  },
  created() {
    this.$on("previewFile", this.previewAttachment);
    this.$on("downloadFile", this.downloadAttachment);
  },
  methods: {
    previewAttachment(fileId) {
      this.$emit("previewAttachment", fileId);
    },
    downloadAttachment(fileId) {
      this.$emit("downloadAttachment", fileId);
    }
  }
};
</script>
