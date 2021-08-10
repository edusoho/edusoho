<template>
  <aside-layout :breadcrumbs="[{ name: '超时未批阅作业' }]">
    <a-spin :spinning="getListLoading">
      <a-table :columns="columns" :data-source="overTimeTaskList" :pagination="paging" @change="change" rowKey="id">
        <template slot="createdTime" slot-scope="createdTime">
          {{ $dateFormat(createdTime, 'YYYY-MM-DD HH:mm') }}
        </template>
      </a-table>
    </a-spin>
  </aside-layout>
</template>

<script>
import AsideLayout from "app/vue/views/layouts/aside.vue";
import { OverView } from 'common/vue/service';
import { Card } from "ant-design-vue";

const columns = [
  {
    title: "学员",
    dataIndex: "userInfo",
    width: "15%",
    ellipsis: true,
    scopedSlots: { customRender: "userInfo" },
  },
  {
    title: "课时名称",
    dataIndex: "multiClass",
    width: "15%",
    ellipsis: true,
    scopedSlots: { customRender: "multiClass" },
  },
  {
    title: "所属班课",
    dataIndex: "class",
    width: "10%",
    ellipsis: true,
  },
  {
    title: "助教老师",
    dataIndex: "assistantInfo.nickname",
    width: "100",
  },
  {
    title: "作业/考试",
    dataIndex: "task",
    key: "taskIds",
    width: "10%",
    ellipsis: true,
    filters: [],
  },
  {
    title: "题量",
    dataIndex: "assessment.status",
    width: "8%",
    ellipsis: true,
  },
  {
    title: "创建时间",
    dataIndex: "createdTime",
    width: "160px",
    sorter: true,
    scopedSlots: { customRender: "createdTime" },
  },
];
const overTimeTaskList = [
  {
    id: 1,
    student: "aaaa",
    course: "是是是",
    class: "随时随地所",
    assistant: "是多少",
    task: "发发发",
    question: 111,
    createdTime: 1627374168,
  },
  {
    id: 2,
    student: "aaaa",
    course: "是是是",
    class: "随时随地所",
    assistant: "是多少",
    task: "毒贩夫妇",
    question: 111,
    createdTime: 1627374168,
  },
  {
    id: 3,
    student: "aaaa",
    course: "是是是",
    class: "随时随地所",
    assistant: "是多少",
    task: "私聊是对的",
    question: 111,
    createdTime: 1627374168,
  },
];
export default {
  name: "index",
  components: {
    AsideLayout,
    ACard: Card,
  },

  data() {
    return {
      columns,
      overTimeTaskList,
      getListLoading: false,
      paging: {
        total: 0,
        offset: 0,
        pageSize: 10,
      },
    };
  },

  computed: {},

  created() {
    this.getOverTimeList(this.paging);
    this.getOverTimeTaskList();
  },

  methods: {
    async getOverTimeList(params = {}) {
      params.limit = params.pageSize || 10;
      params.offset = params.offset || 0;

      this.getListLoading = true;
      try {
        const { data, paging } = await OverView.search(params);
        paging.page = paging.offset / paging.limit + 1;
        paging.pageSize = Number(paging.limit);
        paging.current = params.current || 1;
        this.overTimeTaskList = data;
        this.paging = paging;
      } finally {
        this.getListLoading = false;
      }
    },
    async getOverTimeTaskList() {
      // const { data } = await MultiClassProduct.search({
      //   keywords: "",
      //   offset: 0,
      //   limit: 100000,
      // });
      const data = this.overTimeTaskList;

      const index = _.findIndex(
        this.columns,
        (item) => item.dataIndex === "task"
      );
      const taskItem = this.columns[index];

      taskItem.filters = [];
      _.forEach(data, (item) => {
        taskItem.filters.push({
          text: item.task,
          value: item.id,
        });
      });
      this.$set(this.columns, index, taskItem);
    },
    change(pagination, filters, sorter) {
      console.log("pagination: ", pagination);
      console.log("filters: ", Object.keys(filters));
      console.log("sorter: ", sorter);
      const params = {};

      // if (pagination) {
      //   params.offset = pagination.pageSize * (pagination.current - 1);
      //   (params.pageSize = pagination.pageSize),
      //     (params.current = pagination.current);
      // }

      if (filters && Object.keys(filters).length > 0) {
        _.forEach(Object.keys(filters), (key) => {
          params[key] = filters[key];
        });
      }
      console.log(params);
      // if (sorter && sorter.order) {
      //   params[`${sorter.field}Sort`] =
      //     sorter.order === "ascend" ? "ASC" : "DESC";
      // }

      if (Object.keys(params).length > 0) {
        this.getOverTimeList(params);
      }
    },
  },
};
</script>
<style  scoped>
</style>