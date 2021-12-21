<template>
  <div>
    <a-upload
      accept="image/*"
      :file-list="[]"
      :customRequest="() => {}"
      @change="handleChange"
    >
      <slot name="content">
        <a-button>上传图片</a-button>
      </slot>
    </a-upload>

    <a-modal
      v-if="crop"
      :title="title"
      :key="cropperKey"
      :confirm-loading="confirmLoading"
      :visible="visible"
      :mask-closable="false"
      @ok="handleOk"
      @cancel="handleCancel"
    >
      <vue-cropper
        ref="cropper"
        :view-mode="1"
        :auto-crop-area="1"
        :aspect-ratio="aspectRatio"
        :src="imgUrl"
      />
    </a-modal>
  </div>
</template>

<script>
import _ from 'lodash';
import VueCropper from 'vue-cropperjs';
import 'cropperjs/dist/cropper.css';
import { File } from 'common/vue/service';

export default {
  name: 'UploadImage',

  components: {
    VueCropper
  },

  props: {
    // 是否裁剪
    crop: {
      type: Boolean,
      default: true
    },

    title: {
      type: String,
      default: '图片裁剪',
    },

    // 裁剪比例
    aspectRatio: {
      type: Number
    }
  },

  data() {
    return {
      confirmLoading: false,
      imgUrl: '',
      visible: false,
      cropperKey: 0
    }
  },

  mounted() {
    this.crop && this.toBlobPolyfillInIE();
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

    handleChange(info) {
      const reader = new FileReader();
      reader.readAsDataURL(info.file.originFileObj);
      reader.onload = (event) => {
        this.imgUrl = event.target.result;
        if (this.crop) {
          _.assign(this, {
            cropperKey: this.cropperKey + 1,
            visible: true
          });
        } else {
          this.uploadImage(this.imgUrl);
        }
      };
    },

    handleCancel() {
      this.visible = false;
    },

    // 裁剪保存
    handleOk() {
      this.confirmLoading = true;
      const cropImg = this.$refs.cropper.getCroppedCanvas().toDataURL();
      this.uploadImage(cropImg);
    },

    async uploadImage(file) {
      const formData = new FormData();
      formData.append('file', file);
      formData.append('group', 'system');

      try {
        const data = await File.file(formData);
        this.$emit('success', data);
      } catch(error) {
        const { status } = error.response;
        if (status == 413) {
          Vue.prototype.$message.error('文件过大，请上传小于 2M 的文件！');
        }
      } finally {
        this.crop && _.assign(this, {
          confirmLoading: false,
          visible: false
        });
      }
    }
  }
}
</script>
