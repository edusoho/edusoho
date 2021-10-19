<template>
  <layout>
    <template #title>{{ 'course.live_statistics' | trans }}</template>

    <div class="clearfix">
      <a-input-search
        class="pull-left"
        :placeholder="'course.live_statistics.task_name_placeholder' | trans"
        style="width: 200px"
        @search="onSearch"
      />
      <a-button type="primary" class="pull-right">{{ 'form.btn.export' | trans }}</a-button>
    </div>

    <a-table
      class="mt24"
      :columns="columns"
      :data-source="data"
      :row-key="record => record.id"
      :pagination="pagination"
      :loading="loading"
      @change="handleTableChange"
    >
      <template slot="customTitle">{{ 'course.task' | trans }}</template>
      <template slot="startTimeTitle">{{ 'course.live_statistics.live_start_time' | trans }}</template>
      <template slot="lengthTitle">{{ 'course.live_statistics.live_time_long' | trans }}</template>
      <template slot="maxStudentNumTitle">{{ 'course.live_statistics.max_participate_count' | trans }}</template>
      <template slot="statusTitle">{{ 'course.live_statistics.live_status' | trans }}</template>
      <template slot="actionTitle">{{ 'course.live_statistics.operation' | trans }}</template>

      <template slot="customTitle" slot-scope="text, record">
        <a :href="`/${record.id}`">{{ text }}</a>
      </template>

      <template slot="startTime" slot-scope="text">
        {{ $dateFormat(text, 'YYYY-MM-DD HH:mm:ss') }}
      </template>
      <template slot="status" slot-scope="text">
        {{ text }}
      </template>

      <span slot="action" slot-scope="record">
        <a-button type="link" @click="handleClickViewDetail(record.id)">{{ 'site.btn.detail' | trans }}</a-button>
      </span>
    </a-table>
  </layout>
</template>

<script>
import Layout from '../layout.vue';

import { LiveStatistic } from 'common/vue/service';

const columns = [
  {
    dataIndex: 'title',
    key: 'title',
    slots: { title: 'customTitle' },
    scopedSlots: { customRender: 'customTitle' }
  },
  {
    dataIndex: 'startTime',
    key: 'startTime',
    slots: { title: 'startTimeTitle' },
    scopedSlots: { customRender: 'startTime' }
  },
  {
    dataIndex: 'length',
    key: 'length',
    slots: { title: 'lengthTitle' }
  },
  {
    key: 'maxStudentNum',
    dataIndex: 'maxStudentNum',
    slots: { title: 'maxStudentNumTitle' }
  },
  {
    dataIndex: 'status',
    key: 'status',
    slots: { title: 'statusTitle' },
    scopedSlots: { customRender: 'status' },
  },
  {
    key: 'action',
    slots: { title: 'actionTitle' },
    scopedSlots: { customRender: 'action' },
  },
];

export default {
  name: 'CourseManageLiveStatistics',

  components: {
    Layout
  },

  data() {
    return {
      data: [],
      columns,
      pagination: {
        hideOnSinglePage: true
      },
      loading: false,
    }
  },

  mounted() {
    this.fetchLiveStatistics();
  },

  methods: {
    onSearch(value) {
      console.log(value);
    },

    handleTableChange() {

    },

    async fetchLiveStatistics() {
      const { data, paging } = await LiveStatistic.get({ params: { courseId: 54 }});
      this.data = data;
    },

    handleClickViewDetail() {
      this.$router.push({
        name: 'CourseManageLiveStatisticsDetails'
      });
    }
  }
}
</script>
