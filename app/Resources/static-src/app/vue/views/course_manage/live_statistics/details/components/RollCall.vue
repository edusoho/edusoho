<template>
  <div>
    <div class="clearfix">
      <a-input-search
        class="pull-left"
        placeholder="用户名或手机号"
        style="width: 200px"
        @search="onSearch"
      />
      <a-select class="pull-left ml16" default-value="all" style="width: 120px">
        <a-select-option value="all">全部</a-select-option>
        <a-select-option value="1">已点名</a-select-option>
        <a-select-option value="2">未点名</a-select-option>
      </a-select>
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
      <template slot="nameTitle">用户名</template>
      <template slot="phoneNumberTitle">手机号</template>
      <template slot="mailTitle">邮箱</template>
      <template slot="rollCallTitle">是否点名</template>
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
        hideOnSinglePage: true
      },
      loading: false
    }
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
