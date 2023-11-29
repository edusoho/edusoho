<template>
  <div id="update-duplicate-check" class="ibs-vue">
    <div class="duplicate-head">
      <span class="duplicate-back" @click="goLast">
        <a-icon type="left" />
        返回
      </span>
      <span class="duplicate-divider"></span>
      <span class="duplicate-title">题目编辑</span>
    </div>
    <div class="duplicate-body flex">
      <item-manage
        style="margin: 20px auto; width: 100%;"
        :bank_id="bank_id"
        mode="edit"
        :category="category"
        :subject="subject"
        :type="type"
        :showCKEditorData="showCKEditorData"
        :showAttachment="showAttachment"
        :cdnHost="cdnHost"
        :isDownload="isDownload"
        :isDisable="isDisable"
        :uploadSDKInitData="uploadSDKInitData"
        :deleteAttachmentCallback="deleteAttachmentCallback"
        :previewAttachmentCallback="previewAttachmentCallback"
        :downloadAttachmentCallback="downloadAttachmentCallback"
        @getData="getData"
        @goBack="goBack"
        @deleteAttachment="deleteAttachment"
        @previewAttachment="previewAttachment"
        @downloadAttachment="downloadAttachment"
      ></item-manage>
    </div>
  </div>
</template>
<script>
export default {
  data() {
    let item = {};
    item = JSON.parse($('[name=item]').val());
    item.questions = Object.values(item.questions);
    return {
      fileId: 0,
      subject: item,
      bank_id: $('[name=bank_id]').val(),
      category: JSON.parse($('[name=category]').val()),
      showCKEditorData: {
        publicPath: $('[name=ckeditor_path]').val(),
        filebrowserImageUploadUrl: $('[name=ckeditor_image_upload_url]').val(),
        filebrowserImageDownloadUrl: $('[name=ckeditor_image_download_url]').val(),
        language: document.documentElement.lang === 'zh_CN' ? 'zh-cn' : document.documentElement.lang,
        jqueryPath: $('[name=jquery_path]').val()
      },
      type: $('[name=type]').val(),
      showAttachment: $('[name=show_attachment]').val(),
      cdnHost: $('[name=cdn_host]').val(),
      uploadSDKInitData: {
          ui: 'batch',
          multiple: true,
          multitaskNum: 3,
          fileNumLimit: 3,
          sdkBaseUri: app.cloudSdkBaseUri,
          disableDataUpload: app.cloudDisableLogReport,
          disableSentry: app.cloudDisableLogReport,
          initUrl: $('[name=upload_init_url]').val(),
          finishUrl: $('[name=upload_finish_url]').val(),
          accept: JSON.parse($('[name=upload_accept]').val()),
          fileSingleSizeLimit: $('[name=upload_size_limit]').val(),
          locale: document.documentElement.lang
        },
        isDownload: false,
        isDisable: null,
    };
  },
  methods: {
    goLast() {
      this.$router.go(-1);
    },
    getData(data) {
      const that = this;
      that.isDisable = true;
      this.getRepeatStem(data)
        .then((res) => {
          if (res) {
            this.$confirm({
              title: Translator.trans("created.question.confirm.title"),
              okText: Translator.trans("created.question.confirm.ok.btn"),
              cancelText: Translator.trans(
                "created.question.confirm.close.btn"
              ),
              icon: "exclamation-circle",
              onOk() {
                that.isDisable = false;
                that.forceRemoveModalDom();
              },
              onCancel() {
                that.createdItemQuestion(data);
                that.forceRemoveModalDom();
              },
            });
          } else {
            that.createdItemQuestion(data);
          }
        })
        .catch((res) => {
          console.log(res);
        });
    },
    goBack() {
      window.location.href = $("[name=back_url]").val();
    },
    deleteAttachment(fileId) {
      this.fileId = fileId;
    },
    previewAttachment(fileId) {
      this.fileId = fileId;
    },
    downloadAttachment(fileId) {
      this.fileId = fileId;
    },
  },
};
</script>