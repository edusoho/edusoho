<template>
  <el-dialog
    width="60%"
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
        <el-input v-model="keyWord" :placeholder="`请输入${typeText}名称`" style="width: 250px"></el-input>
        <el-button class="ml-12" type="primary" @click="searchHandler">搜索</el-button>
      </div>
    </div>
    <div class="relative">
      <el-table
        :data="courseSets"
        v-loading="loading"
        element-loading-spinner="el-icon-loading"
        element-loading-text="拼命加载中"
        style="width: 100%"
        class="mb-20"
      >
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
          label="创建时间"
        >
          <template slot-scope="scope">
            <div>{{ formatTime(new Date(scope.row.createdTime)) }}</div>
          </template>
        </el-table-column>
        <el-table-column
          label="操作"
        >
          <template slot-scope="scope">
            <el-button type="text" @click="selectLink(scope.row)">选择</el-button>
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
    <div class="text-14 text-[#313131]">已选{{ typeText }}：{{ selectedName }}</div>
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
import { formatTime } from '@/utils/date-toolkit';

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
      loading: false,
      pagination: {
        current: 1,
        pageSize: 5,
        total: 0,
      },
      selectedName: '',
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
    formatTime,
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
      this.loading = true;
      this[apiConfigObj[this.type].apiName](apiConfigObj[this.type].params)
        .then(res => {
          this.courseSets = res.data;
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
    handleCurrentChange(val) {
      this.pagination.current = val;
      this.searchHandler();
    },
    selectLink(row) {
      this.selectedName = row.courseSetTitle;
    },
  },

};
</script>
