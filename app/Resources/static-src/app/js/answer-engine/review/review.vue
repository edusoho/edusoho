<template>
  <div id="app" class="test-vue">
    <item-review
      :assessment="assessment"
      :answerReport="answerReport"
      :answerRecord="answerRecord"
      :answerScene="answerScene"
      @getReviewData="getReviewData"
      @getReviewDataAagin="getReviewDataAagin"
    ></item-review>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        showCKEditorData: {
          publicPath: '/static-dist/libs/es-ckeditor/ckeditor.js',
          filebrowserImageUploadUrl: $('[name=image_upload_url]').val(),
          filebrowserImageDownloadUrl: $('[name=image_download_url]').val(),
        },
      };
    },
    created() {
        this.assessment = JSON.parse($('[name=assessment]').val());
        this.answerReport = JSON.parse($('[name=answer_report]').val());
        this.answerRecord = JSON.parse($('[name=answer_record]').val());
        this.answerScene = JSON.parse($('[name=answer_scene]').val());
    },
    methods: {
      getReviewData(reviewReport) {
        
        $.ajax({
          url: $("[name='answer_engine_review_url']").val(),
          contentType: 'application/json;charset=utf-8',
          type: 'post',
          data: JSON.stringify(reviewReport),
          beforeSend(request) {
            request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
          }
        }).done(function (resp) {
          console.log(333333333)
        })

        console.log(reviewReport)
      },
      getReviewDataAagin(reviewReport){

      }
    }
  }
</script>
