<template>
  <div id="app" class="test-vue">
    <item-report
      :answerReport="answerReport"
      :assessment="assessment"
      :answerRecord="answerRecord"
      :answerScene="answerScene"
      @doAgainEvent="doAgainEvent"
    ></item-report>
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
      doAgainEvent(data) {
          
      },
      getReviewData(data) {

      }
    }
  }
</script>
