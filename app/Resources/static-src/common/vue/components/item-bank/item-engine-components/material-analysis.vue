<template>
  <div>
    <a-row>
      <a-col class="ibs-pr16 ibs-pl16">
        <div
          class="ibs-engine-material ibs-engine-material--analysis ibs-no-bottom-border"
        >
          <span class="ibs-label">{{ t("itemEngine.itemAnalysis") }}</span>
          <div class="ibs-content ibs-editor-text" v-html="analysis"></div>
          <div
            v-if="getAttachmentTypeData('analysis').length > 0"
            class="ibs-mt16"
            style="padding: 8px 8px 0;background-color: #f5f5f5;border: 1px solid #F2F3F5;border-radius: 6px;"
          >
            <attachment-preview
              v-for="fileData in getAttachmentTypeData('analysis')"
              :key="fileData.id"
              :cdnHost="cdnHost"
              :courseSetStatus="courseSetStatus"
              :fileData="fileData"
            ></attachment-preview>
          </div>
        </div>
      </a-col>
    </a-row>
  </div>
</template>

<script>
import Locale from "common/vue/mixins/locale";
import attachmentPreview from "../attachment-preview";
export default {
  name: "material-analysis",
  inject: ["cdnHost"],
  components: {
    attachmentPreview
  },
  mixins: [Locale],
  props: {
    needScore: {
      type: Number,
      default() {
        return 1;
      }
    },
    analysis: {
      type: String,
      default: ""
    },
    courseSetStatus: {
      type: String,
      default: "1"
    },
    attachments: {
      type: Array,
      default() {
        return [];
      }
    }
  },
  methods: {
    getAttachmentTypeData(type) {
      const result = this.attachments.filter(item => {
        return item.module == type;
      });
      return result;
    }
  }
};
</script>
