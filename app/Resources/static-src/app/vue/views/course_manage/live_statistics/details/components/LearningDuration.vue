<template>
  <div>
    <div class="clearfix">
      <a-input-search
        class="pull-left"
        :placeholder="'live_statistics.user_name_or_mobile_number' | trans"
        style="width: 200px;"
        @search="onSearch"
      />
      <a-button
        type="primary"
        class="pull-right"
        @click="handleClickExport"
      >
        {{ 'site.btn.export' | trans }}
      </a-button>
    </div>

    <a-table
      class="mt24"
      :columns="columns"
      :data-source="data"
      :row-key="record => record.id"
      :pagination="pagination"
      :loading="loading"
      @change="handleTableChange"
    />
  </div>
</template>

<script>
import _ from 'lodash';
import { LiveStatistic } from 'common/vue/service';

const columns = [
  {
    title: Translator.trans('live_statistics.user_name'),
    dataIndex: 'nickname'
  },
  {
    title: Translator.trans('live_statistics.true_name'),
    dataIndex: 'truename'
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
    title: Translator.trans('live_statistics.enter_theLlive_room_time'),
    dataIndex: 'firstEnterTime'
  },
  {
    title: Translator.trans('live_statistics.watching_time'),
    dataIndex: 'watchDuration'
  },
  {
    title: Translator.trans('live_statistics.check_in_number'),
    dataIndex: 'checkinNum'
  },
  {
    title: Translator.trans('live_statistics.chat_number'),
    dataIndex: 'chatNumber'
  },
  {
    title: Translator.trans('live_statistics.answers_number'),
    dataIndex: 'answerNum'
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
