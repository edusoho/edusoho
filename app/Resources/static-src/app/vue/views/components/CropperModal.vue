<template>
  <a-modal
    :key="key"
    title="图片裁剪"
    :visible="visible"
    @cancel="handleCancelCropper">
    <vue-cropper
      ref="cropper"
      :aspect-ratio="aspectRatio"
      :src="imgUrl"
    >
    </vue-cropper>
    <template slot="footer">
      <a-button @click="handleReselect">重新选择</a-button>
      <a-button type="primary" @click="handleSaveCropper" :loading="loading">保存图片</a-button>
    </template>
  </a-modal>
</template>

<script>
import _ from 'lodash';
import VueCropper from 'vue-cropperjs';
import 'cropperjs/dist/cropper.css';
import { UploadToken, File } from 'common/vue/service/index.js';

export default {
  name: 'CropperModal',

  components: {
    VueCropper
  },

  props: {
    visible: {
      type: Boolean,
      required: true
    },

    aspectRatio: {
      type: Number
    },

    imgUrl: {
      type: String,
      required: true
    },

    imgName: {
      type: String,
      required: true
    }
  },

  data() {
    return {
      key: 0,
      loading: false,
      uploadToken: {}
    }
  },

  watch: {
    imgUrl() {
      this.key++;
    }
  },

  methods: {
    async getUploadToken() {
      this.uploadToken = await UploadToken.get('default');
    },

    handleCancelCropper() {
      this.$emit('cancal');
    },

    handleReselect() {
      this.$emit('reselect');
    },

    async handleSaveCropper() {
      this.loading = true;

      if (!this.uploadToken.expiry || (new Date() >= new Date(this.uploadToken.expiry))) {
        await this.getUploadToken();
      }

      const cropper = this.$refs.cropper;

      cropper.getCroppedCanvas().toBlob(async blob => {
        const { x, y, width, height } = cropper.getData();
        const imageData = cropper.getImageData();
        const cropperData = {
          x: _.ceil(_.max([0, x])),
          y: _.ceil(_.max([0, y])),
          width: _.ceil(width),
          height: _.ceil(height)
        }
        const cropResult = {
          x: cropperData.x,
          y: cropperData.y,
          x2: _.add(cropperData.x, cropperData.width),
          y2: _.add(cropperData.y, cropperData.height),
          w: cropperData.width, // 裁剪后宽度
          h: cropperData.height, // 裁剪后高度
          'imgs[large][0]': 480,
          'imgs[large][1]': 270,
          'imgs[middle][0]': 304,
          'imgs[middle][1]': 171,
          'imgs[small][0]': 96,
          'imgs[small][1]': 54,
          post: false,
          width: imageData.naturalWidth, // 原图片宽度
          height: imageData.naturalHeight, // 原图片高度
          group: 'course',
          post: false
        }
        const formData = new FormData();

        formData.append('file', blob, this.imgName);
        formData.append('token', this.uploadToken.token);

        try {
          const { url } = await File.uploadFile(formData)

          const formData1 = new FormData();
          for(const key in cropResult) {
            formData1.append(key, cropResult[key]);
          }

          const imgs = await File.imgCrop(formData1);

          this.$emit('save', { imgUrl: url, imgs });
        } finally {
          this.loading = false;
        }
      });
    }
  }
}
</script>
