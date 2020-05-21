<template>
  <module-frame :is-active="isActive" :is-incomplete="isIncomplete" container-class="setting-course">
    <div slot="preview" class="find-page__part">
      <e-course-list
        :course-list="copyModuleData.data"
        :feedback="false"
        :type-list="type"
        show-mode="admin"
        :uiStyle="uiStyle"
        @fetchCourse="fetchCourse"/>
    </div>
    <div slot="setting">
      <header class="title">
        {{ typeLabel }}列表设置
        <div v-if="portal === 'miniprogram' && typeLabel === '班级'" class="text-12 color-gray mts">使用班级配置功能，小程序版本需要升级到1.3.1及以上</div>
      </header>
      <div class="default-allocate__content clearfix">
        <!-- 列表名称 -->
        <setting-cell title="列表名称：" left-class="required-option">
          <el-input v-model="copyModuleData.data.title" size="mini" max-length="15" placeholder="请输入列表名称" clearable/>
        </setting-cell>

        <!-- 排列方式： -->
        <setting-cell v-if="portal!=='miniprogram'" title="排列方式：" >
          <el-select v-model="displayStyle" placeholder="排列方式" size="mini">
            <el-option v-for="item in layoutOptions" :key="item.value" :label="item.label" :value="item.value"/>
          </el-select>
        </setting-cell>

        <!-- 课程来源 -->
        <setting-cell :title="typeLabel + '来源：'">
          <el-radio v-model="sourceType" label="condition">{{ typeLabel }}分类</el-radio>
          <el-radio v-model="sourceType" label="custom">自定义</el-radio>
        </setting-cell>

        <!-- 课程分类 -->
        <setting-cell :title="typeLabel + '分类：'">
          <el-cascader v-show="sourceType === 'condition'" :options="type === 'course_list' ? courseCategories : classCategories" :props="cascaderProps" v-model="categoryTempId" size="mini" placeholder="请输入列表名称" filterable change-on-select/>
          <div v-show="sourceType === 'custom'" class="required-option">
            <el-button size="mini" @click="openModal">选择{{ typeLabel }}</el-button>
          </div>
        </setting-cell>

        <draggable v-show="sourceType === 'custom' && copyModuleData.data.items.length" v-model="copyModuleData.data.items" class="default-draggable__list">
          <div v-for="(courseItem, index) in copyModuleData.data.items" :key="index" class="default-draggable__item">
            <div class="default-draggable__title text-overflow">{{ courseItem.displayedTitle || courseItem.title }}</div>
            <i class="h5-icon h5-icon-cuowu1 default-draggable__icon-delete" @click="deleteCourse(index)"/>
          </div>
        </draggable>

        <!-- 排列顺序 -->
        <setting-cell v-show="sourceType === 'condition'" title="排列顺序：">
          <div class="section-right__item pull-left">
            <el-select v-model="sort" placeholder="顺序" size="mini">
              <el-option v-for="item in sortOptions" :key="item.value" :label="item.label" :value="item.value"/>
            </el-select>
          </div>
          <div v-show="showDateOptions" class="section-right__item pull-right">
            <el-select v-model="lastDays" placeholder="时间区间" size="mini">
              <el-option v-for="item in dateOptions" :key="item.value" :label="item.label" :value="item.value"/>
            </el-select>
          </div>
        </setting-cell>

        <!-- 显示个数 -->
        <setting-cell v-show="sourceType === 'condition'" title="显示个数：">
          <el-select v-model="limit" placeholder="请选择个数" size="mini">
            <el-option v-for="item in limitOptions" :key="item" :label="item" :value="item"/>
          </el-select>
        </setting-cell>
      </div>
    </div>
    <course-modal
      slot="modal"
      :visible="modalVisible"
      :limit="limit"
      :type="type"
      :course-list="copyModuleData.data.items"
      @visibleChange="modalVisibleHandler"
      @updateCourses="getUpdatedCourses"/>
  </module-frame>
</template>
<script>
import draggable from 'vuedraggable'
import courseList from '&/components/e-course-list/e-course-list'
import courseModal from './modal/course-modal'
import moduleFrame from '../module-frame'
import settingCell from '../module-frame/setting-cell'
import { mapMutations, mapState, mapActions } from 'vuex'
import treeDigger from 'admin/utils/tree-digger'
import pathName2Portal from 'admin/config/api-portal-config'

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
    settingCell
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
      modalVisible: false,
      limitOptions: [1, 2, 3, 4, 5, 6, 7, 8],
      type: this.moduleData.type,
      layoutOptions: [{
        value: 'row',
        label: '一行一列'
      }, {
        value: 'distichous',
        label: '一行两列'
      }],
      sortOptions: [{
        value: '-studentNum',
        label: '加入最多'
      }, {
        value: '-createdTime',
        label: '最近创建'
      }, {
        value: '-rating',
        label: '评分最高'
      }, {
        value: 'recommendedSeq',
        label: `推荐${optionLabel[this.moduleData.type]}`
      }],
      cascaderProps: {
        label: 'name',
        value: 'id'
      },
      pathName: this.$route.name,
      categoryTempId: this.moduleData.data.categoryIdArray || ['0'],
      categoryDiggered: false,
      dateOptions: [{
        value: '7',
        label: '最近7天'
      }, {
        value: '30',
        label: '最近30天'
      }, {
        value: '90',
        label: '最近90天'
      }, {
        value: '0',
        label: '历史所有'
      }]
    }
  },
  computed: {
    ...mapState(['courseCategories', 'classCategories']),
    typeLabel() {
      return optionLabel[this.type]
    },
    isActive: {
      get() {
        return this.active
      },
      set() {}
    },
    uiStyle:{
     get() {
        if (this.$route.name === 'miniprogramSetting'
        || this.$route.query.from === 'miniprogramSetting') {
          return 'old'
        }else{
          return "new"
        }
      },
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
    showDateOptions() {
      const isNewCreated = this.moduleData.data.sort === this.sortOptions[1].value
      const isRecommend = this.moduleData.data.sort === this.sortOptions[3].value

      if (isNewCreated || isRecommend) {
        // 如果是 最新创建 或 推荐课程 时间区间为所有
        this.moduleData.data.lastDays = '0'
      }
      return !isNewCreated && !isRecommend
    },
    sourceType: {
      get() {
        return this.copyModuleData.data.sourceType
      },
      set(value) {
        this.copyModuleData.data.sourceType = value
      }
    },
    sort: {
      get() {
        return this.copyModuleData.data.sort
      },
      set(value) {
        this.copyModuleData.data.sort = value
      }
    },
    displayStyle: {
      get() {
        return this.copyModuleData.data.displayStyle
      },
      set(value) {
        this.copyModuleData.data.displayStyle = value
      }
    },
    lastDays: {
      get() {
        return this.copyModuleData.data.lastDays.toString()
      },
      set(value) {
        this.copyModuleData.data.lastDays = value
      }
    },
    limit: {
      get() {
        return this.copyModuleData.data.limit
      },
      set(value) {
        this.copyModuleData.data.limit = value
      }
    },
    categoryId: {
      get() {
        return this.copyModuleData.data.categoryId
      },
      set(value) {
        this.copyModuleData.data.categoryId = value
      }
    },
    portal() {
      return pathName2Portal[this.pathName]
    }
  },
  watch: {
    copyModuleData: {
      handler(data) {
        this.$emit('updateModule', data)
      },
      deep: true
    },
    categoryTempId: {
      handler(value) {
        if (!value.length) {
          return
        }
        const endIndex = value.length - 1
        // 多级分类需要拿到最后等级的id
        this.moduleData.data.categoryIdArray = value
        this.moduleData.data.categoryId = value[endIndex]
      }
    },
    courseCategories: {
      handler(tree) {
        if (!tree || this.categoryDiggered) return

        const categoryExist = false
        treeDigger(tree, (children, id) => {
          if (id) {
            const categoryExist = (id == this.categoryTempId)
          }
          return children
        })
        this.categoryDiggered = true

        if (categoryExist) return
        // this.categoryTempId = ['0'];
      },
      immediate: true
    },
    classCategories: {
      handler(tree) {
        if (!tree || this.categoryDiggered) return

        const categoryExist = false
        treeDigger(tree, (children, id) => {
          if (id) {
            const categoryExist = (id == this.categoryTempId)
          }
          return children
        })
        this.categoryDiggered = true

        if (categoryExist) return
        this.categoryTempId = ['0']
      },
      immediate: true
    },
    sourceType(value) {
      this.limit = value === 'condition' ? 4 : 8
    }
  },
  methods: {
    ...mapActions(['getCourseList', 'getClassList']),
    getUpdatedCourses(courses) {
      this.copyModuleData.data.items = courses
    },
    modalVisibleHandler(visible) {
      this.modalVisible = visible
    },
    openModal() {
      this.modalVisible = true
    },
    // 删除自定义课程
    deleteCourse(index) {
      this.copyModuleData.data.items.splice(index, 1)
    },
    fetchCourse({ params, index }) {
      if (this.sourceType === 'custom') return
      if (this.type === 'course_list') {
        this.getCourseList(params).then(res => {
          this.moduleData.data.items = res.data
        }).catch((err) => {
          this.$message({
            message: err.message,
            type: 'error'
          })
        })
        return
      }
      this.getClassList(params).then(res => {
        this.moduleData.data.items = res.data
      }).catch((err) => {
        this.$message({
          message: err.message,
          type: 'error'
        })
      })
    }
  }
}
</script>
