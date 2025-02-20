<template>
  <el-dialog
    width="80%"
    :visible.sync="modalVisible"
    :before-close="beforeCloseHandler"
    custom-class="slideshow-link-modal"
    :modal-append-to-body="false"
    :close-on-click-modal="false"
    :close-on-press-escape="false"
    top="2vh"
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
    <div slot="footer" class="flex justify-center">
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
    <div class="course-modal__body">
      <div class="search__container flex items-center">
        <span class="search__label whitespace-nowrap">{{ typeText }}名称：</span>
        <el-input v-model="keyWord" :placeholder="`请输入${typeText}名称`" style="width: 250px; margin-right: 12px" size="small"></el-input>
        <el-button type="primary" @click="searchLink" size="small">搜索</el-button>
      </div>
    </div>
    <div class="relative">
      <el-table
        :data="courseSetList"
        v-loading="loading"
        element-loading-spinner="el-icon-loading"
        element-loading-text="拼命加载中"
        style="width: 100%"
        class="mb-20"
      >
        <el-table-column
          :label="nameColumn.label"
        >
          <template slot-scope="scope">
            <div class="text-nowrap truncate">{{ type === 'course_list' ? scope.row.courseSetTitle : scope.row.title }}</div>
          </template>
        </el-table-column>
        <el-table-column
          prop="price"
          label="商品价格"
        >
        </el-table-column>
        <el-table-column
          v-if="type === 'classroom_list'"
          prop="courseNum"
          label="课程数量"
        >
        </el-table-column>
        <el-table-column
          label="创建时间"
        >
          <template slot-scope="scope">
            <div class="text-nowrap">{{ formatTime(new Date(scope.row.createdTime)) }}</div>
          </template>
        </el-table-column>
        <el-table-column
          label="操作"
        >
          <template slot-scope="scope">
            <el-button type="text" size="small" @click="selectLink(scope.row)">选择</el-button>
          </template>
        </el-table-column>
      </el-table>
    </div>
    <div class="w-full flex flex-row-reverse">
      <el-pagination
        @current-change="handleCurrentChange"
        :page-size="pagination.pageSize"
        layout="total, prev, pager, next, jumper"
        :total="pagination.total">
      </el-pagination>
    </div>
    <div class="flex items-center mt-20">
      <div class="text-14 text-[#313131] h-40 mr-12" style="line-height: 40px">已选{{ typeText }}：
        <span v-if="selectedCourseSet && type === 'course_list'">{{ selectedCourseSet.courseSetTitle }}</span>
        <span v-if="selectedCourseSet && type === 'classroom_list'">{{ selectedCourseSet.title }}</span>
      </div>
      <el-button v-if="selectedCourseSet" type="text" @click="clearLink">清除</el-button>
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
import { formatTime } from '@/utils/date-toolkit';

function apiConfig(queryString, offset, limit) {
  return {
    classroom_list: {
      apiName: 'getClassList',
      params: {
        title: queryString,
        offset: offset,
        limit: limit,
        sort: 'createdTime',
      },
    },
    course_list: {
      apiName: 'getCourseList',
      params: {
        courseSetTitle: queryString,
        offset: offset,
        limit: limit,
        sort: 'createdTime',
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
    type: {
      type: String,
      default: 'course_list',
    },
  },
  data() {
    return {
      tableKey: 0,
      keyWord: '',
      courseSetList: [],
      selectedCourseSet: null,
      valueDefault: VALUE_DEFAULT,
      typeTextDefault: TYPE_TEXT_DEFAULT,
      loading: false,
      pagination: {
        current: 1,
        pageSize: 5,
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
    nameColumn() {
      return this.type === 'course_list' ? { label: '课程名称' } : { label: '班级名称' };
    }
  },
  watch: {
    visible(val) {
      if (!val) {
        return;
      }
      this.keyWord = '';
      this.searchHandler();
      this.selectedCourseSet = this.courseList[0];
    },
  },
  methods: {
    formatTime,
    ...mapActions([
      'getCourseList',
      'getClassList',
    ]),
    beforeCloseHandler() {
      // todo
      this.modalVisible = false;
    },
    saveHandler() {
      this.modalVisible = false;
      if (!this.selectedCourseSet) {
        return;
      }
      const courseSets = [this.selectedCourseSet];
      this.$emit('updateCourses', courseSets);
    },
    searchHandler() {
      const apiConfigObj = apiConfig(this.keyWord, (this.pagination.current - 1) * this.pagination.pageSize, this.pagination.pageSize);
      this.loading = true;
      this[apiConfigObj[this.type].apiName](apiConfigObj[this.type].params)
        .then(res => {
          this.courseSetList = res.data;
          this.pagination.total = res.paging.total;
        })
        .catch(err => {
          this.$message({
            message: err.message,
            type: 'error',
          });
        }).finally(() => {
          this.loading = false;
      });
    },
    searchLink() {
      this.pagination.current = 1;
      this.searchHandler();
    },
    handleCurrentChange(val) {
      this.pagination.current = val;
      this.searchHandler();
    },
    selectLink(row) {
      this.selectedCourseSet = row;
    },
    clearLink() {
      this.selectedCourseSet = null;
    },
  },
};
</script>

<style>
  .slideshow-link-modal {
    .el-dialog__header {
      padding-bottom: 0;
    }
    .el-dialog__body {
      padding-top: 0;
      padding-bottom: 0;
    }
    .el-dialog__footer {
      padding: 0 0 10px 0;
    }
  }
  .el-overlay {
    pointer-events: all !important;
  }
  .el-overlay + div {
    pointer-events: none !important;
  }

</style>
