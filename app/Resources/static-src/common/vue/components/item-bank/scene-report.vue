<template>
  <div class="ibs-scene-report">
    <item-engine
      mode="analysis"
      :assessment="assessment"
      :answerRecord="answerRecord"
      :answerScene="answerScene"
      :answerSceneReport="answerSceneReport"
      :isDownload="isDownload"
      @previewAttachment="previewAttachment"
      :previewAttachmentCallback="previewAttachmentCallback"
      :downloadAttachmentCallback="downloadAttachmentCallback"
      @downloadAttachment="downloadAttachment"
    >
      <template v-slot:pendant><div></div></template>
    </item-engine>
  </div>
</template>

<script>
import itemEngine from './item-engine';

export default {
  name: "scene-report",
  components: {
    itemEngine
  },
  props: {
    assessment: {
      type: Object,
      default() {
        return {};
      }
    },
    answerRecord: {
      type: Object,
      default() {
        return {};
      }
    },
    answerScene: {
      type: Object,
      default() {
        return {};
      }
    },
    answerSceneReport: {
      type: Object,
      default() {
        return {};
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
