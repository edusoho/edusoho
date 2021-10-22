<template>
  <div>
    <div class="clearfix">
      <a-input-search
        class="pull-left"
        :placeholder="'live_statistics.user_name_or_mobile_number' | trans"
        style="width: 200px;"
        @search="onSearch"
      />
      <a-button type="primary" class="pull-right" @click="handleClickExport">{{ 'site.btn.export' | trans }}</a-button>
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
      <template slot="nicknameTitle">{{ 'live_statistics.user_name' | trans }}</template>
      <template slot="truenameTitle">{{ 'live_statistics.true_name' | trans }}</template>
      <template slot="mobileTitle">{{ 'live_statistics.mobile' | trans }}</template>
      <template slot="emailTitle">{{ 'live_statistics.email' | trans }}</template>
      <template slot="firstEnterTimeTitle">{{ 'live_statistics.enter_theLlive_room_time' | trans }}</template>
      <template slot="watchDurationTitle">{{ 'live_statistics.watching_time' | trans }}</template>
      <template slot="checkinNumTitle">{{ 'live_statistics.check_in_number' | trans }}</template>
      <template slot="chatNumberTitle">{{ 'live_statistics.chat_number' | trans }}</template>
      <template slot="answerNumTitle">{{ 'live_statistics.answers_number' | trans }}</template>
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
    dataIndex: 'truename',
    key: 'truename',
    slots: { title: 'truenameTitle' }
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
    dataIndex: 'answerNum',
    key: 'answerNum',
    slots: { title: 'answerNumTitle' }
  }
];

export default {
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
    },

    handleClickExport() {
      window.open(`/task/${this.taskId}/live_statistic/export?nameOrMobile=${this.keyword}`);
    }
  }
}
</script>
