<template>
  <edit-layout>
    <template #title>轮播图</template>

    <div class="design-editor">
      <div class="design-editor__title">添加内容</div>
      <div class="design-editor__image">
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
              @remove="handleClickRemove"
            />
          </transition-group>
        </draggable>

        <div class="add-btn-input">
          <upload-image :aspect-ratio="5 / 2" @success="handleAddSwiper">
            <template #content>
              <div class="add-btn-input">
                +添加图片
              </div>
            </template>
          </upload-image>
        </div>

        <div class="image-tips">·建议图片尺寸为750x300px，支持 jpg/png/gif 格式，大小不超过2MB</div>
        <div class="image-tips">·最多添加5个图片，拖动选中的图片可对其排序</div>
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
          link: { type: '', target: null, url: 'javascript:;' },
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

    handleUpdateLink(data) {
      const params = {
        key: 'link',
        vlaue: data
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
.design-editor {
  &__title {
    margin-bottom: 10px;
    color: #a3a0a0;
  }

  &__image {
    width: 100%;
    padding: 8px;
    background: rgba(237, 237, 237, 0.53);

    .add-btn-input {
      width: 100%;
      height: 54px;
      line-height: 54px;
      cursor: pointer;
      text-align: center;
      background-color: #fff;

      /deep/ .ant-upload {
        color: #31a1ff;
        width: 100%;
      }
    }

    .image-tips {
      margin-top: 10px;
      font-size: 12px;
      color: #888;
    }
  }
}
</style>
