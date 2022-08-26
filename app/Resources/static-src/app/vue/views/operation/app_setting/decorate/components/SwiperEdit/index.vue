<template>
  <edit-layout>
    <template #title>{{ 'decorate.carousel' | trans }}</template>

    <div class="design-editor">
      <draggable
        v-model="myList"
        v-bind="dragOptions"
        @start="drag = true"
        @end="drag = false"
      >
        <transition-group type="transition" :name="!drag ? 'flip-list' : null">
          <item
            v-for="(item, index) in moduleData"
            :key="item.oldKey"
            :index="index"
            :item="item"
            @update-image="handleUpdateImage"
            @select-link="handleSelectLink"
            @remove-link="handleRemoveLink"
            @remove="handleClickRemove"
          />
        </transition-group>
      </draggable>

      <div class="design-editor__item" v-if="moduleData.length < 5">
        <upload-image class="upload-image" :aspect-ratio="343 / 136" @success="handleAddSwiper">
          <template #content>
            <a-button type="primary" block>{{ 'decorate.add_pictures' | trans }}</a-button>
          </template>
        </upload-image>
      </div>

      <div class="design-editor__tips">
        <div>·{{ 'decorate.carousel_tip1' | trans }}</div>
        <div>·{{ 'decorate.carousel_tip2' | trans }}</div>
      </div>
    </div>

    <custom-link-modal ref="customLink" @update-link="handleUpdateLink" />
    <course-link-modal ref="courseLink" @update-link="handleUpdateLink" />
    <classroom-link-modal ref="classroomLink" @update-link="handleUpdateLink" />
  </edit-layout>
</template>

<script>
import _ from 'lodash';
import Draggable from 'vuedraggable';
import EditLayout from '../EditLayout.vue';
import Item from './Item.vue';
import CustomLinkModal from '../CustomLinkModal.vue';
import CourseLinkModal from '../CourseLinkModal.vue';
import ClassroomLinkModal from '../ClassroomLinkModal.vue';
import UploadImage from 'app/vue/components/UploadFile/Image.vue';

export default {
  name: 'SwiperEdit',

  props: {
    moduleData: {
      type: Array,
      required: true
    }
  },

  components: {
    Draggable,
    EditLayout,
    Item,
    CustomLinkModal,
    CourseLinkModal,
    ClassroomLinkModal,
    UploadImage
  },

  data() {
    return {
      currentIndex: 0,
      drag: false
    }
  },

  computed: {
    dragOptions() {
      return {
        animation: 200,
        group: "description",
        disabled: false,
        ghostClass: "ghost"
      }
    },

    myList: {
      get() {
        return this.moduleData;
      },
      set(value) {
        const params = {
          key: 'drag',
          value
        };
        this.update(params);
      }
    }
  },

  mounted() {
    this.initKey();
  },

  methods: {
    handleAddSwiper(data) {
      const oldKey = this.moduleData.length;
      const params = {
        key: 'add',
        value: {
          image: data,
          link: {},
          oldKey
        }
      };
      this.update(params);
    },

    handleClickRemove(index) {
      this.currentIndex = index;
      const params = {
        key: 'remove'
      }
      this.update(params);
    },

    handleSelectLink(params) {
      const { type, index } = params;
      this.currentIndex = index;
      if (type === 'vip') {
        const params = {
          target: null,
          type: 'vip',
          url: ''
        };
        this.handleUpdateLink(params);
        return;
      }

      if (type === 'custom') {
        this.$refs.customLink.showModal();
        return;
      }

      if (type === 'course') {
        this.$refs.courseLink.showModal();
        return;
      }

      if (type === 'classroom') {
        this.$refs.classroomLink.showModal();
        return;
      }
    },

    handleRemoveLink(index) {
      this.currentIndex = index;
      const params = {
        key: 'link',
        value: {
          target: null,
          type: '',
          url: ''
        }
      };
      this.update(params);
    },

    handleUpdateLink(data) {
      const params = {
        key: 'link',
        value: data
      }
      this.update(params);
    },

    handleUpdateImage(params) {
      this.update(params);
    },

    initKey() {
      // oldKey: 防止拖拽后重新渲染 dom
      _.forEach(this.moduleData, (item, index) => {
        item.oldKey = index;
      });
    },

    update(params) {
      this.$emit('update-edit', {
        type: 'swiper',
        index: this.currentIndex,
        ...params
      });
    }
  }
}
</script>

<style lang="less" scoped>
.upload-image {
  display: block !important;
  /deep/ .ant-upload.ant-upload-select {
    display: block;
  }
}
</style>
