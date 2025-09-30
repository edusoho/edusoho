<template>
  <div id="ibs-attachment-modal">
    <a-modal
      width="900px"
      :title="t('attachmentUpload.Upload_file')"
      :visible="visible"
      :destroyOnClose="true"
      @ok="handleOk"
      :getContainer="getContainer"
      @cancel="handleCancel"
    >
      <div class="ibs-uploader-content">
        <div class="ibs-uploader-container" id="uploader-container"></div>
        <div class="ibs-uploader-bottom">
          <ul>
            <li>
              {{ t("attachmentUpload.Supported_formats") }}
              <strong>{{
                uploaderInitData.fileSingleSizeLimit | getSingleFileSize
              }}</strong
              >。
            </li>
            <li>
              {{ t("attachmentUpload.Support_breakpoint_renewal") }}
            </li>
          </ul>
        </div>
      </div>
      <template slot="footer">
        <a-button key="submit" type="primary" @click="handleOk">
          {{ t("attachmentUpload.Close") }}
        </a-button>
      </template>
    </a-modal>

    <SlickList
      :useDragHandle="true"
      lockAxis="y"
      helperClass="ibs-vue"
      v-if="fileData.length > 0"
      v-model="fileData"
      @input="onFileSort"
      style="padding: 8px 8px 0;background-color: #f5f5f5;border: 1px solid #F2F3F5;border-radius: 6px;"
    >
      <SlickItem
        v-for="(item, index) in fileData"
        :index="index"
        :key="item.no"
        style="z-index: 1000;"
      >
        <attachment-preview
          :isPreview="true"
          :bodyDom="bodyDom"
          :module="module"
          :fileData="item"
          :is-upload="true"
          @deleteFile="deleteFile"
        ></attachment-preview>
      </SlickItem>
    </SlickList>

    <a-button
      type="primary"
      class="ibs-mt8"
      v-show="fileData.filter(item => item.status !== 'delete').length < 3"
      @click="showModal"
    >
      {{ t("attachmentUpload.Upload_attachment") }}
    </a-button>
  </div>
</template>

<script>
import { SlickList, SlickItem } from "vue-slicksort";
import attachmentPreview from "./attachment-preview";
import loadScript from "load-script";
import Locale from "common/vue/mixins/locale";

const uploaderDefaultInitData = {
  sdkBaseUri: "",
  disableDataUpload: 0,
  disableSentry: 0,
  initUrl:
    "//es-dev.com/uploader/v2/init?token=MnxhdHRhY2htZW50fDJ8cHJpdmF0ZXwxNTg3ODg4Mjc1fDU2YmUzMmJhZjI5OWVjYzA4NDdmNGU5NWMzYWMxZjNj",
  finishUrl:
    "//es-dev.com/uploader/v2/finished?token=MnxhdHRhY2htZW50fDJ8cHJpdmF0ZXwxNTg3ODg4Mjc1fDU2YmUzMmJhZjI5OWVjYzA4NDdmNGU5NWMzYWMxZjNj",
  // 单位是B
  fileSingleSizeLimit: 2048 * 1024 * 1024,
  locale: "zh_CN",
  // 可内置
  accept: {
    extensions: [
      "mp4",
      "avi",
      "flv",
      "f4v",
      "mpg",
      "wmv",
      "mov",
      "vob",
      "rmvb",
      "mkv",
      "m4v",
      "mp3",
      "ppt",
      "pptx",
      "doc",
      "docx",
      "pdf",
      "xls",
      "xlsx",
      "wps",
      "odt",
      "zip",
      "rar",
      "gz",
      "tar",
      "7z"
    ],
    mimeTypes: [
      "video/mp4",
      "video/mpeg",
      "video/x-msvideo",
      "video/quicktime",
      "video/3gpp",
      "video/x-m4v",
      "video/x-flv",
      "video/x-ms-wmv",
      "audio/mp4",
      "audio/mpeg",
      "audio/basic",
      "audio/ac3",
      "audio/ogg",
      "audio/3gpp",
      "application/vnd.ms-powerpoint",
      "application/vnd.openxmlformats-officedocument.presentationml.presentation",
      "application/vnd.ms-excel",
      "application/vnd.ms-outlook",
      "application/vnd.ms-pkicertstore",
      "application/vnd.ms-pkiseccat",
      "application/vnd.ms-pkistl",
      "application/vnd.ms-powerpoint",
      "application/vnd.ms-project",
      "application/vnd.ms-works",
      "application/msword",
      "application/pdf",
      "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
      "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
      "application/zip",
      "application/x-zip-compressed",
      "application/x-rar-compressed",
      "application/x-tar",
      "application/x-gzip",
      "application/x-7zip"
    ]
  },
  // 内置固定属性
  id: "uploader-container",
  process: { document: { type: "html" } },
  ui: "single"
};
const attachmentFileDefault = {
  type: "other",
  filename: "鸟类知识.pptx",
  id: "500"
};
export default {
  name: "attachment-upload",
  mixins: [Locale],
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
      fileData: this.fileShowData
    };
  },
  components: {
    attachmentPreview,
    SlickItem,
    SlickList
  },
  props: {
    cdnHost: {
      type: String,
      default: "service-cdn.qiqiuyun.net"
    },
    uploaderData: {
      type: Object,
      default() {
        return {};
      }
    },
    bodyDom: {
      type: String,
      default() {
        return "ibs-attachment-modal";
      }
    },
    fileShowData: {
      type: Array,
      default() {
        return [];
      }
    },
    attachmentFile: {
      type: Object,
      default() {
        return attachmentFileDefault;
      }
    },
    mode: {
      type: String,
      default: "create"
    },
    module: {
      type: String,
      default: ""
    }
  },
  computed: {
    uploaderSDK() {
      return `//${this.cdnHost}/js-sdk-v2/uploader/sdk-2.1.0.js?${this.timestamp}`;
    },
    uploaderInitData() {
      if (Object.keys(this.uploaderData).length) {
        return Object.assign(uploaderDefaultInitData, this.uploaderData);
      }
      return uploaderDefaultInitData;
    },
    timestamp() {
      return Math.round(Date.parse(new Date()) / 10000);
    }
  },
  filters: {
    getSingleFileSize(fileSize) {
      return fileSize >= 1024 * 1024 * 1024
        ? `${fileSize / (1024 * 1024 * 1024)}GB`
        : `${fileSize / (1024 * 1024)}MB`;
    }
  },
  methods: {
    showModal() {
      this.visible = true;
      loadScript(this.uploaderSDK, err => {
        console.log(err);
        this.initUpladerSDk();
      });
    },
    handleOk() {
      this.visible = false;
    },
    handleCancel(e) {
      console.log(e);
      this.visible = false;
    },
    initUpladerSDk() {
      this.uploaderInitData.fileNumLimit = 3 - this.fileData.length;
      let uploader = new window.UploaderSDK(this.uploaderInitData);
      uploader.on("error", type => {
        // 例如：上传文件过大时提醒
        this.$message.error(type.message);
      });
      uploader.on("file.finish", file => {
        // 抛出上传完的事件
        const uploadFile = {
          id: file.no,
          file_name: file.name,
          file_type: this.getFileType(file),
          module: this.module,
          ext: file.ext,
          global_id: file.globalId,
          hash_id: file.initResponse.globalId,
          convert_status: "waiting"
        };
        this.fileData.push(uploadFile);
        this.$emit("getFileInfo", uploadFile);
      });
    },
    getFileType(file) {
      const index = file.name.lastIndexOf(".");
      const extension = file.name.substr(index + 1).toLowerCase();

      return this.fileTypeObj[extension]
        ? this.fileTypeObj[extension]
        : "other";
    },
    getContainer() {
      return document.getElementById(this.bodyDom);
    },
    onFileSort() {
      this.$emit("onFileSort", this.fileData);
    },
    deleteFile(fileId) {
      this.fileData = this.fileData.filter(item => item.id !== fileId);
      this.$emit("deleteFile", fileId);
    }
  }
};
</script>
