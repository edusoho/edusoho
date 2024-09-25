<template>
  <div v-if="!isMobile()" id="ibs-attachment-preview">
    <div :id="'ids-attachment-preview-' + fileData.id"></div>

    <!-- 被删除 -->
    <div
      v-if="fileData.status === 'delete'"
      class="ibs-attachment-item ibs-clearfix"
    >
      {{ t("attachmentPreview.DeleteTips") }}
    </div>

    <!-- 立即预览 -->
    <div
      v-else-if="isPreviewImediate"
      class="ibs-clearfix ibs-mb8"
      style="padding: 16px;position: relative;background-color: #fff;"
    >
      <div
        v-if="fileData.file_type === 'audio'"
        :id="defaultPlayerInitData.id"
        :style="{
          width: isDownload ? 'calc(100% - 62px)' : '100%',
          height: '400px'
        }"
        class="video-js vjs-default-skin"
        controls
        preload="auto"
      ></div>
      <div
        v-else
        :id="defaultPlayerInitData.id"
        :style="{
          width: isDownload ? 'calc(100% - 62px)' : '100%',
          height: '400px'
        }"
      ></div>

      <a
        v-if="isDownload"
        :href="courseSetStatus == '0' ? '' : downloadUrl"
        class="ibs-ml32 ibs-cusor-pointer ibs-left"
        @click="downloadAttachment"
        style="position: absolute;top: 50%;right: 16px;transform: translateY(-15px);"
      >
        <img width="30" height="30" :src="iconImg.download" />
      </a>
    </div>

    <!-- 转码的交互 -->
    <div v-else class="ibs-attachment-item ibs-clearfix">
      <img
        v-if="finalMode === 'create'"
        v-handle
        class="ibs-mr12 ibs-mt12 ibs-left"
        :src="iconImg.drag"
        style="height: 16px;"
      />
      <img class="ibs-mr16 ibs-left" :src="coverImgSrc" style="height: 40px;" />
      <p
        class="ibs-attachment-item__name ibs-text-overflow ibs-left"
        :style="{ width: `calc(100% - ${buttonNum * 46 + 110}px)` }"
      >
        {{ fileData.file_name }}
      </p>

      <div class="ibs-attachment-item__btn ibs-dark-minor ibs-right clearfix">
        <a-tooltip
          placement="top"
          v-if="isPreview"
          :getPopupContainer="getPopupContainer"
        >
          <template slot="title" v-if="disabled">
            <span v-if="fileData.file_type == 'other'">
              {{ t("attachmentPreview.File_not_supported") }}
            </span>
            <template v-else-if="fileData.convert_status === 'none'">
              {{ t("attachmentPreview.translate_waiting") }}
            </template>
            <template v-else-if="fileData.convert_status === 'waiting'">
              {{ t("attachmentPreview.translate_waiting") }}
            </template>
            <template v-else-if="fileData.convert_status === 'doing'">
              {{ t("attachmentPreview.translate_do") }}
            </template>
            <template v-else-if="fileData.convert_status === 'error'">
              {{ t("attachmentPreview.translate_error") }}
            </template>
          </template>
          <img
            width="30"
            height="30"
            class="ibs-mr16 ibs-cusor-pointer ibs-left"
            @click="showModal"
            :style="{ opacity: disabled ? 0.4 : 1 }"
            :src="iconImg.preview"
          />
        </a-tooltip>
        <a
          v-if="isDownload"
          :href="courseSetStatus == '0' ? '' : downloadUrl"
          class="ibs-mr16 ibs-cusor-pointer ibs-left"
          @click="downloadAttachment"
          style="margin-top: -1px;"
        >
          <img width="30" height="30" :src="iconImg.download" />
        </a>
        <a-popconfirm
          v-if="isDelete"
          :title="t('attachmentPreview.DeleteTips2')"
          :ok-text="t('attachmentPreview.confirm')"
          :cancel-text="t('attachmentPreview.cancel')"
          @confirm="deleteAttachment"
        >
          <a-icon slot="icon" type="question-circle-o" style="color: red" />
          <img
            width="30"
            class="ibs-mr16 ibs-cusor-pointer ibs-left"
            :src="iconImg.delete"
          />
        </a-popconfirm>
      </div>
    </div>

    <a-modal
      width="900px"
      class="ibs-attachment-preview-modal"
      :title="t('attachmentPreview.Preview')"
      :visible="visible"
      :destroyOnClose="true"
      @ok="handleOk"
      :getContainer="getContainer"
      @cancel="handleCancel"
    >
      <div
        v-if="fileData.file_type === 'audio'"
        :id="defaultPlayerInitData.id"
        style="width: 100%;height: 100%;height: 400px"
        class="video-js vjs-default-skin"
        controls
        preload="auto"
      ></div>
      <div v-else :id="defaultPlayerInitData.id" style="height: 400px"></div>

      <template slot="footer">
        <a-button key="submit" type="primary" @click="handleOk">
          {{ t("attachmentPreview.Close") }}
        </a-button>
      </template>
    </a-modal>
  </div>
</template>

<script>
import Emitter from "common/vue/mixins/emitter";
import loadScript from "load-script";
import coverImgData from "./img.json";
import iconImg from "./icon.json";
import Locale from "common/vue/mixins/locale";
import { HandleDirective } from "vue-slicksort";
import { Popconfirm } from "ant-design-vue";

const playerInitQueue = [];
const playerList = [];
let queueLength = 0;
let i = 0;
const startInitAllPlayer = () => {
  if (i > queueLength) return;

  const currentPlayer = playerInitQueue[i]();

  playerList.push(currentPlayer);

  currentPlayer.on("ready", () => {
    i++;
    startInitAllPlayer();
  });

  currentPlayer.on("playing", () => {
    playerList.forEach(player => {
      if (player !== currentPlayer) {
        player.pause();
      }
    });
  });
};

const defaultFileData = {
  id: "10",
  file_name: "音频格式.mp3",
  file_type: "audio",
  ext: "mp3",
  convert_status: "success"
};

export default {
  name: "attachment-preview",
  mixins: [Emitter, Locale],
  directives: { handle: HandleDirective },
  components: {
    APopconfirm: Popconfirm
  },
  inject: [
    "isDownload",
    "modeOrigin",
    "downloadAttachmentCallback",
    "deleteAttachmentCallback",
    "previewAttachmentCallback"
  ],
  data() {
    return {
      visible: false,
      fileTypeObj: {
        ppt: "ppt",
        pptx: "ppt",
        doc: "document",
        docx: "document",
        pdf: "document",
        xls: "document",
        xlsx: "document",
        mp4: "video",
        avi: "video",
        flv: "video",
        f4v: "video",
        mpg: "video",
        wmv: "video",
        mov: "video",
        vob: "video",
        rmvb: "video",
        mkv: "video",
        m4v: "video",
        mp3: "audio"
      },
      loading: false,
      downloadLoading: false,
      downloadUrl: "javascript:;",
      disabledDownload: false,
      defaultPlayerInitData: {
        id: "global-player" + Math.floor(Math.random() * 10000),
        disableDataUpload: 0,
        disableSentry: 0,
        resNo: "e1daaab4f2254162a07c0e9461f89684",
        token: "7h3uCe6qa4t1CxzV:1587367870:ie6JBYIkvGvtHWq1lpte11Abrv0=",
        user: { id: 2, name: "test@edusoho.com" }
      },
      iconImg
    };
  },
  props: {
    mode: {
      type: String,
      default: "create" // create, do
    },
    courseSetStatus: {
      type: String,
      default: "1"
    },
    cdnHost: {
      type: String,
      default: "service-cdn.qiqiuyun.net"
    },
    fileData: {
      type: Object,
      default() {
        return defaultFileData;
      }
    },
    bodyDom: {
      type: String,
      default() {
        return `ids-attachment-preview-${this.fileData.id}`;
      }
    },
    isPreview: {
      type: Boolean,
      default() {
        return true;
      }
    },
    isUpload: {
      type: Boolean,
      default: false
    },
    module: String
  },
  computed: {
    coverImgSrc() {
      if (this.fileData.file_type === "document") {
        return coverImgData[this.fileData.ext || "doc"];
      }

      return coverImgData[this.fileData.file_type];
    },
    playerSDK() {
      return `//${this.cdnHost}/js-sdk-v2/sdk-v1.js?${this.timestamp}`;
    },
    timestamp() {
      return Math.round(Date.parse(new Date()) / 10000);
    },
    isExcel() {
      return this.fileData.ext === "xlsx" || this.fileData.ext === "xls";
    },
    disabled() {
      const { file_type, convert_status } = this.fileData;

      if (file_type === "audio") return false;

      // 不支持预览：1.附件类型为other和excel 2.类型不是音频且未完成转码
      return file_type === "other" || convert_status !== "success";
    },
    isDelete() {
      //   预览删除按钮-学员上传附件删除按钮。
      //   return this.finalMode === "create" || this.module === "answer";
      return ['answer', 'stem', 'analysis'].includes(this.module);
    },
    buttonNum() {
      const btnArray = [this.isDelete, this.isDownload, this.isPreview];

      return btnArray.filter(item => item).length;
    },
    isPreviewImediate() {
      const { file_type, convert_status } = this.fileData;

      if (file_type === "audio" && this.finalMode === "do") return true;

      return (
        file_type === "video" &&
        this.finalMode === "do" &&
        convert_status === "success"
      );
    },
    finalMode() {
      return this.modeOrigin || this.mode || "create";
    }
  },
  mounted() {
    if (this.isMobile()) {
      this.$el.parentNode.remove();

      return;
    }

    if (this.isPreviewImediate) {
      this.emitPreviewAttachment();
      this.initSDK(true);
    }
  },
  methods: {
    showModal() {
      if (this.courseSetStatus == "0") {
        this.$message.error(this.t("courseClosed.preview"));
        return;
      }

      if (this.disabled) return;

      this.visible = true;
      this.emitPreviewAttachment(this.fileData.id);
      this.initSDK();
    },
    emitPreviewAttachment(fileId = this.fileData.id) {
      this.dispatch("item-engine", "previewFile", fileId);
      this.dispatch("item-preview", "previewFile", fileId);
      this.dispatch("item-import", "previewFile", fileId);
      this.dispatch("item-manage", "previewFile", fileId);
    },
    initSDK(isDelay) {
      isDelay && queueLength++;

      this.previewAttachmentCallback(3000).then(value => {
        if (value.result) {
          loadScript(this.playerSDK, () => {
            const initPlayer = () => {
              return new window.QiQiuYun.Player(
                Object.assign(this.defaultPlayerInitData, value.data)
              );
            };

            isDelay ? playerInitQueue.push(initPlayer) : initPlayer();

            if (playerInitQueue.length === queueLength) {
              startInitAllPlayer();
            }
          });
        } else {
          this.$message.error(value.msg);
        }
      });
    },
    handleOk() {
      this.visible = false;
    },
    handleCancel() {
      this.visible = false;
    },
    deleteAttachment() {
      if (this.loading) return;

      this.loading = true;
      this.dispatch("item-manage", "getDeleteFile", {
        fileId: this.fileData.id
      });
      this.dispatch("item-engine", "getDeleteFile", {
        fileId: this.fileData.id
      });
      this.dispatch("item-import", "getDeleteFile", {
        fileId: this.fileData.id
      });
      this.deleteAttachmentCallback(3000).then(value => {
        if (value.result) {
          this.loading = false;
          this.dispatch("item-import", "getDeleteFile", {
            fileId: this.fileData.id,
            flag: true
          });

          if (this.isUpload) {
            this.$emit("deleteFile", this.fileData.id);
          }
        } else {
          this.$message.error(value.msg);
          this.loading = false;
        }
      });
    },
    getContainer() {
      console.log(this.bodyDom);
      return document.getElementById(this.bodyDom);
    },
    downloadAttachment() {
      if (this.courseSetStatus == "0") {
        this.$message.error(this.t("courseClosed.download"));
        return;
      }

      if (this.disabledDownload) return;

      if (this.downloadUrl == "javascript:;") {
        this.downloadLoading = true;
        this.emitDownloadAttachment(this.fileData.id);
        this.downloadAttachmentCallback(3000).then(value => {
          if (value.result) {
            this.downloadUrl = value.url;
            window.location.href = this.downloadUrl;
            this.downloadLoading = false;
          } else {
            this.$message.error(value.msg);
            this.downloadLoading = false;
          }
        });
      }
    },
    emitDownloadAttachment(fileId) {
      this.dispatch("item-engine", "downloadFile", fileId);
      this.dispatch("item-preview", "downloadFile", fileId);
    },
    getPopupContainer() {
      return document.getElementById("ibs-attachment-preview");
    },
    isMobile() {
      try {
        document.createEvent("TouchEvent");
        return true;
      } catch (e) {
        return false;
      }
    }
  }
};
</script>
