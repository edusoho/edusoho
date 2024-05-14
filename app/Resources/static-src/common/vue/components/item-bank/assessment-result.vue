<template>
  <item-report
    answerShow="show"
    assessmentStatus="finished"
    :metaActivity="metaActivity"
    :assessment="assessment"
    :cdnHost="cdnHost"
    :questionFavorites="questionFavorites"
    :answerRecord="answerRecord"
    :answerScene="answerScene"
    :isDownload="isDownload"
    :answerReport="answerReport"
    :isErrorCorrection="isErrorCorrection"
    @previewAttachment="previewAttachment"
    @downloadAttachment="downloadAttachment"
    @error-correction="errorCorrection"
    :previewAttachmentCallback="previewAttachmentCallback"
    :downloadAttachmentCallback="downloadAttachmentCallback"
  ></item-report>
</template>
<script>
import itemReport from './item-report';

export default {
  name: 'assessment-result',
  components: {
    itemReport
  },
  props: {
    metaActivity: {
      type: Object,
      default() {
        return {};
      }
    },
    isDownload: {
      type: Boolean,
      default: false
    },
    assessment: {
      type: Object,
      default() {
        return {};
      }
    },
    answerReport: {
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
    questionFavorites: {
      type: Array,
      default() {
        return [];
      }
    },
    cdnHost: {
      type: String,
      default: 'service-cdn.qiqiuyun.net'
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

    isErrorCorrection: {
      type: String,
      default: '0'
    }
  },
  methods: {
    previewAttachment(fileId) {
      this.$emit('previewAttachment', fileId);
    },
    downloadAttachment(fileId) {
      this.$emit('downloadAttachment', fileId);
    },
    errorCorrection(params) {
      this.$emit('error-correction', params);
    }
  }
};
</script>
