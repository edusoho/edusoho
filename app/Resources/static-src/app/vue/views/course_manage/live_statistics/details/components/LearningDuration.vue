<template>
  <div>
    <div class="clearfix">
      <a-input-search
        class="pull-left"
        placeholder="用户名或手机号"
        style="width: 200px;"
        @search="onSearch"
      />
      <a-button type="primary" class="pull-right">导出数据</a-button>
    </div>

    <a-table
      class="mt24"
      :columns="columns"
      :data-source="data"
      :row-key="record => record.key"
      :pagination="pagination"
      :loading="loading"
      @change="handleTableChange"
    >
      <template slot="nicknameTitle">用户名</template>
      <template slot="mobileTitle">手机号</template>
      <template slot="emailTitle">邮箱</template>
      <template slot="firstEnterTimeTitle">进入直播间时间</template>
      <template slot="watchDurationTitle">观看时长（分钟）</template>
      <template slot="checkinNumTitle">签到数</template>
      <template slot="chatNumberTitle">聊天数</template>
      <template slot="numberOfAnswersTitle">答题数</template>
    </a-table>
  </div>
</template>

<script>
import _ from 'lodash';
import { LiveStatistic } from 'common/vue/service';

const columns = [
  {
    dataIndex: 'nickname',
    key: 'nickname',
    slots: { title: 'nicknameTitle' }
  },
  {
    dataIndex: 'mobile',
    key: 'mobile',
    slots: { title: 'mobileTitle' }
  },
  {
    dataIndex: 'email',
    key: 'email',
    slots: { title: 'emailTitle' }
  },
  {
    dataIndex: 'firstEnterTime',
    key: 'firstEnterTime',
    slots: { title: 'firstEnterTimeTitle' }
  },
  {
    dataIndex: 'watchDuration',
    key: 'watchDuration',
    slots: { title: 'watchDurationTitle' }
  },
  {
    dataIndex: 'checkinNum',
    key: 'checkinNum',
    slots: { title: 'checkinNumTitle' }
  },
  {
    dataIndex: 'chatNumber',
    key: 'chatNumber',
    slots: { title: 'chatNumberTitle' }
  },
  {
    dataIndex: 'numberOfAnswers',
    key: 'numberOfAnswers',
    slots: { title: 'numberOfAnswersTitle' }
  }
];

export default {
  name: 'CourseManageLiveStatistics',

  props: {
    taskId: {
      type: String,
      required: true
    }
  },

  data() {
    return {
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
    this.fetchLiveMembers();
  },

  methods: {
    onSearch(value) {
      value = _.trim(value);
      if (value !== this.keyword) {
        this.keyword = value;
        this.pagination.current = 1;
        this.fetchLiveMembers();
      }
    },

    handleTableChange(pagination) {
      this.pagination.current = pagination.current;
      this.fetchLiveMembers();
    },

    async fetchLiveMembers() {
      this.loading = true;
      const { current, pageSize } = this.pagination;
      const params = {
        query: {
          taskId: this.taskId
        },
        params: {
          nameOrMobile: this.keyword,
          offset: (current - 1) * pageSize,
          limit: pageSize
        }
      }
      const { data, paging } = await LiveStatistic.getLiveMembers(params);
      this.loading = false;
      this.pagination.total = paging.total;
      this.data = data;
    }
  }
}
</script>
