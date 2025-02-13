<template>
  <el-dialog
    width="90%"
    :visible.sync="modalVisible"
    :before-close="beforeCloseHandler"
    :close-on-click-modal="false"
  >
    <div class="course-modal__header" slot="title">
      <span class="header__title">选择{{ typeText }}</span>
      <span class="header__subtitle"
        >仅显示{{ type === 'coupon' ? '未过期的' : '已发布'
        }}{{ typeText }}</span
      >
      <a
        v-if="['groupon', 'seckill', 'cut'].includes(type)"
        class="color-primary pull-right text-12 mrl"
        :href="`${createMarketingUrl}_${type}`"
        target="_blank"
        >创建活动</a
      >
    </div>
    <div class="course-modal__body">
      <div class="search__container flex items-center">
        <span class="search__label whitespace-nowrap">{{ typeText }}名称：</span>
        <el-input v-model="keyWord" :placeholder="`请输入${typeText}名称`" style="width: 360px"></el-input>
        <el-button class="ml-12" type="primary" @click="searchHandler">搜索</el-button>
      </div>
      <div v-if="limit > 1" class="help-text mbs">拖动{{ typeText }}名称可调整排序</div>
    </div>
    <el-table
      :data="courseSets"
      stripe
      style="width: 100%">
      <el-table-column
        prop="courseSetTitle"
        label="课程名称"
      >
      </el-table-column>
      <el-table-column
        prop="price"
        label="商品价格"
      >
      </el-table-column>
      <el-table-column
        prop="updatedTime"
        label="创建时间"
      >
      </el-table-column>
      <el-table-column
        label="操作"
      >
      </el-table-column>
    </el-table>
    <div slot="footer" class="course-modal__footer dialog-footer flex justify-center">
      <el-button
        class="text-14 btn-border-primary"
        size="small"
        @click="modalVisible = false"
        >取 消</el-button
      >
      <el-button
        class="text-14"
        type="primary"
        size="small"
        @click="saveHandler"
        >保 存</el-button
      >
    </div>
  </el-dialog>
</template>

<script>
import marketingMixins from 'admin/mixins/marketing';
import { mapActions } from 'vuex';
import {
  VALUE_DEFAULT,
  TYPE_TEXT_DEFAULT,
} from 'admin/config/module-default-config';

function apiConfig(type, queryString, offset, limit) {
  return {
    classroom_list: {
      apiName: 'getClassList',
      params: {
        title: queryString,
        offset: offset,
        limit: limit,
      },
    },
    course_list: {
      apiName: 'getCourseList',
      params: {
        courseSetTitle: queryString,
        offset: offset,
        limit: limit,
      },
    },
  };
}

export default {
  name: 'slideshow-link-modal',
  mixins: [marketingMixins],
  components: {
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
      default: 'course_list',
    },
  },
  data() {
    return {
      tableKey: 0,
      keyWord: '',
      courseSets: [],
      courseListIds: [],
      valueDefault: VALUE_DEFAULT,
      typeTextDefault: TYPE_TEXT_DEFAULT,
      hideLoading: false,
      pagination: {
        current: 1,
        pageSize: 10,
        total: 0,
      },
    };
  },
  computed: {
    modalVisible: {
      get() {
        return this.visible;
      },
      set(visible) {
        this.$emit('visibleChange', visible);
      },
    },
    valueKey() {
      return this.valueDefault[this.type].key;
    },
    typeText() {
      return this.typeTextDefault[this.type].text;
    },
  },
  watch: {
    visible(val) {
      if (!val) {
        return;
      }
      // 重置 table 数据，重置 table 生命周期
      // this.tableKey++;
      // this.courseSets = this.courseList;
      this.keyWord = '';
    },
  },
  created() {

  },
  mounted() {
    this.searchHandler();
  },
  methods: {
    ...mapActions([
      'getCourseList',
      'getClassList',
      'getMarketingList',
      'getCouponList',
      'getOpenCourseList',
    ]),
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
    searchHandler() {
      const apiConfigObj = apiConfig(this.type, this.keyWord, (this.pagination.current - 1) * this.pagination.pageSize, this.pagination.pageSize);
      this.hideLoading = false;
      this[apiConfigObj[this.type].apiName](apiConfigObj[this.type].params)
        .then(res => {
          this.courseSets = res;
        })
        .catch(err => {
          this.hideLoading = true;
          this.$message({
            message: err.message,
            type: 'error',
          });
        });
    },
  },
};
</script>
