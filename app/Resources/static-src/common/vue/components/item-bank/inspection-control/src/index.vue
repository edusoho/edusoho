<template>
  <div class="ibs-inspection">
    <div class="ibs-card ibs-card--inspection" v-show="state === 'watching'">
      <div class="ibs-card-head ibs-clearfix ibs-text-left">
        {{ t("inspectionControl.Invigilation_tips") }}
      </div>
      <div class="ibs-card-body ibs-text-center">
        <div class="ibs-inspection-card">
          <a-alert
            :message="behaviorMap[watchBehavior]"
            :type="watchingState"
          />
          <div class="ibs-inspection-card-title">
            {{ t("inspectionControl.Independent_completion") }}
          </div>
          <div class="ibs-inspection-card-area">
            <div
              class="ibs-inspection-card-capture"
              id="inspection-watching-video"
            ></div>
          </div>
        </div>
      </div>
    </div>
    <a-modal
      :title="t('inspectionControl.Tips')"
      :visible="visible"
      :closable="closable"
      :maskClosable="false"
      :footer="null"
      class="ibs-inspection-modal"
      :class="
        `ibs-inspection-${state} ${
          isFaceCaptured ? 'captured' : 'not-captured'
        }`
      "
      :confirmLoading="confirmLoading"
      :destroyOnClose="true"
      :okText="confirmText"
      :getContainer="getContainer"
      width="828px"
      @cancel="handleCancel"
    >
      <div slot="footer">
        <a-button @click="reloadPage" v-show="isFaceCaptured">
          {{ t("inspectionControl.Recapture") }}
        </a-button>
        <a-button
          type="primary"
          @click="handleConfirm"
          :loading="confirmLoading"
          :disabled="!isFaceCaptured && startCapture"
          >{{ confirmText }}</a-button
        >
      </div>
      <div
        v-show="state === 'loading'"
        class="ibs-loading-content ibs-text-center"
      >
        <div>
          <a-icon type="loading" style="font-size: 64px" />
        </div>
        <div class="ibs-loading-text">
          <p>{{ t("inspectionControl.Intelligent_invigilator") }}</p>
        </div>
      </div>
      <div
        v-show="state === 'error'"
        class="ibs-loading-content ibs-text-center"
      >
        <div>
          <a-icon type="info-circle" style="font-size: 64px; color: #E6E6E6" />
        </div>
        <div class="ibs-loading-text">
          <p v-html="errorMessage"></p>
        </div>
        <div class="ibs-loading-btn">
          <a-button type="primary" @click="reloadPage">{{
            t("inspectionControl.Retry")
          }}</a-button>
        </div>
      </div>
      <div
        :class="{ actived: state === 'ready' }"
        class="ibs-capture-content ibs-capture-ready"
      >
        <div class="ibs-capture-title">
          {{ t("inspectionControl.Collect__head") }}
        </div>
        <a-row>
          <div id="ibs-capture">
            <div id="inspection-collect-video"></div>
          </div>
        </a-row>
      </div>
    </a-modal>
  </div>
</template>

<script>
import loadScript from "load-script";
import Locale from "common/vue/mixins/locale";
import imgData from "./img.json";

export default {
  name: "inspection-control",
  mixins: [Locale],
  inheritAttrs: false,
  components: {},
  props: {
    mode: {
      type: String,
      default: "watching"
    }
  },
  data() {
    return {
      faceUrl: "",
      stateNum: 0,
      startCapture: false,
      visible: false,
      confirmLoading: false,
      collectLoading: false,
      collectEnable: false,
      finishLoading: false,
      inspectionController: null,
      SDK: null,
      descriptor: null,
      captureImgData: null,
      stateList: ["loading", "ready", "watching", "error"],
      watchBehavior: "normal",
      closable: false,
      collectIndex: 1,
      bestFace: null,
      behaviorMap: {
        // eslint-disable-next-line no-undef
        no_face: `${this.t("inspectionControl.behaviormap_1")}`,
        // eslint-disable-next-line no-undef
        not_self: `${this.t("inspectionControl.behaviormap_2")}`,
        // eslint-disable-next-line no-undef
        many_face: `${this.t("inspectionControl.behaviormap_3")}`,
        // eslint-disable-next-line no-undef
        page_hide: `${this.t("inspectionControl.behaviormap_4")}`,
        // eslint-disable-next-line no-undef
        normal: `${this.t("inspectionControl.behaviormap_5")}`
      },
      imgArr: [],
      collectImgShow: false,
      collectBtnShow: true,
      alert: {
        isShow: false,
        type: "",
        text: ""
      },
      errorMessage: ""
    };
  },
  computed: {
    watchingState() {
      return this.watchBehavior === "normal" ? "info" : "warning";
    },
    state() {
      return this.stateList[this.stateNum];
    },
    confirmText() {
      return this.captureImgData
        ? // eslint-disable-next-line no-undef
          this.t("inspectionControl.captureImgData_1")
        : // eslint-disable-next-line no-undef
          this.t("inspectionControl.captureImgData_2");
    },
    cancelText() {
      // eslint-disable-next-line no-undef
      return this.captureImgData
        ? this.t("inspectionControl.captureImgData_3")
        : "";
    },
    isFaceCaptured() {
      return !!this.captureImgData;
    },
    avatarOutlineImgSrc() {
      return imgData.avatarOutline;
    }
  },
  watch: {},
  created() {
    let timestamp = parseInt(Date.parse(new Date()) / 3600000);
    loadScript(
      "//edtech.edusoho.net/exam-supervisor/exam-supervisor-sdk.js?v=" +
        timestamp,
      err => {
        console.log(err);
      }
    );
  },
  mounted() {
    this.$emit("ready");
  },
  methods: {
    reloadPage() {
      window.location.reload();
    },
    captureModal(opts) {
      this.visible = true;
      setTimeout(() => {
        this.captureFace(opts);
      }, 300);
    },
    closeModal() {
      this.visible = false;
      this.confirmLoading = false;
    },
    startWatching() {
      setTimeout(() => {
        if (this.faceUrl) {
          this.inspectionController.start("inspection-watching-video", {
            url: this.faceUrl
          });
        } else {
          console.log(this.bestFace);
          this.inspectionController.start("inspection-watching-video", {
            image: this.bestFace
          });
        }
      }, 300);
    },
    stopWatching() {
      this.inspectionController.stop();
    },
    captureFace(opts) {
      if (opts.errorMessage) {
        this.stateNum = 3;
        this.visible = true;
        this.errorMessage = opts.errorMessage;
        return;
      }

      this.faceUrl = opts.faceUrl;
      if (!window.ExamSupervisorSDK) {
        setTimeout(() => {
          this.captureFace(opts);
        }, 300);
        return;
      }

      this.inspectionController = new window.ExamSupervisorSDK({
        apiServer: "//exam-supervisor-service.edusoho.net",
        token: opts.token
      });

      this.initControllerEvents();

      if (!opts.faceUrl) {
        this.stateNum = 1;
        this.bootCollectFace();
      } else {
        setTimeout(() => {
          if (this.mode === "watching") {
            this.startInspection();
            return;
          }
          this.closeModal();
        }, 500);
      }
    },
    startInspection() {
      this.startWatching();
    },
    initControllerEvents() {
      this.inspectionController.on("cheating", cheating => {
        let message = this.t("inspectionControl.watchResult");
        message += "behavior: " + cheating.behavior + ", ";
        message += ")";
        console.log(message);
        this.watchBehavior = cheating.behavior;
        this.$emit("cheatHappened", cheating);
      });

      this.inspectionController.on("start-error", error => {
        console.log("error");
        this.stateNum = 3;
        this.visible = true;
        this.errorMessage = error.message;
        console.log(error);
      });

      this.inspectionController.on("started", () => {
        this.stateNum = 2;
        this.closeModal();
        this.$emit("inspectionStarted");
      });

      this.inspectionController.on("stopped", () => {
        this.$emit("inspectionStopped");
      });
    },
    bootCollectFace() {
      this.collectEnable = false;
      this.collectImgShow = false;

      this.inspectionController.bootCollectFace(
        "inspection-collect-video",
        result => this.faceCaptured(result)
      );
    },
    async faceCaptured(result) {
      console.log(result);
      return new Promise(() => {
        console.log("uploading......");
        this.$emit("faceCaptured", {
          capture: result.face
        });
        this.bestFace = result.face;
        this.confirmLoading = true;
        this.finishLoading = true;
        if (this.mode === "watching") {
          this.startInspection();
          return;
        }
      });
    },
    handleConfirm() {
      this.confirmLoading = true;
      this.finishLoading = true;
      setTimeout(() => {
        if (this.mode === "watching") {
          this.startInspection();
          return;
        }
      }, 500);
    },
    handleCancel() {
      console.log("Clicked cancel button");
      this.visible = false;
    },
    getContainer() {
      return document.getElementById("item-bank-sdk-message");
    }
  }
};
</script>
