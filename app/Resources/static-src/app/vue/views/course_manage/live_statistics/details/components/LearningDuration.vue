<template>
  <div>
    <div class="clearfix">
      <a-input-search
        class="pull-left"
        placeholder="用户名或手机号"
        style="width: 200px"
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
      <template slot="nameTitle">用户名</template>
      <template slot="phoneNumberTitle">手机号</template>
      <template slot="mailTitle">邮箱</template>
      <template slot="timeTitle">进入直播间时间</template>
      <template slot="durationTitle">观看时长（分钟）</template>
      <template slot="checkInNumberTitle">签到数</template>
      <template slot="chatNumberTitle">聊天数</template>
      <template slot="numberOfAnswersTitle">答题数</template>
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
    dataIndex: 'time',
    key: 'time',
    slots: { title: 'timeTitle' }
  },
  {
    dataIndex: 'duration',
    key: 'duration',
    slots: { title: 'durationTitle' }
  },
  {
    dataIndex: 'checkInNumber',
    key: 'checkInNumber',
    slots: { title: 'checkInNumberTitle' }
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

const data = [
  {
    key: '1',
    name: 'John Brown',
    phoneNumber: '15978507331',
    mail: '5201314@qq.com',
    time: '2021-10-14 15:40',
    duration: 20,
    checkInNumber: 10,
    chatNumber: 20,
    numberOfAnswers: 41234324
  },
  {
    key: '2',
    name: 'John Brown',
    phoneNumber: '15978507331',
    mail: '5201314@qq.com',
    time: '2021-10-14 15:40',
    duration: 20,
    checkInNumber: 10,
    chatNumber: 20,
    numberOfAnswers: 41234324
  }
];

export default {
  name: 'CourseManageLiveStatistics',

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
