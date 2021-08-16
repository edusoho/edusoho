<template>
  <div>
    <a-upload
      ref="upload"
      accept="image/*"
      :file-list="[]"
      list-type="picture-card"
      :customRequest="() => {}"
      @change="uploadPicture"
    >
      <img v-if="pictureUrl" :src="pictureUrl" style="width: 100%;" />
      <div v-else>
        <a-icon :type="loading ? 'loading' : 'plus'" />
        <div class="ant-upload-text">
          上传照片
        </div>
      </div>
    </a-upload>
    <p v-if="tip" class="mb0" style="font-size: 14px; line-height: 20px;">{{ tip }}</p>

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
  </div>
</template>

<script>
import _ from 'lodash';
import VueCropper from 'vue-cropperjs';
import 'cropperjs/dist/cropper.css';
import { UploadToken, File } from 'common/vue/service';

export default {
  name: 'UploadPicture',

  components: {
    VueCropper
  },

  props: {
    title: {
      type: String,
      default: '图片裁剪',
    },

    tip: {
      type: String,
      default: '',
    },

    aspectRatio: {
      type: Number
    },

    file: {
      type: String,
      default: ''
    }
  },

  data() {
    return {
      loading: false,
      pictureName: '',
      pictureUrl: '', // 裁剪后图片
      imgUrl: '', // 原图片
      visible: false,
      uploadLoading: false,
      uploadToken: {},
      cropperKey: 0
    }
  },

  created() {
    this.pictureUrl = this.file;
    this.toBlobPolyfillInIE();
  },

  watch: {
    file() {
      this.pictureUrl = this.file;
    }
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

    uploadPicture(info) {
      this.cropperKey++;

      const reader = new FileReader();

      reader.onload = (event) => {
        this.imgUrl = event.target.result;
        this.visible = true;
      };

      this.pictureName = info.file.originFileObj.name;
      reader.readAsDataURL(info.file.originFileObj);
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

        formData.append('file', blob, this.pictureName);
        formData.append('token', this.uploadToken.token);

        this.uploadLoading = true;

        try {
          const { url, id } = await File.uploadFile(formData);
          this.pictureUrl = url;
          this.$emit('success', id);
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

<style lang="less" scoped>
@import "~common/variable.less";

.ant-upload-select-picture-card {
  i {
    font-size: 32px;
    color: @gray;
  }
}

.ant-upload-select-picture-card .ant-upload-text {
  margin-top: 8px;
  color: @gray-dark;
}
</style>
