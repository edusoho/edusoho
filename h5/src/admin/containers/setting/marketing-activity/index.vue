<template>
  <module-frame :is-active="active" :is-incomplete="incomplete" container-class="setting-groupon">
    <div slot="preview" class="groupon-container">
      <activity
        :activity="copyModuleData.activity"
        :tag="copyModuleData.tag"
        :show-title="radio"
        :type="moduleData.type"
        :feedback="false"/>
    </div>
    <div slot="setting" class="groupon-allocate">
      <header class="title">{{ activityTitle }}
        <div v-if="portal === 'miniprogram'" class="text-12 color-gray mts">营销活动配置即将发布，敬请期待...</div>
      </header>
      <div class="groupon-item-setting clearfix">
        <div class="groupon-item-setting__section clearfix">
          <!-- 标题栏 -->
          <setting-cell title="标题栏：" class="mbm">
            <el-radio v-model="radio" label="show">显示</el-radio>
            <el-radio v-model="radio" label="unshow">不显示</el-radio>
          </setting-cell>
          <p class="pull-left section-left">活动：</p>
          <div class="section-right">
            <div class="required-option">
              <el-button v-show="!activityName" type="info" size="mini" @click="openModal">选择活动</el-button>
              <el-tag v-show="activityName" :disable-transitions="true" class="courseLink" closable @close="handleClose">
                <el-tooltip class="text-content ellipsis" effect="dark" placement="top">
                  <span slot="content">{{ activityName }}</span>
                  <span>{{ activityName }}</span>
                </el-tooltip>
              </el-tag>
            </div>
          </div>
        </div>
        <div class="groupon-item-setting__section clearfix">
          <p class="pull-left section-left">活动标签：</p>
          <div class="section-right pull-left">
            <el-input v-model="copyModuleData.tag" size="mini" max-length="8" placeholder="请输入活动名称" clearable/>
          </div>
        </div>
      </div>
    </div>
    <course-modal slot="modal" :visible="modalVisible" :course-list="courseSets" :type="moduleData.type" limit="1" @visibleChange="modalVisibleHandler" @updateCourses="getUpdatedCourses"/>
  </module-frame>
</template>

<script>
import activity from '&/components/e-marketing/e-activity'
import moduleFrame from '../module-frame'
import courseModal from '../course/modal/course-modal'
import settingCell from '../module-frame/setting-cell'
import pathName2Portal from 'admin/config/api-portal-config'

export default {
  name: 'MarketingGroupon',
  components: {
    moduleFrame,
    courseModal,
    activity,
    settingCell
  },
  props: {
    active: {
      type: Boolean,
      default: false
    },
    // eslint-disable-next-line vue/require-default-prop
    moduleData: {
      type: Object,
      dafault: () => {}
    },
    incomplete: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      modalVisible: false,
      courseSets: [],
      pathName: this.$route.name
    }
  },
  computed: {
    copyModuleData: {
      get() {
        return this.moduleData.data
      },
      set() {}
    },
    activityName() {
      return this.moduleData.data.activity.name
    },
    activityTitle() {
      const type = this.moduleData.type
      if (type === 'seckill') return '秒杀设置'
      if (type === 'cut') return '砍价设置'
      return '拼团设置'
    },
    radio: {
      get() {
        return this.copyModuleData.titleShow
      },
      set(value) {
        this.copyModuleData.titleShow = value
      }
    },
    portal() {
      return pathName2Portal[this.pathName]
    }
  },
  methods: {
    modalVisibleHandler(visible) {
      this.modalVisible = visible
    },
    openModal() {
      this.modalVisible = true
    },
    getUpdatedCourses(courses) {
      this.courseSets = courses
      if (!courses.length) return

      this.copyModuleData.activity = courses[0]
    },
    removeActivity() {
      this.courseSets = []
      this.$set(this.copyModuleData, 'activity', {})
    },
    handleClose() {
      this.removeActivity()
    }
  }
}
</script>
