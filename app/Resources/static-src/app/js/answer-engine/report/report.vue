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
      :showDoAgainBtn="showDoAgainBtn"
      :cdnHost="cdnHost"
      :collect="collect"
      :previewAttachmentCallback="previewAttachmentCallback"
      :downloadAttachmentCallback="downloadAttachmentCallback"
      @previewAttachment="previewAttachment"
      @downloadAttachment="downloadAttachment"
      @doAgainEvent="doAgainEvent"
      @cancelFavoriteEvent="cancelFavoriteEvent"
      @favoriteEvent="favoriteEvent"
      @submitReturn="returnUrlGoto"
    >
      <template slot="returnBtn" v-if="showReturnBtn">
        <div class="ibs-text-center ibs-mt16">
          <a-button type="primary" shape="round" @click="gotoReturnUrl">{{
              "返回错题本"
            }}</a-button>
        </div>
      </template>
    </item-report>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        collect: $("[name='collect']").val() == 1,
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
        showDoAgainBtn: $('[name=show_do_again_btn]').val() === undefined ? 1 : parseInt($('[name=show_do_again_btn]').val()),
        showReturnBtn: $('[name=submit_return_url]').val() === undefined ? 0 : $('[name=submit_return_url]').val().length
      };
    },
    created() {
        const that = this;
        $.ajax({
          url: '/api/answer_record/'+$("[name='answer_record_id']").val(),
          type: 'GET',
          async:false,
          headers:{
            'Accept':'application/vnd.edusoho.v2+json'
          },
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
            request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
          }
        }).done(function (res) {
          that.assessment = res.assessment;
          that.answerReport = res.answer_report;
          that.answerRecord = res.answer_record;
          that.answerScene = res.answer_scene;
        })

        $.ajax({
          url: '/api/assessments/'+that.assessment.id+'/question_favorites',
          type: 'GET',
          async:false,
          headers:{
            'Accept':'application/vnd.edusoho.v2+json'
          },
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
            request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
          }
        }).done(function (res) {
          that.questionFavorites = res;
        })
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
      gotoReturnUrl() {
        parent.location.href = $('[name=submit_return_url]').val();
      },
      returnUrlGoto() {
        parent.location.href = $('[name=submit_return_url]').val();
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
