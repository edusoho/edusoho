<template>
  <edit-layout>
    <template #title>{{ 'decorate.image_ad_settings' | trans }}</template>

    <div class="design-editor">
      <div class="design-editor__item clearfix">
        <span class="design-editor__required pull-left">{{ 'ad_pictures' | trans }}：</span>
        <div class="poster-image pull-left">
          <img class="poster-image__img" v-if="moduleData.image.uri" :src="moduleData.image.uri" />
          <upload-image :crop="false" @success="uploadImageSuccess">
            <template #content>
              <div class="poster-image__modify" v-if="moduleData.image.uri">{{ 'decorate.change_picture' | trans }}</div>
              <div v-else class="poster-image__add">+ {{ 'decorate.add_pictures' | trans }}</div>
            </template>
          </upload-image>
        </div>
      </div>

      <div class="design-editor__item clearfix">
        <span class="pull-left">{{ 'decorate.select_link' | trans }}：</span>
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
            <a-menu-item key="course">{{ 'decorate.choose_a_course' | trans }}</a-menu-item>
            <a-menu-item key="classroom">{{ 'decorate.select_class' | trans }}</a-menu-item>
            <a-menu-item key="vip">{{ 'decorate.select_member' | trans }}</a-menu-item>
            <a-menu-item key="custom">{{ 'decorate.custom_link' | trans }}</a-menu-item>
          </a-menu>
        </a-dropdown>
      </div>

      <div class="design-editor__item">
        <span>{{ 'decorate.mobile_phone_screen' | trans }}：</span>
        <a-radio-group :default-value="moduleData.responsive" @change="handleChangeResponsive">
          <a-radio value="1">
            {{ 'decorate.turn_on' | trans }}
          </a-radio>
          <a-radio value="0">
            {{ 'decorate.closure' | trans }}
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
import CustomLinkModal from '../CustomLinkModal.vue';
import CourseLinkModal from '../CourseLinkModal.vue';
import ClassroomLinkModal from '../ClassroomLinkModal.vue';
import UploadImage from 'app/vue/components/UploadFile/Image.vue';

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
    ClassroomLinkModal,
    UploadImage
  },

  computed: {
    selectdLink() {
      const { target, type, url } = this.moduleData.link;
      if (!type && url) return url;

      if (type === 'vip') return Translator.trans('members_only');

      if (_.includes(['classroom', 'course'], type)) {
        const { title, displayedTitle } = target;
        return title || displayedTitle;
      }

      return '';
    },

    selectText() {
      return this.selectdLink ? Translator.trans('decorate.revise') : Translator.trans('decorate.select_link');
    }
  },

  methods: {
    uploadImageSuccess(data) {
      this.update({ key: 'image', value: data });
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
