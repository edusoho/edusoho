<template>
  <div id="update-duplicate-check" class="ibs-vue">
    <div class="duplicate-head flex items-center">
      <span class="duplicate-back flex items-center" @click="goLast">
        <span class="es-icon es-icon-fanhui mr4"></span>
        {{ 'importer.import_back_btn'|trans }}
      </span>
      <span class="duplicate-divider"></span>
      <span class="duplicate-title">{{ 'question.bank.head.edit'|trans }}</span>
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
          that.$message.error(res.message);
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
    previewAttachmentCallback() {
        let that = this;
        return new Promise(resolve => {
          $.ajax({
            url: $('[name=preview-attachment-url]').val(),
            type: 'post',
            data: {id: this.fileId},
            beforeSend(request) {
              request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
            }
          }).done(function (resp) {
            resp.data['sdkBaseUri'] = app.cloudSdkBaseUri;
            resp.data['disableDataUpload'] = app.cloudDisableLogReport;
            resp.data['disableSentry'] = app.cloudDisableLogReport;
            resolve(resp);
            that.fileId = 0;
          })
        });
    },
    downloadAttachmentCallback() {
      let that = this;
      return new Promise(resolve => {
        $.ajax({
          url: $('[name=download-attachment-url]').val(),
          type: 'post',
          data: {id: this.fileId},
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
          }
        }).done(function (resp) {
          resolve(resp);
          that.fileId = 0;
        })
      });
    },
    deleteAttachmentCallback() {
      let that = this;
      return new Promise(resolve => {
        $.ajax({
          url: $('[name=delete-attachment-url]').val(),
          type: 'post',
          data: {id: this.fileId},
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
          }
        }).done(function (resp) {
          resolve(resp);
          that.fileId = 0;
        })
      });
    },
    getRepeatStem(data) {
      const stem = data.data.material !== '' ? data.data.material : data.data.questions[0].stem
      return new Promise(resolve => {
        $.ajax({
          url: $('[name=check_duplicative_url]').val(),
          contentType: 'application/json;charset=utf-8',
          type: 'post',
          data: JSON.stringify({material:stem}),
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
          }
        }).done(function (res) {
          resolve(res);
        })
      });
    },
    forceRemoveModalDom() {
      const modal = document.querySelector(".ant-modal-root");

      if (modal) {
        modal.remove();
      }

      document.body.style = "";
    },
    createdItemQuestion(data) {
      console.log(data);
      let submission = data.isAgain ? 'continue' : '';
      data = data.data;
      data['submission'] = submission;
      data['type'] = $('[name=type]').val();
      let mode = 'edit';
      $.ajax({
        url: $('[name=update_url]').val(),
        contentType: 'application/json;charset=utf-8',
        type: 'post',
        data: JSON.stringify(data),
        beforeSend(request) {
          request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
        }
      }).done((res) => {
          window.location.href = $('[name=back_url]').val() + '?type=afterEdit';
      })
    },
  },
};
</script>