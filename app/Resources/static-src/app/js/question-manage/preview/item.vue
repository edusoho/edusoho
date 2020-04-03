<template>
  <div id="app">
    <item-preview
      :item="item"
      :showCKEditorData="showCKEditorData"
    ></item-preview>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        item: JSON.parse($('[name=item]').val()),
        showCKEditorData: {
          publicPath: '/static-dist/libs/es-ckeditor/ckeditor.js',
          // filebrowserImageUploadUrl: $('[name=image_upload_url]').val(),
          // filebrowserImageDownloadUrl: $('[name=image_download_url]').val(),
        },
      }
    }
  }
</script>
