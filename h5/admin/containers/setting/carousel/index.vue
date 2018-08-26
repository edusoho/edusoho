<template>
  <module-frame containerClass="setting-carousel" :isActive="isActive">
    <div slot="preview" class="carousel-image-container">
      <img v-bind:src="updateImg" class="carousel-image">
      <img class="icon-delete" src="static/images/delete.png" @click="handleRemove()" v-show="isActive">
    </div>

    <div slot="setting" class="carousel-allocate">
      <header class="title">轮播图设置</header>
      <div v-for="(item, index) in parts[0].data">
        <item :item="item" :index="index" :active="activeItemIndex"
              @selected="selected" @chooseCourse="openModal"></item>
      </div>
      <el-button class="btn-add-item" type="info" size="medium" @click="addItem">添加一个轮播图</el-button>
    </div>

    <course-modal slot="modal" :visible="modalVisible"
                  :courseList="courseSets"
                  @visibleChange="modalVisibleHandler"
                  @sort="getSortedCourses"></course-modal>
  </module-frame>
</template>

<script>
import Api from '@admin/api';
import item from './item';
import moduleFrame from '../module-frame'
import courseModal from '../course/modal/course-modal'

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
      defaultItem: {
        image: 'http://www.esdev.com/themes/jianmo/img/banner_net.jpg',
        link: {
          type: 'url',
          url: 'http://zyc.st.edusoho.cn'
        }
      },
      imgAdress: 'http://www.esdev.com/themes/jianmo/img/banner_net.jpg',
      parts: [{
        data:[],
      }]
    }
  },
  props: {
    active: {
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
    updateImg() {
      return this.imgAdress
    },
  },
  created() {
    this.addItem();
  },
  methods: {
    addItem() {
      this.parts[0].data.push(JSON.parse(JSON.stringify(this.defaultItem)));
    },
    selected(selected) {
      this.imgAdress = selected.imageUrl;
      this.activeItemIndex = selected.selectIndex;
    },
    handleRemove() {
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
  }
}
</script>
