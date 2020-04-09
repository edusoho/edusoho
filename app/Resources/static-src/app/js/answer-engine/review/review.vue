<template>
  <div id="app" class="test-vue">
    <item-review
      :sections="sections"
      :reportSections="reportSections"
      @getReviewData="getReviewData"
      :showCKEditorData="showCKEditorData"
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
        const assessment = JSON.parse($('[name=assessment]').val());
        const answerReport = JSON.parse($('[name=answer_report]').val());
        this.sections = assessment.sections;
        this.reportSections = answerReport;
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
      }
    }
  }
</script>
