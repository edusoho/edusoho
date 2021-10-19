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
      <template slot="nameTitle">{{ 'live_statistics.user_name' | trans }}</template>
      <template slot="phoneNumberTitle">{{ 'live_statistics.mobile' | trans }}</template>
      <template slot="mailTitle">{{ 'live_statistics.email' | trans }}</template>
      <template slot="rollCallTitle">{{ 'live_statistics.checkin_status' | trans }}</template>
    </a-table>
  </div>
</template>

<script>

const columns = [
  {
    dataIndex: 'name',
    key: 'name',
    slots: { title: 'nameTitle' }
  },
  {
    dataIndex: 'phoneNumber',
    key: 'phoneNumber',
    slots: { title: 'phoneNumberTitle' }
  },
  {
    dataIndex: 'mail',
    key: 'mail',
    slots: { title: 'mailTitle' }
  },
  {
    dataIndex: 'rollCall',
    key: 'rollCall',
    slots: { title: 'rollCallTitle' }
  }
];

const data = [
  {
    key: '1',
    name: 'John Brown',
    phoneNumber: '15978507331',
    mail: '5201314@qq.com',
    rollCall: 1
  }
];

export default {
  data() {
    return {
      data,
      columns,
      pagination: {
        hideOnSinglePage: true,
        current: 1,
        pageSize: 10,
        total: 0
      },
      loading: false
    }
  },

  mounted() {
    console.log('RollCall');
  },

  methods: {
    onSearch(value) {
      console.log(value);
    },

    handleTableChange() {

    }
  }
}
</script>
