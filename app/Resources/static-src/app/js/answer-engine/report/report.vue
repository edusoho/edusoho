<template>
  <div id="app" class="ibs-vue">
    <item-report
      :answerShow="answerShow"
      :answerReport="answerReport"
      :assessment="assessment"
      :answerRecord="answerRecord"
      :answerScene="answerScene"
      :questionFavorites="questionFavorites"
      :showCKEditorData="showCKEditorData"
      :showAttachment="showAttachment"
      :cdnHost="cdnHost"
      :previewAttachmentCallback="previewAttachmentCallback"
      :downloadAttachmentCallback="downloadAttachmentCallback"
      @previewAttachment="previewAttachment"
      @downloadAttachment="downloadAttachment"
      @doAgainEvent="doAgainEvent"
      @cancelFavoriteEvent="cancelFavoriteEvent"
      @favoriteEvent="favoriteEvent"
    ></item-report>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        answerShow: $('[name=answer_show]').val(),
        showCKEditorData: {
          publicPath: $('[name=ckeditor_path]').val(),
          filebrowserImageUploadUrl: $('[name=ckeditor_image_upload_url]').val(),
          filebrowserImageDownloadUrl: $('[name=ckeditor_image_download_url]').val(),
          language: document.documentElement.lang === 'zh_CN' ? 'zh-cn' : document.documentElement.lang,
          jqueryPath: $('[name=jquery_path]').val()
        },
        showAttachment: $('[name=show_attachment]').val(),
        cdnHost: $('[name=cdn_host]').val(),
        fileId: 0,
      };
    },
    created() {
        this.assessment = JSON.parse($('[name=assessment]').val());
        this.answerReport = JSON.parse($('[name=answer_report]').val());
        this.answerRecord = JSON.parse($('[name=answer_record]').val());
        this.answerScene = JSON.parse($('[name=answer_scene]').val());
        this.questionFavorites = JSON.parse($('[name=question_favorites]').val());
    },
    methods: {
      doAgainEvent(data) {
        location.href = $('[name=restart_url]').val();
      },
      cancelFavoriteEvent(favorite) {
        $.ajax({
          url: '/api/me/question_favorite/1',
          headers:{
            'Accept':'application/vnd.edusoho.v2+json'
          },
          contentType: 'application/json;charset=utf-8',
          type: 'DELETE',
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
          },
          data: JSON.stringify(favorite),
        }).done(function (res) {
          
        })
      },
      favoriteEvent(favorite) {
        $.ajax({
          url: '/api/me/question_favorite',
          headers:{
            'Accept':'application/vnd.edusoho.v2+json'
          },
          contentType: 'application/json;charset=utf-8',
          type: 'POST',
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
          },
          data: JSON.stringify(favorite),
        }).done(function (res) {
          
        })
      },
      deleteAttachment(fileId, flag) {
        if (flag) {
          this.fileId = fileId;
        }
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
            console.log(app);
            console.log(resp);
            resp.data['playServer'] = app.cloudPlayServer;
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
