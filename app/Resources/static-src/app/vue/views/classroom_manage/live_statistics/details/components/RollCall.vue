<template>
  <div>
    <div class="clearfix">
      <a-select
        class="pull-left"
        default-value=""
        style="width: 120px;"
        @change="handleSelectChange"
      >
        <a-select-option value="">{{ 'live_statistics.checkin_status.all' | trans }}</a-select-option>
        <a-select-option value="checked">{{ 'live_statistics.checkin_status.checked' | trans }}</a-select-option>
        <a-select-option value="unchecked">{{ 'live_statistics.checkin_status.not_checked' | trans }}</a-select-option>
      </a-select>
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
      <span slot="checkin" slot-scope="text">
        {{ (text == '1' ? 'site.yes' : 'site.no') | trans  }}
      </span>
    </a-table>
  </div>
</template>

<script>
import { LiveStatistic } from 'common/vue/service';

const columns = [
  {
    title: Translator.trans('live_statistics.user_name'),
    dataIndex: 'nickname'
  },
  {
    title: Translator.trans('live_statistics.mobile'),
    dataIndex: 'mobile'
  },
  {
    title: Translator.trans('live_statistics.email'),
    dataIndex: 'email'
  },
  {
    title: Translator.trans('live_statistics.checkin_status'),
    dataIndex: 'checkin',
    scopedSlots: { customRender: 'checkin' }
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
      status: ''
    }
  },

  mounted() {
    this.fetchLiveRollCall();
  },

  methods: {
    handleSelectChange(value) {
      this.status = value;
      this.pagination.current = 1;
      this.fetchLiveRollCall();
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
          status: this.status,
          offset: (current - 1) * pageSize,
          limit: pageSize
        }
      }
      const { data, paging } = await LiveStatistic.getLiveRollCall(params);
      this.loading = false;
      this.pagination.total = paging.total;
      this.data = data;
    },

    handleClickExport() {
      window.open(`/task/${this.taskId}/live_statistic/roll_call/export?status=${this.status}`);
    }
  }
}
</script>
