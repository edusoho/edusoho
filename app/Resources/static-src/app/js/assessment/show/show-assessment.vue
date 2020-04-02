<template>
  <div id="app">
    <item-engine
      :sections="sections"
      :testpaper="testpaper"
      @getAnswerData="getAnswerData"
      :showCKEditorData="showCKEditorData"
    ></item-engine>
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
       console.log(assessment)
       this.sections = assessment.sections;
       this.testpaper = assessment;
    },
    methods: {
      getAnswerData(data) {

      }
    }
  }
</script>
