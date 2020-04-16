<template>
  <div id="app" class="test-vue">
    <assessment-preview
      :assessment="assessment"
      :showCKEditorData="showCKEditorData"
    ></assessment-preview>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        showCKEditorData: {
          publicPath: $('[name=ckeditor_path]').val(),
          filebrowserImageUploadUrl: $('[name=ckeditor_image_upload_url]').val(),
          filebrowserImageDownloadUrl: $('[name=ckeditor_image_download_url]').val(),
        },
      };
    },
    created() {
       const assessment = JSON.parse($('[name=assessment]').val());
       this.assessment = assessment;
    },
    methods: {
    }
  }
</script>
