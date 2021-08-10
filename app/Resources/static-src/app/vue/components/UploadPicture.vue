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
        :view-mode="3"
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
import { UploadToken, File } from 'common/vue/service/index.js';

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
      cropperKey: 0,
      imgs: {}
    }
  },

  created() {
    this.pictureUrl = this.file;
  },

  watch: {
    file() {
      this.pictureUrl = this.file;
    }
  },

  methods: {
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
        } finally {
          this.loading = false;
          this.visible = false;
          this.uploadLoading = false;
        }
      });
    },

    // 后端负责裁剪
    async handleSaveCropper2() {
      this.loading = true;

      if (!this.uploadToken.expiry || (new Date() >= new Date(this.uploadToken.expiry))) {
        await this.getUploadToken();
      }

      const cropper = this.$refs.cropper;

      cropper.getCroppedCanvas().toBlob(async blob => {
        const { x, y, width, height } = cropper.getData();
        const { naturalWidth, naturalHeight } = cropper.getImageData();

        const cropperData = {
          x: _.ceil(_.max([0, x])),
          y: _.ceil(_.max([0, y])),
          width: _.ceil(width),
          height: _.ceil(height)
        };

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
          width: naturalWidth, // 原图片宽度
          height: naturalHeight, // 原图片高度
          group: 'course',
          post: false
        };
        const formData = new FormData();

        formData.append('file', blob, this.pictureName);
        formData.append('token', this.uploadToken.token);

        this.uploadLoading = true;

        try {
          const { url } = await File.uploadFile(formData);

          this.pictureUrl = url;
          this.$emit('success', url)

          const formData1 = new FormData();
          for(const key in cropResult) {
            formData1.append(key, cropResult[key]);
          }

          this.imgs = await File.imgCrop(formData1);
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
