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

  methods: {
    async getEditorUploadToken() {
      const { token } = await UploadToken.get('course');
      this.imageUploadUrl += token;
      this.flashUploadUrl += token;
    },

    async initCkeditor(content = '') {
      await this.getEditorUploadToken();
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
        this.editor.on('instanceReady', () => {
          this.setData(content);
        });
      });
    },

    getData() {
      return this.editor.getData(this.content);
    },

    setData(content) {
      if (!content) return;
      this.editor.setData(content);
    }
  }
}
</script>

<style lang="less" scoped>
#summary {
  height: 400px;
}
</style>
