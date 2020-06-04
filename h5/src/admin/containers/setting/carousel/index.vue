<template>
  <module-frame :is-active="isActive" :is-incomplete="isIncomplete" container-class="setting-carousel">
    <div slot="preview" :class="{ 'carousel-image-container__app' :(pathName === 'appSetting')}" class="carousel-image-container">
      <img
        v-show="carouselImage"
        :src="carouselImage"
        class="carousel-image">
      <div v-show="!carouselImage" :class="{ 'image-mask__app' :(pathName === 'appSetting')}" class="image-mask">
        轮播图
      </div>
      <div v-if="pathName !== 'appSetting'" class="carousel-title ellipsis">{{ carouselTitle }}</div>
    </div>

    <div slot="setting" class="carousel-allocate">
      <e-suggest v-if="moduleData.tips" :suggest="moduleData.tips" :key="moduleData.moduleType"></e-suggest>
      <header class="title">
        轮播图设置
        <div v-if="pathName !== 'appSetting'" class="help-text" >建议图片尺寸为750x400px，支持 jpg/png/gif 格式，大小不超过2MB</div>
        <div v-else class="help-text" >建议图片尺寸为750x300px，支持 jpg/png/gif 格式，大小不超过2MB</div>
      </header>
      <div v-for="(item, index) in copyModuleData.data" :key="index">
        <item
          :item="item"
          :index="index"
          :active="activeItemIndex"
          :item-num="itemNum"
          :course-sets="courseSets[index]"
          @selected="selected"
          @chooseCourse="openModal"
          @remove="itemRemove"
          @removeCourseLink="removeCourseLink"
          @setOutLink="setOutLink"/>
      </div>
      <el-button v-show="addBtnShow" class="btn-add-item" type="info" size="medium" @click="addItem">添加一个轮播图</el-button>
    </div>

    <course-modal
      slot="modal"
      :type="['course_list', 'classroom_list'].includes(type) ? type : 'course_list'"
      :visible="modalVisible"
      :course-list="courseSets[activeItemIndex] || []"
      limit="1"
      @visibleChange="modalVisibleHandler"
      @updateCourses="getUpdatedCourses"/>
  </module-frame>
</template>

<script>
import { MODULE_DEFAULT } from 'admin/config/module-default-config'
import item from './item'
import moduleFrame from '../module-frame'
import courseModal from '../course/modal/course-modal'
import suggest from "&/components/e-suggest/e-suggest.vue"

export default {
  components: {
    item,
    moduleFrame,
    courseModal,
    'e-suggest':suggest
  },
  props: {
    active: {
      type: Boolean,
      default: false
    },
    moduleData: {
      type: Object,
      default: () => {}
    },
    incomplete: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      activeItemIndex: 0,
      modalVisible: false,
      courseSets: [],
      type: 'course_list',
      pathName: this.$route.name
    }
  },
  computed: {
    isActive: {
      get() {
        return this.active
      },
      set() {}
    },
    isIncomplete: {
      get() {
        return this.incomplete
      },
      set() {}
    },
    copyModuleData: {
      get() {
        return this.moduleData
      },
      set() {
        console.log('changed copyModuleData')
      }
    },
    carouselImage() {
      return this.copyModuleData.data[this.activeItemIndex] &&
        this.copyModuleData.data[this.activeItemIndex].image.uri
    },
    carouselTitle() {
      return this.copyModuleData.data[this.activeItemIndex] &&
        this.copyModuleData.data[this.activeItemIndex].title
    },
    itemNum() {
      return this.copyModuleData.data.length
    },
    addBtnShow() {
      return this.itemNum < 5
    }
  },
  watch: {
    copyModuleData: {
      handler(data) {
        this.$emit('updateModule', data)
      },
      deep: true
    }
  },
  methods: {
    addItem() {
      // 需要深拷贝对象
      const itemString = JSON.stringify(MODULE_DEFAULT.slideShow.data[0])
      const itemObject = JSON.parse(itemString)
      this.copyModuleData.data.push(itemObject)
    },
    selected(selected) {
      this.activeItemIndex = selected.selectIndex
    },
    getUpdatedCourses(data) {
      const linkData = this.copyModuleData.data[this.activeItemIndex].link
      if (this.type === 'classroom_list') {
        this.courseSets[this.activeItemIndex] = [{
          id: data[0].id,
          // courseSetId: data[0].courseSet.id,
          title: data[0].title,
          displayedTitle: data[0].title
        }]
        linkData.type = 'classroom'
        return
      }
      this.courseSets[this.activeItemIndex] = [{
        id: data[0].id,
        courseSetId: data[0].courseSet.id,
        title: data[0].title || data[0].courseSetTitle,
        displayedTitle: data[0].displayedTitle
      }]
      linkData.type = 'course'
    },
    modalVisibleHandler(visible) {
      this.modalVisible = visible
    },
    openModal({ value, index }) {
      if (value !== 'vip') {
        this.modalVisible = true
      } else {
        this.copyModuleData.data[index].link.type = 'vip'
      }
      this.type = value
      this.activeItemIndex = index
    },
    itemRemove(index) {
      this.activeItemIndex = index - 1
      this.copyModuleData.data.splice(index, 1)
    },
    removeCourseLink(index) {
      this.type = ''
      this.copyModuleData.data[index].link.type = ''
      this.copyModuleData.data[index].link.target = null
      this.courseSets[this.activeItemIndex] = []
    },
    setOutLink(item = {}) {
      const index = item.index
      this.copyModuleData.data[index].link = { ...item }
    }
  }
}
</script>
