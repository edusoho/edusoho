<template>
  <div id="app" class="test-vue">
    <item-report
      :sections="sections"
      :reportSections="reportSections"
      :testpaper="testpaper"
      :showCKEditorData="showCKEditorData"
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
        const assessment = JSON.parse($('[name=assessment]').val());
        const answerReport = JSON.parse($('[name=answer_report]').val());
        this.sections = assessment.sections
        this.reportSections = answerReport
        this.testpaper = assessment
    },
    methods: {
      doAgainEvent(data) {
          
      },
      getReviewData(data) {

      }
    }
  }
</script>
