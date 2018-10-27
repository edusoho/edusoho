<template>
  <module-frame containerClass="setting-groupon" :isActive="active" :isIncomplete="incomplete">
    <div slot="preview" class="groupon-container">
      <groupon :activity="copyModuleData.activity" :tag="copyModuleData.tag"></groupon>
    </div>
    <div slot="setting" class="groupon-allocate">
      <header class="title">活动设置</header>
      <div class="groupon-item-setting clearfix">
        <div class="groupon-item-setting__section clearfix">
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
            <el-input size="mini" v-model="copyModuleData.tag" placeholder="请输入列表名称" clearable></el-input>
          </div>
        </div>
      </div>
    </div>
    <course-modal slot="modal" :visible="modalVisible" limit=1 :courseList="courseSets" @visibleChange="modalVisibleHandler" @updateCourses="getUpdatedCourses" type="groupon">
    </course-modal>
  </module-frame>
</template>

<script>
import groupon from '@/containers/components/e-marketing/e-groupon';
import moduleFrame from '../module-frame';
import courseModal from '../course/modal/course-modal';

export default {
  name: 'marketing-groupon',
  components: {
    moduleFrame,
    courseModal,
    groupon,
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
