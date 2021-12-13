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

      <div class="design-editor__item clearfix">
        <span class="pull-left">选择链接：</span>
        <div
          v-show="selectdLink"
          class="pull-left text-overflow selectd-link"
        >
          {{ selectdLink }}
          <a-icon @click="handleModity" type="close-circle" />
        </div>
        <a-dropdown class="pull-left">
          <a class="ant-dropdown-link" @click="(e) => e.preventDefault()">
            {{ selectText }}<a-icon type="down" />
          </a>
          <a-menu slot="overlay" @click="handleSelectLink">
            <a-menu-item key="course">选择课程</a-menu-item>
            <a-menu-item key="classroom">选择班级</a-menu-item>
            <a-menu-item key="vip">选择会员</a-menu-item>
            <a-menu-item key="custom">自定义链接</a-menu-item>
          </a-menu>
        </a-dropdown>
      </div>

      <div class="design-editor__item">
        <span>自适应手机屏幕：</span>
        <a-radio-group :default-value="moduleData.responsive" @change="handleChangeResponsive">
          <a-radio value="1">
            开启
          </a-radio>
          <a-radio value="0">
            关闭
          </a-radio>
        </a-radio-group>
      </div>
    </div>

    <custom-link-modal ref="customLink" @update-link="handleUpdateLink" />
    <course-link-modal ref="courseLink" @update-link="handleUpdateLink" />
    <classroom-link-modal ref="classroomLink" @update-link="handleUpdateLink" />
  </edit-layout>
</template>

<script>
import _ from 'lodash';
import EditLayout from '../EditLayout.vue';
import { UploadToken, File } from 'common/vue/service';
import CustomLinkModal from '../CustomLinkModal.vue';
import CourseLinkModal from '../CourseLinkModal.vue';
import ClassroomLinkModal from '../ClassroomLinkModal.vue';

export default {
  name: 'PosterEdit',

  props: {
    moduleData: {
      type: Object,
      required: true
    }
  },

  components: {
    EditLayout,
    CustomLinkModal,
    CourseLinkModal,
    ClassroomLinkModal
  },

  data() {
    return {
      uploadToken: {}
    }
  },

  computed: {
    selectdLink() {
      const { target, type, url } = this.moduleData.link;
      if (!type && url) return url;

      if (type === 'vip') return '会员专区';

      if (_.includes(['classroom', 'course'], type)) {
        const { title, displayedTitle } = target;
        return title || displayedTitle;
      }

      return '';
    },

    selectText() {
      return this.selectdLink ? '修改' : '选择链接';
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
        this.update({ key: 'image', value: data });
      } catch(error) {
        const { status } = error.response;
        if (status == 413) {
          Vue.prototype.$message.error('文件过大，请上传小于 2M 的文件！');
        }
      }
    },

    handleChangeResponsive(e) {
      this.update({ key: 'responsive', value: e.target.value });
    },

    handleSelectLink({ key }) {
      if (key === 'vip') {
        const params = {
          target: null,
          type: 'vip',
          url: ''
        };
        this.update({ key: 'link', value: params });
        return;
      }

      if (key === 'custom') {
        this.$refs.customLink.showModal();
        return;
      }

      if (key === 'course') {
        this.$refs.courseLink.showModal();
        return;
      }

      if (key === 'classroom') {
        this.$refs.classroomLink.showModal();
        return;
      }
    },

    handleModity() {
      const params = {
        target: null,
        type: '',
        url: ''
      };
      this.update({ key: 'link', value: params });
    },

    handleUpdateLink(params) {
      this.update({ key: 'link', value: params });
    },

    update({ key, value }) {
      this.$emit('update-edit', { type: 'poster', key, value });
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

.selectd-link {
  position: relative;
  padding-right: 30px;
  max-width: 160px;

  i {
    position: absolute;
    right: 12px;
    top: 3px;
    display: none;
    color: #31A1FF;
  }

  &:hover {
    i {
      display: block;
    }
  }
}
</style>
