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
        <a-button type="link" @click="handleClickViewTask(record.id)">{{ text }}</a-button>
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
import _ from 'lodash';

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
    scopedSlots: { customRender: 'status' }
  },
  {
    key: 'action',
    slots: { title: 'actionTitle' },
    scopedSlots: { customRender: 'action' }
  }
];

export default {
  name: 'CourseManageLiveStatistics',

  components: {
    Layout
  },

  data() {
    return {
      courseId: $('.js-course-id').val(),
      data: [],
      columns,
      pagination: {
        hideOnSinglePage: true,
        current: 1,
        pageSize: 10,
        total: 0
      },
      loading: false,
      keyword: ''
    }
  },

  mounted() {
    this.fetchLiveStatistics();
  },

  methods: {
    onSearch(value) {
      value = _.trim(value);
      if (_.size(value) && value !== this.keyword) {
        this.keyword = value;
        this.pagination.current = 1;
        this.fetchLiveStatistics();
      }
    },

    handleTableChange(pagination) {
      this.pagination.current = pagination.current;
      this.fetchLiveStatistics();
    },

    async fetchLiveStatistics() {
      this.loading = true;
      const { current, pageSize } = this.pagination;
      const params = {
        params: {
          courseId: this.courseId,
          title: this.keyword,
          offset: (current - 1) * pageSize,
          limit: pageSize
        }
      }
      const { data, paging } = await LiveStatistic.get(params);
      this.loading = false;
      this.pagination.total = paging.total;
      this.data = data;
    },

    handleClickViewTask(id) {
      window.open(`/course/${this.courseId}/task/${id}/show`);
    },

    handleClickViewDetail(id) {
      this.$router.push({
        name: 'CourseManageLiveStatisticsDetails',
        query: {
          courseId: this.courseId,
          taskId: id
        }
      });
    }
  }
}
</script>
