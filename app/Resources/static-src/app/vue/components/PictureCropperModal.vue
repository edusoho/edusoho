<template>
  <a-modal
    :title="title"
    :key="cropperKey"
    :visible="visible"
    :mask-closable="false"
    @cancel="handleCancel"
  >
    <vue-cropper
      ref="cropper"
      :view-mode="1"
      :auto-crop-area="1"
      :aspect-ratio="aspectRatio"
      :src="imgUrl"
    />
    <template slot="footer">
      <a-button @click="handleReselect">重新选择</a-button>
      <a-button type="primary" @click="handleSaveCropper" :loading="uploadLoading">保存图片</a-button>
    </template>
  </a-modal>
</template>

<script>
import _ from 'lodash';
import VueCropper from 'vue-cropperjs';
import 'cropperjs/dist/cropper.css';
import { UploadToken, File } from 'common/vue/service';

export default {
  name: 'PictureCropperModal',

  components: {
    VueCropper
  },

  props: {
    title: {
      type: String,
      default: '图片裁剪',
    },

    aspectRatio: {
      type: Number
    }
  },

  data() {
    return {
      loading: false,
      imgName: '',
      imgUrl: '',
      visible: false,
      uploadLoading: false,
      uploadToken: {},
      cropperKey: 0
    }
  },

  created() {
    this.toBlobPolyfillInIE();
  },

  methods: {
    // IE toBlob 兼容处理
    toBlobPolyfillInIE() {
      if (!HTMLCanvasElement.prototype.toBlob) {
        Object.defineProperty(HTMLCanvasElement.prototype, 'toBlob', {
          value: function (callback, type, quality) {
            var canvas = this;
            setTimeout(function() {
              var binStr = atob( canvas.toDataURL(type, quality).split(',')[1] );
              var len = binStr.length;
              var arr = new Uint8Array(len);

              for (var i = 0; i < len; i++) {
                arr[i] = binStr.charCodeAt(i);
              }

              callback(new Blob([arr], { type: type || 'image/png' }));
            });
          }
        });
      }
    },

    async getUploadToken() {
      this.uploadToken = await UploadToken.get('default');
    },

    showModal(params) {
      const { imgUrl, imgName } = params;
      _.assign(this, {
        visible: true,
        cropperKey: this.cropperKey + 1,
        imgUrl,
        imgName
      });
    },

    handleCancel() {
      this.visible = false;
    },

    handleReselect() {
      const $inputs = this.$refs.upload.$el.getElementsByTagName('input');

      this.visible = false;

      if ($inputs.length > 0) {
        $inputs[0].click();
      }
    },

    // 前端裁剪，上传裁剪后的图片
    async handleSaveCropper() {
      this.loading = true;

      if (!this.uploadToken.expiry || (new Date() >= new Date(this.uploadToken.expiry))) {
        await this.getUploadToken();
      }

      const cropper = this.$refs.cropper;

      cropper.getCroppedCanvas().toBlob(async blob => {
        const formData = new FormData();

        formData.append('file', blob, this.imgName);
        formData.append('token', this.uploadToken.token);

        this.uploadLoading = true;

        try {
          const data = await File.uploadFile(formData);
          this.$emit('success', data);
        } catch(error) {
          const { status } = error.response;
          if (status == 413) {
            Vue.prototype.$message.error('文件过大，请上传小于 2M 的文件！');
          }
        } finally {
          this.loading = false;
          this.visible = false;
          this.uploadLoading = false;
        }
      });
    }
  }
}
</script>
