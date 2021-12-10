<template>
  <edit-layout>
    <template #title>图片广告设置</template>

    <div class="design-editor">
      <div class="design-editor__item clearfix">
        <span class="design-editor__required pull-left">广告图片：</span>
        <div class="poster-image pull-left">
          <img class="poster-image__img" v-if="moduleData.image.url" :src="moduleData.image.url" />
          <a-upload
            accept="image/*"
            :file-list="[]"
            :customRequest="() => {}"
            @change="handleChange"
          >
            <div class="poster-image__modify" v-if="moduleData.image.url">更换图片</div>
            <div v-else class="poster-image__add">+ 添加图片</div>
          </a-upload>
        </div>
      </div>

      <div class="design-editor__item">
        <span>选择链接：</span>
        <a-radio-group>
          <a-radio value="1">
            开启
          </a-radio>
          <a-radio value="0">
            关闭
          </a-radio>
        </a-radio-group>
      </div>

      <div class="design-editor__item">
        <span>自适应手机屏幕：</span>
        <a-radio-group>
          <a-radio value="1">
            开启
          </a-radio>
          <a-radio value="0">
            关闭
          </a-radio>
        </a-radio-group>
      </div>
    </div>
  </edit-layout>
</template>

<script>
import _ from 'lodash';
import EditLayout from '../EditLayout.vue';
import { UploadToken, File } from 'common/vue/service';

export default {
  name: 'PosterEdit',

  props: {
    moduleData: {
      type: Object,
      required: true
    }
  },

  components: {
    EditLayout
  },

  data() {
    return {
      uploadToken: {}
    }
  },

  methods: {
    async getUploadToken() {
      this.uploadToken = await UploadToken.get('default');
    },

    async handleChange(info) {
      const blob = info.file.originFileObj;

      if (!this.uploadToken.expiry || (new Date() >= new Date(this.uploadToken.expiry))) {
        await this.getUploadToken();
      }

      const formData = new FormData();

      formData.append('file', blob, this.imgName);
      formData.append('token', this.uploadToken.token);

      try {
        const data = await File.uploadFile(formData);
        this.$emit('update-edit', {
          type: 'poster',
          key: 'image',
          value: data
        });
      } catch(error) {
        const { status } = error.response;
        if (status == 413) {
          Vue.prototype.$message.error('文件过大，请上传小于 2M 的文件！');
        }
      }
    }
  }
}
</script>

<style lang="less" scoped>
.poster-image {
  position: relative;
  text-align: center;
  cursor: pointer;
  font-size: 18px;
  width: 250px;
  height: 130px;

  &:hover {
    .poster-image__modify {
      display: block;
    }
  }

  &__add,
  &__modify {
    width: 250px;
    height: 130px;
    line-height: 130px;
  }

  &__img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
  }

  &__add {
    color: #919191;
    background: #f5f5f5;
  }

  &__modify {
    position: absolute;
    top: 0;
    left: 0;
    display: none;
    color: #fff;
    background: rgba(0, 0, 0, 0.5);
  }
}
</style>
