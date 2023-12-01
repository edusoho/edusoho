<template>
  <div id="app" class="ibs-vue">
    <item-preview
      :item="info"
      :showAttachment="showAttachment"
      :cdnHost="cdnHost"
      :isDownload="isDownload"
      :needScore="0"
      :previewAttachmentCallback="previewAttachmentCallback"
      :downloadAttachmentCallback="downloadAttachmentCallback"
      @previewAttachment="previewAttachment"
      @downloadAttachment="downloadAttachment"
    ></item-preview>
  </div>
</template>

<script>
export default {
  props: {
    info: {
      type: Object,
      default: () => {},
    },
  },
  data() {
    return {
      item: {
        id: "15",
        bank_id: "1",
        type: "single_choice",
        material: "",
        analysis: "",
        category_id: "0",
        difficulty: "normal",
        question_num: "1",
        created_user_id: "2",
        updated_user_id: "2",
        updated_time: "1700014654",
        created_time: "1700014654",
        is_deleted: "0",
        deleted_time: "0",
        questions: [
          {
            id: "25",
            item_id: "15",
            stem: "\u5355\u90091",
            seq: 1,
            score: "2.0",
            answer_mode: "single_choice",
            case_sensitive: "1",
            response_points: [
              {
                radio: {
                  text: "1",
                  val: "A",
                },
              },
              {
                radio: {
                  text: "2",
                  val: "B",
                },
              },
              {
                radio: {
                  text: "3",
                  val: "C",
                },
              },
              {
                radio: {
                  text: "4",
                  val: "D",
                },
              },
            ],
            answer: ["A"],
            analysis: "\u89e3\u6790\u89e3\u6790",
            created_user_id: "2",
            updated_user_id: "2",
            updated_time: "1700014654",
            created_time: "1700014654",
            score_rule: {
              score: 2,
              scoreType: "question",
              otherScore: 2,
            },
            is_deleted: "0",
            deleted_time: "0",
            attachments: [],
          },
        ],
        attachments: [],
      },
      showAttachment: $("[name=show_attachment]").val(),
      cdnHost: $("[name=cdn_host]").val(),
      fileId: 0,
      isDownload:
        JSON.parse($("[name=question_bank_attachment_setting]").val())
          .enable === "1",
    };
  },
  provide() {
    return {
      modeOrigin: "do",
    };
  },
  mounted() {
  },
  methods: {
    previewAttachment(fileId) {
      this.fileId = fileId;
    },
    downloadAttachment(fileId) {
      console.log(fileId);
      this.fileId = fileId;
    },
    previewAttachmentCallback() {
      let self = this;
      return new Promise((resolve) => {
        $.ajax({
          url: $("[name=preview-attachment-url]").val(),
          type: "post",
          data: { id: this.fileId },
          beforeSend(request) {
            request.setRequestHeader(
              "X-CSRF-Token",
              $("meta[name=csrf-token]").attr("content")
            );
          },
        }).done(function (resp) {
          resp.data["sdkBaseUri"] = app.cloudSdkBaseUri;
          resp.data["disableDataUpload"] = app.cloudDisableLogReport;
          resp.data["disableSentry"] = app.cloudDisableLogReport;
          resolve(resp);
          self.fileId = 0;
        });
      });
    },
    downloadAttachmentCallback() {
      let self = this;
      return new Promise((resolve) => {
        $.ajax({
          url: $("[name=download-attachment-url]").val(),
          type: "post",
          data: { id: this.fileId },
          beforeSend(request) {
            request.setRequestHeader(
              "X-CSRF-Token",
              $("meta[name=csrf-token]").attr("content")
            );
          },
        }).done(function (resp) {
          resolve(resp);
          self.fileId = 0;
        });
      });
    },
  },
};
</script>
