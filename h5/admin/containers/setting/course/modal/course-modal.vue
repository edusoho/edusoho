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
      <div class="search__container">
        <span class="search__label">选择课程：</span>

        <!-- 接口字段 courseSetTitle -->
        <el-autocomplete
          size="medium"
          v-model="keyWord"
          placeholder="搜索课程"
          class="inline-input search__input"
          :value-key="'displayedTitle'"
          :clearable="true"
          :autofocus="true"
          :trigger-on-focus="false"
          :fetch-suggestions="searchHandler"
          @select="selectHandler"
        ></el-autocomplete>
      </div>
      <div class="help-text mbs">拖动课程名称可调整排序</div>
    </div>
    <course-table :key="tableKey" :courseList="courseSets" @updateCourses="getUpdatedCourses"></course-table>
    <span slot="footer" class="course-modal__footer dialog-footer">
      <el-button class="text-medium btn-border-primary" size="small" @click="modalVisible = false">取 消</el-button>
      <el-button class="text-medium" type="primary" size="small" @click="saveHandler">保 存</el-button>
    </span>
  </el-dialog>
</template>

<script>
import courseTable from './course-table'
import { mapMutations, mapState, mapActions } from 'vuex';

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
    },
    limit: {
      default: '',
    },
  },
  data () {
    return {
      tableKey: 0,
      keyWord: '',
      cacheResult: {},
      courseSets: this.courseList,
      courseListIds: [],
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
  watch: {
    visible(val) {
      if (!val) {
        return;
      }
      // 重置 table 数据，重置 table 生命周期
      this.tableKey ++;
      this.courseSets = this.courseList;
      this.restoreListIds();

      this.keyWord = '';
    },
  },
  created() {
    this.restoreListIds();
  },
  methods: {
    ...mapActions([
      'getCourseList'
    ]),
    restoreListIds() {
      this.courseListIds = [];
      for (let i = 0; i < this.courseSets.length; i++) {
        this.courseListIds.push(this.courseSets[i].id);
      }
    },
    getUpdatedCourses(courses) {
      this.courseSets = courses;
      this.restoreListIds();
    },
    beforeCloseHandler() {
      // todo

      this.modalVisible = false;
    },
    saveHandler() {
      this.$emit('updateCourses', this.courseSets);
      this.modalVisible = false;
    },
    selectHandler(item) {
      const exccedLimit = this.courseSets.length >= window.parseInt(this.limit, 10);

      if (exccedLimit) {
        this.$message({
          message: `当前最多可选 ${this.limit} 个课程`,
          type: 'warning'
        });
        return;
      }
      if (this.courseListIds.includes(item.id)) {
        this.$message({
          message: '重复添加了哦',
          type: 'warning'
        });
        return;
      }
      this.courseListIds.push(item.id)
      // 不使用push 操作, 避免改变props在父组件中的引用，导致父页面数据更新
      this.courseSets = [...this.courseSets, item];
    },
    searchHandler(queryString, cb) {
      if (this.cacheResult[queryString]) {
        cb(this.cacheResult[queryString])
        return;
      }
      this.getCourseList({
        courseSetTitle: queryString
      }).then(res => {
        this.cacheResult[queryString] = res.data;
        cb(res.data);
      })
    }
  }
}
</script>
