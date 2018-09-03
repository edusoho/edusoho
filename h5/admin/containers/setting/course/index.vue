<template>
  <module-frame containerClass="setting-course" :isActive="isActive">
    <div slot="preview" class="find-page__part">
      <e-course-list :courseList="copyModuleData.data" :limit="copyModuleData.data.limit" :feedback="false"></e-course-list>
    </div>
    <div slot="setting" class="course-allocate">
      <header class="title">课程列表设置</header>
      <div class="course-item-setting clearfix">
        <!-- 列表名称 -->
        <div class="course-item-setting__section clearfix">
          <p class="pull-left section-left required-option">列表名称：</p>
          <div class="section-right">
            <el-input size="mini" v-model="copyModuleData.data.title" placeholder="请输入列表名称" clearable></el-input>
          </div>
        </div>
        <!-- 课程来源 -->
        <div class="course-item-setting__section mtl clearfix">
          <p class="pull-left section-left required-option">课程来源：</p>
          <div class="section-right">
            <el-radio v-model="copyModuleData.data.sourceType" label="condition">课程分类</el-radio>
            <el-radio v-model="copyModuleData.data.sourceType" label="custom">自定义</el-radio>
          </div>
        </div>
        <!-- 课程分类 -->
        <div class="course-item-setting__section mtl clearfix">
          <p class="pull-left section-left required-option">课程分类：</p>
          <div class="section-right">
            <!-- <el-input size="mini" v-model="categoryId" placeholder="请输入列表名称"> -->
            <el-cascader v-show="copyModuleData.data.sourceType === 'condition'" size="mini" placeholder="请输入列表名称" :options="categories" :props="cascaderProps" v-model="categoryId" filterable change-on-select></el-cascader>
            </el-input>
            <div v-show="copyModuleData.data.sourceType === 'custom'">
              <el-button type="info" size="mini" @click="openModal">选择课程</el-button>
            </div>
          </div>
          <draggable v-show="copyModuleData.data.sourceType === 'custom' && copyModuleData.data.items.length" v-model="copyModuleData.data.items" class="section__course-container">
            <div class="section__course-item" v-for="(courseItem, index) in copyModuleData.data.items" :key="index">
              <div class="section__course-item__title text-overflow">{{ courseItem.courseSetTitle }}</div>
              <i class="h5-icon h5-icon-cuowu1 section__course-item__icon-delete" @click="deleteCourse(index)"></i>
            </div>
          </draggable>
        </div>
        <!-- 排列顺序 -->
        <div class="course-item-setting__section mtl clearfix"
          v-show="copyModuleData.data.sourceType === 'condition'">
          <p class="pull-left section-left required-option">排列顺序：</p>
          <div class="section-right">
            <div class="section-right__item pull-left">
              <el-select v-model="copyModuleData.data.sort" placeholder="顺序" size="mini">
                <el-option v-for="item in sortOptions" :key="item.value" :label="item.label" :value="item.value">
                </el-option>
              </el-select>
            </div>
            <div class="section-right__item pull-right" v-show="showDateOptions">
              <el-select v-model="copyModuleData.data.lastDays.toString()" placeholder="时间区间" size="mini">
                <el-option v-for="item in dateOptions" :key="item.value" :label="item.label" :value="item.value">
                </el-option>
              </el-select>
            </div>
          </div>
        </div>
        <!-- 显示个数 -->
        <div class="course-item-setting__section mtl clearfix">
          <p class="pull-left section-left required-option">显示个数：</p>
          <div class="section-right">
            <el-select v-model="copyModuleData.data.limit" placeholder="请选择个数" size="mini">
              <el-option v-for="item in [1,2,3,4,5,6,7,8]" :key="item" :label="item" :value="item">
              </el-option>
            </el-select>
          </div>
        </div>
      </div>
    </div>
    <course-modal slot="modal"
      :visible="modalVisible"
      :limit="copyModuleData.data.limit"
      :courseList="copyModuleData.data.items"
      @visibleChange="modalVisibleHandler"
      @updateCourses="getUpdatedCourses"></course-modal>
  </module-frame>
</template>
<script>
import draggable from 'vuedraggable';
import courseList from '@/containers/components/e-course-list/e-course-list';
import courseModal from './modal/course-modal'
import moduleFrame from '../module-frame'
import { mapMutations, mapState, mapActions } from 'vuex';

export default {
  components: {
    'e-course-list': courseList,
    draggable,
    courseModal,
    moduleFrame,
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
  data() {
    return {
      modalVisible: false,
      limitOptions: [1, 2, 3, 4, 5, 6, 7, 8],
      sortOptions: [{
        value: '-studentNum',
        label: '加入最多'
      }, {
        value: '-createdTime',
        label: '最近创建'
      }, {
        value: '-rating',
        label: '评价最高',
      }, {
        value: 'recommendedSeq',
        label: '推荐课程',
      }],
      cascaderProps: {
        label: 'name',
        value: 'id',
      },
      categoryId: [this.moduleData.data.categoryId.toString()],
      date: '最近7天',
      dateOptions: [{
        value: '7',
        label: '最近7天',
      }, {
        value: '30',
        label: '最近30天',
      }, {
        value: '90',
        label: '最近90天',
      }, {
        value: '0',
        label: '历史所有',
      }],
    }
  },
  computed: {
    ...mapState(['categories']),
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
    showDateOptions() {
      const isNewCreated = this.moduleData.data.sort === this.sortOptions[1].value
      const isRecommend = this.moduleData.data.sort === this.sortOptions[3].value;

      if (isNewCreated || isRecommend) {
        // 如果是 最新创建 或 推荐课程 时间区间为所有
        this.moduleData.data.lastDays = '0';
      }
      return !isNewCreated && !isRecommend;
    }
  },
  watch: {
    copyModuleData: {
      handler(data) {
        this.$emit('updateModule', data);
      },
      deep: true,
    },
    categoryId: {
      handler(value) {
        if (!value.length) {
          return;
        }
        this.moduleData.data.categoryId = value[0];
      },
    }
  },
  methods: {
    getUpdatedCourses(courses) {
      this.copyModuleData.data.items = courses;
    },
    modalVisibleHandler(visible) {
      this.modalVisible = visible;
    },
    openModal() {
      this.modalVisible = true;
    },
    // 删除自定义课程
    deleteCourse(index) {
      this.copyModuleData.data.items.splice(index, 1);
    }
  }
}
</script>
