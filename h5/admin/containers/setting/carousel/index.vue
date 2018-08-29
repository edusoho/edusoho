<template>
  <module-frame containerClass="setting-carousel" :isActive="isActive">
    <div slot="preview" class="carousel-image-container">
      <img class="carousel-image"
        v-show="carouselImage"
        :src="carouselImage">
      <div class="carousel-title ellipsis">{{carouselTitle}}</div>
    </div>

    <div slot="setting" class="carousel-allocate">
      <header class="title">轮播图设置</header>
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
      slot="modal" limit=1
      :visible="modalVisible"
      :courseList="courseSets[activeItemIndex] || []"
      @visibleChange="modalVisibleHandler"
      @updateCourses="getUpdatedCourses">
    </course-modal>
  </module-frame>
</template>

<script>
import Api from '@admin/api';
import moduleDefault from '@admin/utils/module-default-config';
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
  },
  computed: {
    isActive: {
      get() {
        return this.active;
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
      this.copyModuleData.data.push(Object.assign(moduleDefault.slideShow.data[0]))
    },
    selected(selected) {
      this.activeItemIndex = selected.selectIndex;
    },
    getUpdatedCourses(courses) {
      this.courseSets[this.activeItemIndex] = [{
        id: courses[0].id,
        title: courses[0].title || courses[0].courseSetTitle,
      }];
    },
    modalVisibleHandler(visible) {
      this.modalVisible = visible;
    },
    openModal() {
      this.modalVisible = true;
    },
    itemRemove(index) {
      this.activeItemIndex = index - 1;
      this.copyModuleData.data.splice(index, 1);
    },
    removeCourseLink(index) {
      this.copyModuleData.data[index].link.target = '';
    }
  }
}
</script>
