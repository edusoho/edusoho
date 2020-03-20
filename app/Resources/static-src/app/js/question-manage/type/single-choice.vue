<template>
  <div id="app">
    <item-manage :showCKEditorData="showCKEditorData" :type="type"></item-manage>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        type: 'single_choice',
        showCKEditorData: {
          publicPath: "http://t5.edusoho.cn/static-dist/libs/es-ckeditor/ckeditor.js",
        },
      };
    }
  }
</script>
