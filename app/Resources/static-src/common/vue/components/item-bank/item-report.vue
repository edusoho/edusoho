<template>
  <div>
    <item-engine
      mode="report"
      :metaActivity="metaActivity"
      :answerShow="answerShow"
      :answerReport="answerReport"
      :assessment="assessment"
      :answerRecord="answerRecord"
      :answerScene="answerScene"
      :cdnHost="cdnHost"
      :isDownload="isDownload"
      :assessmentStatus="assessmentStatus"
      :questionFavorites="questionFavorites"
      :Collect="Collect"
      :exercise="exercise"
      :showCKEditorData="showCKEditorData"
      :isErrorCorrection="isErrorCorrection"
      :assessmentResponses="assessmentResponses"
      @collectUpdateEvent="collectUpdateEvent"
      @cancelCollectEvent="cancelCollectEvent"
      @previewAttachment="previewAttachment"
      @downloadAttachment="downloadAttachment"
      @error-correction="errorCorrection"
      @getAiAnalysis="getAiAnalysis"
      @stopAiAnalysis="stopAiAnalysis"
      :previewAttachmentCallback="previewAttachmentCallback"
      :downloadAttachmentCallback="downloadAttachmentCallback"
      :showDoAgainBtn="showDoAgainBtn"
      :courseSetStatus="courseSetStatus"
      :isShowAiAnalysis="isShowAiAnalysis"
    >
      <template #returnBtn>
        <slot name="returnBtn"></slot>
      </template>
    </item-engine>
    <a-modal
      v-if="assessmentStatus !== 'finished'"
      :title="t('itemReport.DoAgain_tip')"
      :visible="visible"
      :closable="closable"
      :getContainer="getContainer"
    >
      <p>
        <span class="ibs-warning-color"
          ><i class="ib-icon ib-icon-info ibs-mr8"></i
          >{{ t("itemReport.DoAagin_time") }}</span
        ><span class="ibs-text-medium ibs-ml8">{{ time }}</span>
      </p>
      <template slot="footer">
        <a-button key="submit" type="primary" @click="handleOk">
          {{ t("itemReport.DoAagin_know") }}
        </a-button>
      </template>
    </a-modal>
  </div>
</template>

<script>
import { getCountDown } from "common/date-toolkit";
import itemEngine from "./item-engine";
import Emitter from "common/vue/mixins/emitter";
import Locale from "common/vue/mixins/locale";

export default {
  name: 'item-report',
  mixins: [Emitter, Locale],
  data() {
    return {
      time: null,
      timeMeter: null,
      flag: false,
      visible: false,
      closable: false,
      redoInterval: Number(this.answerScene.redo_interval),
      doTimes: Number(this.answerScene.do_times),
      finishedTime: Number(this.answerReport.review_time) * 1000
    };
  },
  components: {
    itemEngine
  },
  props: {
    exercise: {
      type: Object,
      default() {
        return {};
      }
    },
    metaActivity: {
      type: Object,
      default() {
        return {};
      }
    },
    courseSetStatus: {
      type: String,
      default: "1"
    },
    answerReport: {
      type: Object,
      default() {
        return {};
      }
    },
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
    answerShow: {
      type: String,
      default() {
        return "show";
      }
    },
    assessmentStatus: {
      type: String,
      default() {
        return "";
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
    },
    //收藏功能 默认在答题报告模式显示
    Collect: {
      type: Boolean,
      default: true
    },
    showCKEditorData: {
      type: Object,
      default() {
        return {};
      }
    },
    showDoAgainBtn: {
      type: Number,
      default: 1
    },

    isErrorCorrection: {
      type: String,
      default: "0"
    },
    isDownload: {
      type: Boolean,
      default: false
    },
    assessmentResponses: {
      type: Object,
      default() {
        return {};
      }
    },
    isShowAiAnalysis: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    timeCount() {
      return (
        this.finishedTime +
        this.redoInterval * 60 * 1000 -
        Date.parse(new Date())
      );
    }
  },
  created() {
    this.$on("doAgain", this.doAgain);
  },
  mounted() {
    if (
      this.answerRecord.status === "finished" &&
      this.redoInterval &&
      !this.doTimes &&
      this.timeCount > 0
    ) {
      this.timer();
    } else {
      this.flag = true;
    }
  },
  methods: {
    doAgain() {
      if (this.flag) {
        this.$emit("doAgainEvent");
      } else {
        this.visible = true;
      }
    },
    timer() {
      let i = 0;
      let time = this.timeCount;
      this.timeMeter = setInterval(() => {
        let { hours, minutes, seconds } = getCountDown(time, i++);
        this.time = `${hours}:${minutes}:${seconds}`;
        console.log(this.time);
        if (
          (Number(hours) == 0 &&
            Number(minutes) == 0 &&
            Number(seconds) == 0) ||
          Number(seconds) < 0
        ) {
          this.flag = true;
          this.clearTime();
          this.visible = false;
        }
      }, 1000);
    },
    clearTime() {
      clearInterval(this.timeMeter);
      this.timeMeter = null;
    },
    handleOk() {
      this.visible = false;
    },
    getContainer() {
      return document.getElementById("item-bank-sdk-message");
    },
    collectUpdateEvent(data) {
      this.$emit("favoriteEvent", data);
      this.dispatch("assessment-result", "favoriteEvent", data);
    },
    cancelCollectEvent(data) {
      this.$emit("cancelFavoriteEvent", data);
      this.dispatch("assessment-result", "cancelFavoriteEvent", data);
    },
    previewAttachment(fileId) {
      this.$emit("previewAttachment", fileId);
    },
    downloadAttachment(fileId) {
      this.$emit("downloadAttachment", fileId);
    },
    errorCorrection(params) {
      this.$emit("error-correction", params);
    },
    getAiAnalysis(questionId, finished) {
      this.$emit("getAiAnalysis", questionId, finished);
    },
    stopAiAnalysis(questionId) {
      this.$emit("stopAiAnalysis", questionId);
    },
  }
};
</script>
