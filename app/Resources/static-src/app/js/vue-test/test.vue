<template>
  <div id="app">
    <div>{{ message }}</div>
    <item-manage :showCKEditorData="showCKEditorData"></item-manage>
  </div>
</template>


<script>


export default {
  data() {
    return {
      message: '信dsfdsfs息',
      showCKEditorData: {
        publicPath: "http://t5.edusoho.cn/static-dist/libs/es-ckeditor/ckeditor.js",
      },
    };
  }
};
</script>