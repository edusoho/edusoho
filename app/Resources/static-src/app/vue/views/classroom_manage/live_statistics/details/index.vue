<template>
  <layout>
    <template #title>
      <div class="clearfix">
        <div class="pull-left">xxx / {{ liveData.task.title }}</div>
        <div class="pull-right tips">{{ 'live_statistics.detail_notice' | trans }}</div>
      </div>
    </template>

    <a-row :gutter="16">
      <a-col :span="6">
        <div class="live-data">
          <div class="live-data__value text-overflow">{{ liveData.teacher }}</div>
          <div class="live-data__label">{{ 'live_statistics.presenter' | trans }}</div>
        </div>
      </a-col>

      <a-col :span="12">
        <div class="live-data">
          <div class="live-data__value">
            {{ $dateFormat(liveData.startTime, 'YYYY-MM-DD HH:mm') }}
            {{ 'live_statistics.to' | trans }}
            {{ $dateFormat(liveData.endTime, 'YYYY-MM-DD HH:mm') }}
          </div>
          <div class="live-data__label">{{ 'live_statistics.live_time' | trans }}</div>
        </div>
      </a-col>

      <a-col :span="6">
        <div class="live-data">
          <div class="live-data__value">{{ liveData.length }}{{ 'site.date.minute' | trans }}</div>
          <div class="live-data__label">{{ 'live_statistics.actual_live_time' | trans }}</div>
        </div>
      </a-col>
    </a-row>

    <a-row :gutter="16" class="mt16">
      <a-col :span="6">
        <div class="live-data">
          <div class="live-data__value">{{ liveData.maxOnlineNumber }}{{ 'live_statistics.people' | trans }}</div>
          <div class="live-data__label">{{ 'live_statistics.online_number' | trans }}</div>
        </div>
      </a-col>

      <a-col :span="6">
        <div class="live-data">
          <div class="live-data__value">{{ liveData.memberNumber }}{{ 'live_statistics.people' | trans }}</div>
          <div class="live-data__label">{{ 'live_statistics.number_of_visitors' | trans }}</div>
        </div>
      </a-col>

      <a-col :span="6">
        <div class="live-data">
          <div class="live-data__value">{{ liveData.chatNumber }}{{ 'live_statistics.strip' | trans }}</div>
          <div class="live-data__label">{{ 'live_statistics.chat_number' | trans }}</div>
        </div>
      </a-col>

      <a-col :span="6">
        <div class="live-data">
          <div class="live-data__value">{{ liveData.avgWatchTime }}{{ 'site.date.minute' | trans }}</div>
          <div class="live-data__label">{{ 'live_statistics.per_capita_viewing_time' | trans }}</div>
        </div>
      </a-col>
    </a-row>

    <a-tabs class="mt16" default-active-key="1">
      <a-tab-pane key="1" :tab="'live_statistics.detail.total_learn_time' | trans">
        <learning-duration :task-id="taskId" />
      </a-tab-pane>
      <a-tab-pane key="2" :tab="'live_statistics.detail.checkin' | trans">
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
  name: 'classroomManageLiveStatisticsDetails',

  components: {
    Layout,
    LearningDuration,
    RollCall
  },

  data() {
    return {
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
