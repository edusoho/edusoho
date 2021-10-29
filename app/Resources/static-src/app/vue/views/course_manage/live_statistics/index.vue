<template>
  <layout>
    <template #title>{{ 'live_statistics' | trans }}</template>

    <div class="clearfix">
      <a-input-search
        class="pull-left"
        :placeholder="'live_statistics.task_name_placeholder' | trans"
        style="width: 200px;"
        @search="onSearch"
      />
      <a-button type="primary" class="pull-right" @click="handleClickExport">{{ 'site.btn.export' | trans }}</a-button>
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
      <template slot="customTitle" slot-scope="text, record">
        <a-button type="link" @click="handleClickViewTask(record.id)">{{ text }}</a-button>
      </template>

      <template slot="startTime" slot-scope="text">
        {{ $dateFormat(text, 'YYYY-MM-DD HH:mm') }}
      </template>

      <template slot="status" slot-scope="text">
        <span :class="`task-status task-status--${text}`">{{ getTaskStatus(text) | trans }}</span>
      </template>

      <span slot="action" slot-scope="record">
        <a-button type="link" @click="handleClickViewDetail(record.id)">{{ 'site.btn.detail' | trans }}</a-button>
      </span>
    </a-table>
  </layout>
</template>

<script>
import _ from 'lodash';
import Layout from '../layout.vue';
import { LiveStatistic } from 'common/vue/service';

const columns = [
  {
    title: Translator.trans('course.task'),
    dataIndex: 'title',
    scopedSlots: { customRender: 'customTitle' }
  },
  {
    title: Translator.trans('live_statistics.live_start_time'),
    dataIndex: 'startTime',
    scopedSlots: { customRender: 'startTime' }
  },
  {
    title: Translator.trans('live_statistics.live_time_long'),
    dataIndex: 'length'
  },
  {
    title: Translator.trans('live_statistics.max_participate_count'),
    dataIndex: 'maxStudentNum'
  },
  {
    title: Translator.trans('live_statistics.live_status'),
    dataIndex: 'status',
    scopedSlots: { customRender: 'status' }
  },
  {
    title: Translator.trans('live_statistics.operation'),
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
      if (value !== this.keyword) {
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
          taskId: id
        }
      });
    },

    getTaskStatus(status) {
      const taskStatus = {
        coming: 'live_statistics.live_coming',
        playing: 'live_statistics.live_playing',
        finished: 'live_statistics.live_finished'
      };

      return taskStatus[status];
    },

    handleClickExport() {
      window.open(`/course/${this.courseId}/live_statistic/export?title=${this.keyword}`);
    }
  }
}
</script>

<style lang="less" scoped>
.task-status {
  position: relative;
  padding-left: 8px;

  &::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 0;
    transform: translateY(-50%);
    width: 6px;
    height: 6px;
    border-radius: 50%;
  }

  &--coming::before {
    background-color: #999;
  }

  &--finished::before {
    background-color: #fe4040;
  }

  &--playing::before {
    background-color: #46c37B;
  }
}
</style>
