<template>
  <module-frame containerClass="setting-course" :isActive="isActive" :isIncomplete="isIncomplete">
    <div slot="preview" class="find-page__part">
      <e-course-list
      	:courseList="copyModuleData.data"
      	:feedback="false"
      	:typeList="type"
      	@fetchCourse="fetchCourse">
      </e-course-list>
    </div>
    <div slot="setting" class="course-allocate">
      <header class="title">{{typeLabel}}列表设置</header>
      <div class="course-item-setting clearfix">
        <!-- 列表名称 -->
        <div class="course-item-setting__section clearfix">
          <p class="pull-left section-left required-option">列表名称：</p>
          <div class="section-right">
            <el-input size="mini" v-model="copyModuleData.data.title" maxLength="15" placeholder="请输入列表名称" clearable></el-input>
          </div>
        </div>
        <!-- 课程来源 -->
        <div class="course-item-setting__section mtl clearfix">
          <p class="pull-left section-left">{{typeLabel}}来源：</p>
          <div class="section-right">
            <el-radio v-model="sourceType" label="condition">{{typeLabel}}名称</el-radio>
            <el-radio v-model="sourceType" label="custom">自定义</el-radio>
          </div>
        </div>
        <!-- 课程分类 -->
        <div class="course-item-setting__section mtl clearfix">
          <p class="pull-left section-left">{{typeLabel}}分类：</p>
          <div class="section-right">
            <el-cascader v-show="sourceType === 'condition'" size="mini" placeholder="请输入列表名称" :options="this.type === 'course_list' ? courseCategories : classCategories" :props="cascaderProps" v-model="categoryTempId" filterable change-on-select></el-cascader>
            </el-input>
            <div class="required-option" v-show="sourceType === 'custom'">
              <el-button size="mini" @click="openModal">选择{{typeLabel}}</el-button>
            </div>
          </div>
          <draggable v-show="sourceType === 'custom' && copyModuleData.data.items.length" v-model="copyModuleData.data.items" class="section__course-container">
            <div class="section__course-item" v-for="(courseItem, index) in copyModuleData.data.items" :key="index">
              <div class="section__course-item__title text-overflow">{{ courseItem.displayedTitle || courseItem.title }}</div>
              <i class="h5-icon h5-icon-cuowu1 section__course-item__icon-delete" @click="deleteCourse(index)"></i>
            </div>
          </draggable>
        </div>
        <!-- 排列顺序 -->
        <div class="course-item-setting__section mtl clearfix"
          v-show="sourceType === 'condition'">
          <p class="pull-left section-left">排列顺序：</p>
          <div class="section-right">
            <div class="section-right__item pull-left">
              <el-select v-model="sort" placeholder="顺序" size="mini">
                <el-option v-for="item in sortOptions" :key="item.value" :label="item.label" :value="item.value">
                </el-option>
              </el-select>
            </div>
            <div class="section-right__item pull-right" v-show="showDateOptions">
              <el-select v-model="lastDays" placeholder="时间区间" size="mini">
                <el-option v-for="item in dateOptions" :key="item.value" :label="item.label" :value="item.value">
                </el-option>
              </el-select>
            </div>
          </div>
        </div>
        <!-- 显示个数 -->
        <div v-show="sourceType === 'condition'" class="course-item-setting__section mtl clearfix">
          <p class="pull-left section-left">显示个数：</p>
          <div class="section-right">
            <el-select v-model="limit" placeholder="请选择个数" size="mini">
              <el-option v-for="item in [1,2,3,4,5,6,7,8]" :key="item" :label="item" :value="item">
              </el-option>
            </el-select>
          </div>
        </div>
      </div>
    </div>
    <course-modal slot="modal"
      :visible="modalVisible"
      :limit="limit"
      :type="type"
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
import treeDigger from '@admin/utils/tree-digger';

const optionLabel = {
  'course_list': '课程',
  'classroom_list': '班级'
}

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
    incomplete: {
      type: Boolean,
      default: false,
    }
  },
  data() {
    return {
      modalVisible: false,
      limitOptions: [1, 2, 3, 4, 5, 6, 7, 8],
      type: this.moduleData.type,
      sortOptions: [{
        value: '-studentNum',
        label: '加入最多'
      }, {
        value: '-createdTime',
        label: '最近创建'
      }, {
        value: '-rating',
        label: '评分最高',
      }, {
        value: 'recommendedSeq',
        label: `推荐${optionLabel[this.moduleData.type]}`,
      }],
      cascaderProps: {
        label: 'name',
        value: 'id',
      },
      categoryTempId: [this.moduleData.data.categoryId.toString()],
      categoryDiggered: false,
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
      }]
    }
  },
  computed: {
    ...mapState(['courseCategories', 'classCategories']),
    typeLabel() {
      return optionLabel[this.type];
    },
    isActive: {
      get() {
        return this.active;
      },
      set() {}
    },
    isIncomplete: {
      get() {
        return this.incomplete;
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
    },
    sourceType: {
      get() {
        return this.copyModuleData.data.sourceType;
      },
      set(value) {
        this.copyModuleData.data.sourceType = value;
      },
    },
    sort: {
      get() {
        return this.copyModuleData.data.sort;
      },
      set(value) {
        this.copyModuleData.data.sort = value;
      },
    },
    lastDays: {
      get() {
        return this.copyModuleData.data.lastDays.toString();
      },
      set(value) {
        this.copyModuleData.data.lastDays = value;
      },
    },
    limit: {
      get() {
        return this.copyModuleData.data.limit;
      },
      set(value) {
        this.copyModuleData.data.limit = value;
      },
    },
    categoryId: {
      get() {
        return this.copyModuleData.data.categoryId;
      },
      set(value) {
        this.copyModuleData.data.categoryId = value;
      },
    }
  },
  watch: {
    copyModuleData: {
      handler(data) {
        this.$emit('updateModule', data);
      },
      deep: true,
    },
    categoryTempId: {
      handler(value) {
        if (!value.length) {
          return;
        }
        const endIndex = value.length - 1
        this.moduleData.data.categoryId = value[endIndex];
      },
    },
    courseCategories: {
      handler(tree) {
        if (!tree || this.categoryDiggered) return;

        const categoryExist = false;
        treeDigger(tree, (children, id) => {
          if (id) {
            const categoryExist = (id == this.categoryTempId);
          }
          return children;
        })
        this.categoryDiggered = true;

        if (categoryExist) return;
        this.categoryTempId = ['0'];
      },
      immediate: true,
    },
    classCategories: {
      handler(tree) {
        if (!tree || this.categoryDiggered) return;

        const categoryExist = false;
        treeDigger(tree, (children, id) => {
          if (id) {
            const categoryExist = (id == this.categoryTempId);
          }
          return children;
        })
        this.categoryDiggered = true;

        if (categoryExist) return;
        this.categoryTempId = ['0'];
      },
      immediate: true,
    },
    sourceType(value) {
      this.limit = value === 'condition' ? 4 : 8;
    }
  },
  methods: {
    ...mapActions(['getCourseList', 'getClassList']),
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
    },
    fetchCourse({params, index}) {
    	if (this.sourceType === 'custom') return;
    	if (this.type === 'course_list') {
	      this.getCourseList(params).then(res => {
	        this.moduleData.data.items = res.data;
	      })
	      return;
      }
    	this.getClassList(params).then(res => {
        this.moduleData.data.items = res.data;
      })
    }
  }
}
</script>
