<template>
  <el-dialog
    width="90%"
    :visible.sync="modalVisible"
    :before-close="beforeCloseHandler"
    :close-on-click-modal="false">
    <div class="course-modal__header" slot="title">
      <span class="header__title">选择{{typeText}}</span>
      <span class="header__subtitle">仅显示{{type === 'coupon' ? '未过期的' : '已发布'}}{{typeText}}</span>
      <a v-if="type === 'groupon'" class="color-primary pull-right text-12 mrl" :href="createMarketingUrl" target="_blank">创建拼团活动</a>
    </div>
    <div class="course-modal__body">
      <div class="search__container">
        <span class="search__label">选择{{typeText}}：</span>

        <!-- 接口字段 courseSetTitle -->
        <el-autocomplete
          size="medium"
          v-model="keyWord"
          :placeholder="`搜索${typeText}`"
          class="inline-input search__input"
          :value-key="valueKey"
          :clearable="true"
          :autofocus="true"
          :hide-loading="hideLoading"
          :trigger-on-focus="false"
          :fetch-suggestions="searchHandler"
          @select="selectHandler"
        ></el-autocomplete>
      </div>
      <div class="help-text mbs">拖动{{ typeText }}名称可调整排序</div>
    </div>
    <course-table :key="tableKey" :courseList="courseSets" @updateCourses="getUpdatedCourses" :type="type"></course-table>
    <span slot="footer" class="course-modal__footer dialog-footer">
      <el-button class="text-14 btn-border-primary" size="small" @click="modalVisible = false">取 消</el-button>
      <el-button class="text-14" type="primary" size="small" @click="saveHandler">保 存</el-button>
    </span>
  </el-dialog>
</template>

<script>
import marketingMixins from '@admin/mixins/marketing';
import head from '@admin/config/modal-config';
import courseTable from './course-table';
import { mapMutations, mapState, mapActions } from 'vuex';
import { VALUE_DEFAULT, TYPE_TEXT_DEFAULT } from '@admin/config/module-default-config';

function apiConfig(type, queryString) {
  return {
    'classroom_list': {
      apiName: 'getClassList',
      params: {
        title: queryString
      }
    },
    'course_list': {
      apiName: 'getCourseList',
      params: {
        courseSetTitle: queryString
      }
    },
    'groupon': {
      apiName: 'getMarketingList',
      params: {
        name: queryString,
        statuses: 'ongoing,unstart',
        type: type,
        itemType: 'course'
      }
    },
    'coupon': {
      apiName: 'getCouponList',
      params: {
        name: queryString,
        unexpired: 1,
        unreceivedNumGt: 0
      }
    },
    'cut': {
      apiName: 'getMarketingList',
      params: {
        name: queryString,
        statuses: 'ongoing,unstart',
        type: type
      }
    },
    'seckill': {
      apiName: 'getMarketingList',
      params: {
        name: queryString,
        statuses: 'ongoing,unstart',
        type: type
      }
    }
  }
}

export default {
  name: 'course-modal',
  mixins: [marketingMixins],
  components: {
    courseTable,
  },
  props: {
    courseList: {
      type: Array,
      default: () => {
        return [];
      },
    },
    visible: {
      type: Boolean,
      default: false,
    },
    limit: {
      default: '',
    },
    type: {
      type: String,
      default: 'course_list'
    }
  },
  data () {
    return {
      tableKey: 0,
      keyWord: '',
      courseSets: this.courseList,
      courseListIds: [],
      head,
      valueDefault: VALUE_DEFAULT,
      typeTextDefault: TYPE_TEXT_DEFAULT,
      hideLoading: false
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
    },
    valueKey() {
      return this.valueDefault[this.type].key;
    },
    typeText() {
      return this.typeTextDefault[this.type].text;
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
    }
  },
  created() {
    this.restoreListIds();
  },
  methods: {
    ...mapActions([
      'getCourseList',
      'getClassList',
      'getMarketingList',
      'getCouponList'
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
      this.modalVisible = false;
      if (!this.courseSets.length) {
        return;
      }
      this.$emit('updateCourses', this.courseSets);
    },
    selectHandler(item) {
      const exccedLimit = this.courseSets.length >= window.parseInt(this.limit, 10);

      if (exccedLimit) {
        this.$message({
          message: `当前最多可选 ${this.limit} 个${ this.typeText }`,
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
      const apiConfigObj = apiConfig(this.type, queryString);
      this.hideLoading = false;
      this[apiConfigObj[this.type].apiName](
        apiConfigObj[this.type].params
      ).then(res => {
        cb(res.data);
      }).catch((err) => {
        this.hideLoading = true;
        this.$message({
          message: err.message,
          type: 'error'
        });
      });
      return;
    }
  }
}
</script>
