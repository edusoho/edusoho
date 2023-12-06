<template>
  <div id="app" class="ibs-vue">
    <item-manage
      v-if="mode === 'create'"
      :bank_id="bank_id"
      :mode="mode"
      :category="category"
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
    <item-manage
      v-if="mode === 'edit'"
      :bank_id="bank_id"
      :mode="mode"
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
</template>

<script>
  export default {
    data() {
      let mode = $('[name=mode]').val();
      let item = {};
      if (mode === 'edit') {
        item = JSON.parse($('[name=item]').val());
        item.questions = Object.values(item.questions);
      }

      return {
        bank_id: $('[name=bank_id]').val(),
        mode: mode,
        category: JSON.parse($('[name=category]').val()),
        subject: item,
        type: $('[name=type]').val(),
        showCKEditorData: {
          publicPath: $('[name=ckeditor_path]').val(),
          filebrowserImageUploadUrl: $('[name=ckeditor_image_upload_url]').val(),
          filebrowserImageDownloadUrl: $('[name=ckeditor_image_download_url]').val(),
          language: document.documentElement.lang === 'zh_CN' ? 'zh-cn' : document.documentElement.lang,
          jqueryPath: $('[name=jquery_path]').val()
        },
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
        fileId: 0,
        isDownload: false,
        isDisable: null,
      };
    },
    provide() {
      return {
        modeOrigin: 'create',
        self: this
      }
    },
    methods: {
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
      getData(data) {
        const that = this
        that.isDisable = true;
        this.getRepeatStem(data).then( (res)=> {
          if (res) {
            this.$confirm({
              title: Translator.trans('created.question.confirm.title'),
              okText: Translator.trans('created.question.confirm.ok.btn'),
              cancelText: Translator.trans('created.question.confirm.close.btn'),
              icon: 'exclamation-circle',
							class: "repeat-stem-text",
              onOk() {
                that.isDisable = false;
                that.forceRemoveModalDom()
              },
              onCancel() {
                that.createdItemQuestion(data)
                that.forceRemoveModalDom()
              },
            });
          } else {
            that.createdItemQuestion(data)
          }
        }).catch( (res)=> {
          console.log(res);
        });
      },
      createdItemQuestion(data) {
        let submission = data.isAgain ? 'continue' : '';
        data = data.data;
        data['submission'] = submission;
        data['type'] = $('[name=type]').val();
        let mode = $('[name=mode]').val();
        $.ajax({
          url: mode === 'create' ? $('[name=create_url]').val() : $('[name=update_url]').val(),
          contentType: 'application/json;charset=utf-8',
          type: 'post',
          data: JSON.stringify(data),
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
          }
        }).done(function (resp) {
          if (resp.goto) {
            window.location.href = resp.goto;
          }
        })
      },
      forceRemoveModalDom() {
        const modal = document.querySelector(".repeat-stem-text").parentNode.parentNode;

        if (modal) {
          modal.remove();
        }

        document.body.style = "";
      },
      goBack() {
        window.location.href = $('[name=back_url]').val();
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
        let self = this;
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
            self.fileId = 0;
          })
        });
      },
      downloadAttachmentCallback() {
        let self = this;
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
            self.fileId = 0;
          })
        });
      },
      deleteAttachmentCallback() {
        let self = this;
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
            self.fileId = 0;
          })
        });
      }
    }
  }
</script>
