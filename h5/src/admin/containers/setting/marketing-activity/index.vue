<template>
  <module-frame
    containerClass="setting-groupon"
    :isActive="active"
    :isIncomplete="incomplete"
  >
    <div slot="preview" class="groupon-container">
      <activity
        :activity="copyModuleData.activity"
        :tag="copyModuleData.tag"
        :showTitle="radio"
        :type="moduleData.type"
        :feedback="false"
      >
      </activity>
    </div>
    <div slot="setting" class="groupon-allocate">
      <e-suggest
        v-if="moduleData.tips"
        :suggest="moduleData.tips"
        :key="moduleData.moduleType"
      ></e-suggest>
      <header class="title">
        {{ activityTitle }}
        <div class="text-12 color-gray mts" v-if="portal === 'miniprogram'">
          营销活动配置即将发布，敬请期待...
        </div>
      </header>
      <div class="groupon-item-setting clearfix">
        <div class="groupon-item-setting__section clearfix">
          <!-- 标题栏 -->
          <setting-cell :title="$t('groupPurchase.title')" class="mbm">
            <el-radio v-model="radio" label="show">{{ $t('groupPurchase.display') }}</el-radio>
            <el-radio v-model="radio" label="unshow">{{ $t('groupPurchase.noDisplay') }}</el-radio>
          </setting-cell>
          <p class="pull-left section-left">{{ $t('groupPurchase.activity') }}</p>
          <div class="section-right">
            <div class="required-option">
              <el-button
                type="info"
                size="mini"
                @click="openModal"
                v-show="!activityName"
                >{{ $t('groupPurchase.selectActivity') }}</el-button
              >
              <el-tag
                class="courseLink"
                closable
                :disable-transitions="true"
                @close="handleClose"
                v-show="activityName"
              >
                <el-tooltip
                  class="text-content ellipsis"
                  effect="dark"
                  placement="top"
                >
                  <span slot="content">{{ activityName }}</span>
                  <span>{{ activityName }}</span>
                </el-tooltip>
              </el-tag>
            </div>
          </div>
        </div>
        <div class="groupon-item-setting__section clearfix">
          <p class="pull-left section-left">{{ $t('groupPurchase.activityLabel') }}</p>
          <div class="section-right pull-left">
            <el-input
              size="mini"
              v-model="copyModuleData.tag"
              maxLength="8"
              :placeholder="$t('groupPurchase.placeholder')"
              clearable
            ></el-input>
          </div>
        </div>
      </div>
    </div>
    <course-modal
      slot="modal"
      :visible="modalVisible"
      limit="1"
      :courseList="courseSets"
      @visibleChange="modalVisibleHandler"
      @updateCourses="getUpdatedCourses"
      :type="moduleData.type"
    >
    </course-modal>
  </module-frame>
</template>

<script>
import activity from '&/components/e-marketing/e-activity';
import moduleFrame from '../module-frame';
import courseModal from '../course/modal/course-modal';
import settingCell from '../module-frame/setting-cell';
import pathName2Portal from 'admin/config/api-portal-config';
import suggest from '&/components/e-suggest/e-suggest.vue';
export default {
  name: 'marketing-groupon',
  components: {
    moduleFrame,
    courseModal,
    activity,
    settingCell,
    'e-suggest': suggest,
  },
  props: {
    active: {
      type: Boolean,
      default: false,
    },
    moduleData: {
      type: Object,
    },
    incomplete: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      modalVisible: false,
      courseSets: [],
      pathName: this.$route.name,
    };
  },
  computed: {
    copyModuleData: {
      get() {
        return this.moduleData.data;
      },
      set() {},
    },
    activityName() {
      return this.moduleData.data.activity.name;
    },
    activityTitle() {
      const type = this.moduleData.type;
      if (type === 'seckill') return this.$t('groupPurchase.flashSaleSettings');
      if (type === 'cut') return this.$t('coupon.bargainSettings');
      return this.$t('groupPurchase.groupPurchaseSetting');
    },
    radio: {
      get() {
        return this.copyModuleData.titleShow;
      },
      set(value) {
        this.copyModuleData.titleShow = value;
      },
    },
    portal() {
      return pathName2Portal[this.pathName];
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
  },
};
</script>
