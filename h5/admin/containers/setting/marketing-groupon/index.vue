<template>
  <module-frame containerClass="setting-groupon" :isActive="active" :isIncomplete="incomplete">
    <div slot="preview" class="groupon-container">
      <groupon :activity="copyModuleData.activity" :tag="copyModuleData.tag" :showTitle="radio" :type="moduleData.type"></groupon>
    </div>
    <div slot="setting" class="groupon-allocate">
      <header class="title">{{ activityTitle }}</header>
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
              <el-button type="info" size="mini" @click="openModal" v-show="!activityName">选择活动</el-button>
              <el-tag class="courseLink" closable :disable-transitions="true" @close="handleClose" v-show="activityName">
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
          <div class="section-right">
            <el-input size="mini" v-model="copyModuleData.tag" maxLength="8" placeholder="请输入活动名称" clearable></el-input>
          </div>
        </div>
      </div>
    </div>
    <course-modal slot="modal" :visible="modalVisible" limit=1 :courseList="courseSets" @visibleChange="modalVisibleHandler" @updateCourses="getUpdatedCourses" :type="moduleData.type">
    </course-modal>
  </module-frame>
</template>

<script>
import groupon from '@/containers/components/e-marketing/e-groupon';
import moduleFrame from '../module-frame';
import courseModal from '../course/modal/course-modal';
import settingCell from '../module-frame/setting-cell';

export default {
  name: 'marketing-groupon',
  components: {
    moduleFrame,
    courseModal,
    groupon,
    settingCell
  },
  props: {
    active: {
      type: Boolean,
      default: false,
    },
    moduleData: {
      type: Object
    },
    incomplete: {
      type: Boolean,
      default: false,
    }
  },
  data () {
    return {
      modalVisible: false,
      courseSets: [],
    }
  },
  computed: {
    copyModuleData: {
      get() {
        return this.moduleData.data;
      },
      set() {}
    },
    activityName() {
      return this.moduleData.data.activity.name;
    },
    activityTitle() {
      const type = this.moduleData.type;
      if (type === 'seckill') return '秒杀';
      if (type === 'cut') return '帮砍价';
      return '活动设置';
    },
    radio: {
      get() {
        return this.copyModuleData.titleShow;
      },
      set(value) {
        this.copyModuleData.titleShow = value;
      }
    },
  },
  methods: {
    modalVisibleHandler(visible) {
      this.modalVisible = visible;
    },
    openModal() {
      this.modalVisible = true;
    },
    getUpdatedCourses(courses) {
      this.courseSets = courses;
      if (!courses.length) return;

      this.copyModuleData.activity = courses[0];
    },
    removeActivity() {
      this.courseSets = [];
      this.$set(this.copyModuleData, 'activity', {});
    },
    handleClose() {
      this.removeActivity();
    },
  }
}
</script>
