<template>
  <item-engine
    mode="do"
    assessmentStatus="preview"
    :assessment="assessment"
    :answerRecord="answerRecord"
    :answerScene="answerScene"
    :cdnHost="cdnHost"
    @previewAttachment="previewAttachment"
    :previewAttachmentCallback="previewAttachmentCallback"
    :downloadAttachmentCallback="downloadAttachmentCallback"
    :showCKEditorData="showCKEditorData"
  ></item-engine>
</template>
<script>
import itemEngine from "./item-engine";

export default {
  name: "assessment-preview",
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
    }
  },
  methods: {
    previewAttachment(fileId) {
      this.$emit("previewAttachment", fileId);
    }
  }
};
</script>
