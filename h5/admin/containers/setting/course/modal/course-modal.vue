<template>
  <el-dialog
    width="90%"
    :visible.sync="modalVisible"
    :before-close="beforeCloseHandler"
    :close-on-click-modal="false">
    <div class="course-modal__header" slot="title">
      <span class="header__title">选择课程</span>
      <span class="header__subtitle">仅显示已发布课程</span>
    </div>
    <div class="course-modal__body">
      <div class="search__container"">
        <span class="search__label"">选择课程：</span>
        <!-- 接口字段 courseSetTitle -->
        <el-input class="search__input" clearable
          size="medium" v-model="keyWord"
          placeholder="搜索课程"></el-input>
      </div>
    </div>
    <course-table :courseList="courseSets" @sort="getSortedCourses"></course-table>
    <span slot="footer" class="course-modal__footer dialog-footer">
      <el-button class="text-medium" size="small" @click="modalVisible = false">取 消</el-button>
      <el-button class="text-medium" type="primary" size="small" @click="saveHandler">保 存</el-button>
    </span>
  </el-dialog>
</template>

<script>
import courseTable from './course-table'

export default {
  name: 'course-modal',
  components: {
    courseTable,
  },
  props: {
    courseList: {
      type: Array,
      default: [],
    },
    visible: {
      type: Boolean,
      default: false,
    }
  },
  data () {
    return {
      keyWord: '',
      courseSets: this.courseList,
    }
  },
  computed: {
    modalVisible: {
      get() {
        return this.visible;
      },
      set(visible) {
        this.$emit('visibleChange', visible);
      }
    }
  },
  methods: {
    getSortedCourses(courses) {
      this.courseSets = courses;
    },
    beforeCloseHandler() {
      // todo

      this.modalVisible = false;
    },
    saveHandler() {
      this.$emit('sort', this.courseSets);
      this.modalVisible = false;
    }
  }
}
</script>
