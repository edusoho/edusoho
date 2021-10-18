<template>
  <div class="course-manage">
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
      :row-key="record => record.key"
      :pagination="pagination"
      :loading="loading"
      @change="handleTableChange"
    >
      <template slot="nameTitle">{{ 'course.task' | trans }}</template>
      <template slot="timeTitle">{{ 'course.live_statistics.live_start_time' | trans }}</template>
      <template slot="durationTitle">{{ 'course.live_statistics.live_time_long' | trans }}</template>
      <template slot="numberOfPeopleTitle">{{ 'course.live_statistics.max_participate_count' | trans }}</template>
      <template slot="statusTitle">{{ 'course.live_statistics.live_status' | trans }}</template>
      <template slot="actionTitle">{{ 'course.live_statistics.operation' | trans }}</template>


      <a slot="name" slot-scope="text">{{ text }}</a>
      <span slot="time">2021-10-14 15:40</span>
      <span slot="status" slot-scope="status">
        10
      </span>
      <span slot="action" slot-scope="text, record">
        <a-button type="link" @click="handleClickViewDetail()">{{ 'site.btn.detail' | trans }}</a-button>
      </span>
    </a-table>
  </div>
</template>

<script>
const columns = [
  {
    dataIndex: 'name',
    key: 'name',
    slots: { title: 'nameTitle' },
    scopedSlots: { customRender: 'name' }
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
    key: 'numberOfPeople',
    dataIndex: 'numberOfPeople',
    slots: { title: 'numberOfPeopleTitle' }
  },
  {
    key: 'status',
    slots: { title: 'statusTitle' },
    scopedSlots: { customRender: 'status' }
  },
  {
    key: 'action',
    slots: { title: 'actionTitle' },
    scopedSlots: { customRender: 'action' },
  },
];

const data = [
  {
    key: '1',
    name: 'John Brown',
    time: '2021-10-14 15:40',
    duration: 20,
    numberOfPeople: 10,
    status: 'end'
  },
  {
    key: '2',
    name: 'John Brown',
    time: '2021-10-14 15:40',
    duration: 20,
    numberOfPeople: 10,
    status: 'end'
  },
  {
    key: '3',
    name: 'John Brown',
    time: '2021-10-14 15:40',
    duration: 20,
    numberOfPeople: 10,
    status: 'end'
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

    },

    handleClickViewDetail() {

    }
  }
}
</script>
