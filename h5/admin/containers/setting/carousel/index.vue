<template>
  <module-frame containerClass="setting-carousel" :isActive="isActive" :isIncomplete="isIncomplete">
    <div slot="preview" class="carousel-image-container">
      <img class="carousel-image"
        v-show="carouselImage"
        :src="carouselImage">
      <div class="image-mask" v-show="!carouselImage">
        轮播图
      </div>
      <div class="carousel-title ellipsis">{{carouselTitle}}</div>
    </div>

    <div slot="setting" class="carousel-allocate">
      <header class="title">
        轮播图设置<div class="help-text">建议图片尺寸为750x400px，支持 jpg/png/gif 格式，大小不超过2MB</div></header>
      <div v-for="(item, index) in copyModuleData.data" :key="index">
        <item
          :item="item"
          :index="index"
          :active="activeItemIndex"
          :itemNum="itemNum"
          :courseSets="courseSets[index]"
          @selected="selected"
          @chooseCourse="openModal"
          @remove="itemRemove"
          @removeCourseLink="removeCourseLink"></item>
      </div>
      <el-button class="btn-add-item" type="info" size="medium" @click="addItem" v-show="addBtnShow">添加一个轮播图</el-button>
    </div>

    <course-modal
      slot="modal"
      limit=1
      :type="type"
      :visible="modalVisible"
      :courseList="courseSets[activeItemIndex] || []"
      @visibleChange="modalVisibleHandler"
      @updateCourses="getUpdatedCourses">
    </course-modal>
  </module-frame>
</template>

<script>
import Api from '@admin/api';
import { MODULE_DEFAULT } from '@admin/config/module-default-config';
import item from './item';
import moduleFrame from '../module-frame';
import courseModal from '../course/modal/course-modal';

export default {
  components: {
    item,
    moduleFrame,
    courseModal,
  },
  data() {
    return  {
      activeItemIndex: 0,
      modalVisible: false,
      courseSets: [],
      type: 'course_list'
    }
  },
  props: {
    active: {
      type: Boolean,
      default: false,
    },
    moduleData: {
      type: Object,
      default: {},
    },
    incomplete: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    isActive: {
      get() {
        return this.active;
      },
      set() {}
    },
    isIncomplete: {
      get() {
        return this.incomplete;
      },
      set() {}
    },
    copyModuleData: {
      get() {
        return this.moduleData;
      },
      set() {
        console.log('changed copyModuleData')
      }
    },
    carouselImage() {
      return this.copyModuleData.data[this.activeItemIndex]
        && this.copyModuleData.data[this.activeItemIndex].image.uri;
    },
    carouselTitle() {
      return this.copyModuleData.data[this.activeItemIndex]
        && this.copyModuleData.data[this.activeItemIndex].title;
    },
    itemNum() {
      return this.copyModuleData.data.length;
    },
    addBtnShow() {
      return this.itemNum < 5;
    },
  },
  watch: {
    copyModuleData: {
      handler(data) {
        this.$emit('updateModule', data);
      },
      deep: true,
    },
  },
  methods: {
    addItem() {
      // 需要深拷贝对象
      const itemString = JSON.stringify(MODULE_DEFAULT.slideShow.data[0]);
      const itemObject = JSON.parse(itemString);
      this.copyModuleData.data.push(itemObject)
    },
    selected(selected) {
      this.activeItemIndex = selected.selectIndex;
    },
    getUpdatedCourses(data) {
      if (this.type === 'class_list') {
        this.courseSets[this.activeItemIndex] = [{
          id: data[0].id,
          // courseSetId: data[0].courseSet.id,
          title: data[0].title,
          displayedTitle: data[0].title,
        }];
        return;
      }
      this.courseSets[this.activeItemIndex] = [{
        id: data[0].id,
        courseSetId: data[0].courseSet.id,
        title: data[0].title || data[0].courseSetTitle,
        displayedTitle: data[0].displayedTitle,
      }];
    },
    modalVisibleHandler(visible) {
      this.modalVisible = visible;
    },
    openModal(type) {
      this.type = type
      this.modalVisible = true;
    },
    itemRemove(index) {
      this.activeItemIndex = index - 1;
      this.copyModuleData.data.splice(index, 1);
    },
    removeCourseLink(index) {
      this.copyModuleData.data[index].link.target = '';
      this.courseSets[this.activeItemIndex] = [];
    }
  }
}
</script>
