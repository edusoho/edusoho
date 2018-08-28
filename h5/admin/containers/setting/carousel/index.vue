<template>
  <module-frame containerClass="setting-carousel" :isActive="isActive">
    <div slot="preview" class="carousel-image-container">
      <img v-bind:src="copyModuleData.data[activeItemIndex].image.uri" class="carousel-image">
      <img class="icon-delete" src="static/images/delete.png" @click="handleRemove()" v-show="isActive">
      <div class="carousel-title ellipsis">{{copyModuleData.data[activeItemIndex].title}}</div>
    </div>

    <div slot="setting" class="carousel-allocate">
      <header class="title">轮播图设置</header>
      <div v-for="(item, index) in copyModuleData.data">
        <item
          :item="item"
          :index="index"
          :active="activeItemIndex"
          :itemNum="itemNum"
          :courseSets="courseSets"
          @selected="selected"
          @chooseCourse="openModal"
          @inputChange="inputChange"
          @remove="itemRemove"
          @removeCourseLink="removeCourseLink"></item>
      </div>
      <el-button class="btn-add-item" type="info" size="medium" @click="addItem">添加一个轮播图</el-button>
    </div>

    <course-modal slot="modal" :visible="modalVisible" limit=1
      :courseList="courseSets"
      @visibleChange="modalVisibleHandler"
      @sort="getSortedCourses">
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
      title: '',
      itemNum: 0,
      imgAdress: '',
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
    updateImg() {
      return this.imgAdress
    },
    copyModuleData: {
      get() {
        return this.moduleData;
      },
      set() {
        console.log('changed copyModuleData')
      }
    }
  },
  created() {
    this.addItem();
  },
  methods: {
    addItem() {
      if (this.itemNum < 5) {
        this.copyModuleData.data.push(moduleDefault.slideShow.data[0])
        this.itemNum = this.copyModuleData.data.length;
      } else {
        this.$message({
          message: '最多可以添加五张轮播图',
          type: 'warning'
        });
      }
    },
    selected(selected) {
      this.imgAdress = selected.imageUrl;
      this.activeItemIndex = selected.selectIndex;
    },
    handleRemove() {
      // this.parts[0].data.splice(0, this.parts[0].data.length);
      this.$el.remove();
    },
    getSortedCourses(courses) {
      this.courseSets = courses;
    },
    modalVisibleHandler(visible) {
      this.modalVisible = visible;
    },
    openModal() {
      this.modalVisible = true;
    },
    inputChange(inputChange) {
      this.copyModuleData.data[this.activeItemIndex].title = inputChange.title;
    },
    itemRemove(remove) {
      console.log(remove)
      this.imgAdress = remove.imageUrl;
      this.copyModuleData.data.splice(remove.index, 1);
    },
    removeCourseLink() {
      this.courseSets = this.courseSets.splice(1, 1);
    }
  }
}
</script>
