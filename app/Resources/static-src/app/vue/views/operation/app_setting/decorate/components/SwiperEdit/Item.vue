<template>
  <div class="image-item">
    <a-icon
      class="remove-btn"
      type="close-circle"
      theme="filled"
      @click="handleClickRemove"
    />
    <div class="image-item__img">
      <img :src="item.image.uri" />

      <div class="re-upload">
        <upload-image :aspect-ratio="343 / 136" @success="handleUploadSuccess">
          <template #content>
            <div class="re-upload-mask" />
            <div class="re-upload-text">
              {{ 'decorate.change_picture' | trans }}
            </div>
          </template>
        </upload-image>
      </div>
    </div>
    <div class="mt16 clearfix">
      <span class="pull-left" style="font-size: 14px; color: #666;">{{ 'decorate.select_link' | trans }}：</span>
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
        <a-menu slot="overlay" @click="selectLink">
          <a-menu-item key="course">{{ 'decorate.choose_a_course' | trans }}</a-menu-item>
          <a-menu-item key="classroom">{{ 'decorate.select_class' | trans }}</a-menu-item>
          <a-menu-item key="vip">{{ 'decorate.select_member' | trans }}</a-menu-item>
          <a-menu-item key="custom">{{ 'decorate.custom_link' | trans }}</a-menu-item>
        </a-menu>
      </a-dropdown>
    </div>
  </div>
</template>

<script>
import UploadImage from 'app/vue/components/UploadFile/Image.vue';

export default {
  name: 'SwiperEditItem',

  props: {
    item: {
      type: Object,
      required: true
    },

    index: {
      type: Number,
      required: true
    }
  },

  components: {
    UploadImage
  },

  computed: {
    selectdLink() {
      if (!this.item.link) return '';

      const { target, type, url } = this.item.link;
      if (type === 'url') return url;
      if (type === 'vip') return Translator.trans('members_only');

      if (_.includes(['classroom', 'course'], type) && target) {
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
    handleUploadSuccess(data) {
      const params = {
        key: 'edit',
        index: this.index,
        value: data
      };
      this.$emit('update-image', params);
    },

    selectLink({ key }) {
      const params = {
        type: key,
        index: this.index
      };
      this.$emit('select-link', params);
    },

    handleModity() {
      this.$emit('remove-link', this.index);
    },

    handleClickRemove() {
      this.$emit('remove', this.index);
    }
  }
}
</script>

<style lang="less" scoped>
.image-item {
  position: relative;
  padding: 15px 10px;
  margin-bottom: 24px;
  width: 100%;
  border: 1px solid #e1e1e1;
  background-color: #fff;
  font-size: 12px;
  cursor: move;

  .remove-btn {
    position: absolute;
    top: -6px;
    right: -6px;
    display: none;
    font-size: 18px;
    color: #bbb;
    text-align: center;
    cursor: pointer;
    transform: all .3s ease;

    &:hover {
      color: #aaa;
    }
  }

  &__img {
    position: relative;
    width: 100%;
    height: auto;

    img {
      width: 100%;
    }

    .re-upload {
      display: none;
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      cursor: pointer;

      .re-upload-mask {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0.6;
        background: black;
      }

      .re-upload-text {
        position: absolute;
        top: 0;
        left: 0;
        line-height: 124px;
        text-align: center;
        width: 100%;
        height: 100%;
        font-size: 18px;
        color: #fff;
      }
    }

    &:hover {
      .re-upload {
        display: block;
      }
    }
  }

  .selectd-link {
    position: relative;
    padding-right: 30px;
    max-width: 160px;
    font-size: 14px;

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

  &:hover {
    .remove-btn {
      display: block;
    }
  }
}
</style>
