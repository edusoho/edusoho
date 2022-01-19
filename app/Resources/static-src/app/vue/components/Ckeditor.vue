<template>
  <div id="summary" />
</template>

<script>
import loadScript from 'load-script';
import { UploadToken } from 'common/vue/service';
const CKEDITOR_BASEPATH = app.basePath + '/static-dist/libs/es-ckeditor/';
window.CKEDITOR_BASEPATH = CKEDITOR_BASEPATH;

export default {
  data() {
    return {
      editor: null,
      imageUploadUrl: '/editor/upload?token=',
      flashUploadUrl: '/editor/upload?token='
    }
  },

  async mounted() {
    await this.getEditorUploadToken();
    this.initCkeditor();
  },

  methods: {
    async getEditorUploadToken() {
      const { token } = await UploadToken.get('course');
      this.imageUploadUrl += token;
      this.flashUploadUrl += token;
    },

    initCkeditor() {
      loadScript(`${CKEDITOR_BASEPATH}/ckeditor.js`,(err) => {
        if (err) throw err;
        this.editor = CKEDITOR.replace('summary', {
          allowedContent: true,
          toolbar: 'Detail',
          height: 400,
          fileSingleSizeLimit: app.fileSingleSizeLimit,
          filebrowserImageUploadUrl: this.imageUploadUrl,
          filebrowserFlashUploadUrl: this.flashUploadUrl
        });
      });
    }
  }
}
</script>

<style lang="less" scoped>
#summary {
  height: 400px;
}
</style>
