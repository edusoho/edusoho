<template>
  <layout>
    <template #title>
      <div class="clearfix">
        <div class="pull-left">xxx / {{ liveData.task.title }}</div>
        <div class="pull-right tips">数据来源于直播间，自动在直播结束一天同步</div>
      </div>
    </template>

    <a-row :gutter="16">
      <a-col :span="6">
        <div class="live-data">
          <div class="live-data__value text-overflow">{{ liveData.teacher }}</div>
          <div class="live-data__label">主讲人</div>
        </div>
      </a-col>

      <a-col :span="12">
        <div class="live-data">
          <div class="live-data__value">{{ $dateFormat(liveData.startTime, 'YYYY-MM-DD HH:mm') }} 至 {{ $dateFormat(liveData.endTime, 'YYYY-MM-DD HH:mm') }}</div>
          <div class="live-data__label">直播时间</div>
        </div>
      </a-col>

      <a-col :span="6">
        <div class="live-data">
          <div class="live-data__value">{{ liveData.length }}分钟</div>
          <div class="live-data__label">实际直播时长</div>
        </div>
      </a-col>
    </a-row>

    <a-row :gutter="16" class="mt16">
      <a-col :span="6">
        <div class="live-data">
          <div class="live-data__value">{{ liveData.maxOnlineNumber }}人</div>
          <div class="live-data__label">同时在线人数</div>
        </div>
      </a-col>

      <a-col :span="6">
        <div class="live-data">
          <div class="live-data__value">{{ liveData.memberNumber }}人</div>
          <div class="live-data__label">观看人数</div>
        </div>
      </a-col>

      <a-col :span="6">
        <div class="live-data">
          <div class="live-data__value">{{ liveData.chatNumber }}条</div>
          <div class="live-data__label">所有用户聊天数</div>
        </div>
      </a-col>

      <a-col :span="6">
        <div class="live-data">
          <div class="live-data__value">{{ liveData.checkinNum }}分钟</div>
          <div class="live-data__label">人均观看时长</div>
        </div>
      </a-col>
    </a-row>

    <a-tabs class="mt16" default-active-key="1">
      <a-tab-pane key="1" tab="学习时长统计">
        <learning-duration :task-id="taskId" />
      </a-tab-pane>
      <a-tab-pane key="2" tab="点名统计">
        <roll-call :task-id="taskId" />
      </a-tab-pane>
    </a-tabs>
  </layout>
</template>

<script>
import Layout from '../../layout.vue';
import LearningDuration from './components/LearningDuration.vue';
import RollCall from './components/RollCall.vue';

import { LiveStatistic } from 'common/vue/service';

export default {
  name: 'CourseManageLiveStatisticsDetails',

  components: {
    Layout,
    LearningDuration,
    RollCall
  },

  data() {
    return {
      courseId: this.$route.query.courseId,
      taskId: this.$route.query.taskId,
      liveData: {}
    }
  },

  mounted() {
    this.fetchLiveDetails();
  },

  methods: {
    async fetchLiveDetails() {
      this.liveData = await LiveStatistic.getLiveDetails({ query: { taskId: this.taskId } });
    }
  }
}
</script>

<style lang="less" scoped>
.tips {
  font-size: 14px;
  color: #999;
}

.live-data {
  position: relative;
  padding: 24px 24px 24px 48px;
  width: 100%;
  border-radius: 8px;
  box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.1);
  background: #fff;
  transition: background 0.3s ease;

  &::before {
    content: "";
    position: absolute;
    top: 8px;
    left: 8px;
    display: block;
    width: 8px;
    height: 8px;
    background: #46c17c;
    border-radius: 50%;
    box-shadow: 0 0 4px 0 rgba(70, 193, 124, 0.6);
  }

  &__value {
    font-size: 18px;
    color: #333;
    line-height: 28px;
    font-weight: 500;
  }

  &__label {
    margin-top: 16px;
    font-size: 14px;
    color: #999;
    font-weight: 400;
  }

  &:hover {
    background: #f9f9f9;
  }
}
</style>
