<template>
  <div>
    <div class="clearfix">
      <a-input-search
        class="pull-left"
        :placeholder="'live_statistics.user_name_or_mobile_number' | trans"
        style="width: 200px;"
        @search="onSearch"
      />
      <a-select class="pull-left ml16" default-value="all" style="width: 120px;">
        <a-select-option value="all">{{ 'live_statistics.checkin_status.all' | trans }}</a-select-option>
        <a-select-option value="1">{{ 'live_statistics.checkin_status.checked' | trans }}</a-select-option>
        <a-select-option value="2">{{ 'live_statistics.checkin_status.not_checked' | trans }}</a-select-option>
      </a-select>
      <a-button type="primary" class="pull-right">{{ 'site.btn.export' | trans }}</a-button>
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
      <template slot="mobileTitle">{{ 'live_statistics.mobile' | trans }}</template>
      <template slot="emailTitle">{{ 'live_statistics.email' | trans }}</template>
      <template slot="checkinTitle">{{ 'live_statistics.checkin_status' | trans }}</template>
    </a-table>
  </div>
</template>

<script>
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
    dataIndex: 'checkin',
    key: 'checkin',
    slots: { title: 'checkinTitle' }
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
      keyword: '',
      status: ''
    }
  },

  mounted() {
    this.fetchLiveRollCall();
  },

  methods: {
    onSearch(value) {
      value = _.trim(value);
      if (value !== this.keyword) {
        this.keyword = value;
        this.pagination.current = 1;
        this.fetchLiveRollCall();
      }
    },

    handleTableChange(pagination) {
      this.pagination.current = pagination.current;
      this.fetchLiveRollCall();
    },

    async fetchLiveRollCall() {
      this.loading = true;
      const { current, pageSize } = this.pagination;
      const params = {
        query: {
          taskId: this.taskId
        },
        params: {
          nameOrMobile: this.keyword,
          status: this.status,
          offset: (current - 1) * pageSize,
          limit: pageSize
        }
      }
      const { data, paging } = await LiveStatistic.getLiveRollCall(params);
      this.loading = false;
      this.pagination.total = paging.total;
      this.data = data;
    }
  }
}
</script>
