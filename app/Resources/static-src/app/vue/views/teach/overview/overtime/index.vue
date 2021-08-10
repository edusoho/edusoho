<template>
  <aside-layout :breadcrumbs="[{ name: '超时未批阅作业' }]">
    <a-spin :spinning="getListLoading">
      <a-table :columns="columns" :data-source="overTimeTaskList" :pagination="paging" @change="change" rowKey="id">
        <template slot="userInfo" slot-scope="userInfo">
          <a href="javascript:;" @click="viewStudentInfo(userInfo.id)">{{userInfo.nickname}}</a>
        </template>
        <template slot="multiClass" slot-scope="multiClass">
          <a href="javascript:;" @click="goToMultiClassManage(multiClass.id)">{{multiClass.title}}</a>
        </template>
        <template slot="activity" slot-scope="activity">
          <span>{{activity.title}}</span>
          <a-tag v-if="activity.mediaType === 'testpaper'" color="#fb8d4d" style="margin-left:8px">考</a-tag>
        </template>
        <template slot="end_time" slot-scope="end_time">
          {{ $dateFormat(end_time, 'YYYY-MM-DD HH:mm') }}
        </template>
      </a-table>
      <a-modal title="学员详细信息" :visible="viewStudentInfoVisible" @cancel="close">
        <userInfoTable :user="modalShowUser" />
        <template slot="footer">
          <a-button key="back" @click="close"> 关闭 </a-button>
        </template>
      </a-modal>
    </a-spin>
  </aside-layout>
</template>

<script>
import AsideLayout from "app/vue/views/layouts/aside.vue";
import userInfoTable from "app/vue/views/components/userInfoTable";

import { OverView, UserProfiles } from "common/vue/service";
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
    dataIndex: "activity.title",
    width: "15%",
    ellipsis: true,
  },
  {
    title: "所属班课",
    dataIndex: "multiClass",
    width: "10%",
    ellipsis: true,
    scopedSlots: { customRender: "multiClass" },
  },
  {
    title: "助教老师",
    dataIndex: "assistantInfo.nickname",
  },
  {
    title: "作业/考试",
    dataIndex: "activity",
    ellipsis: true,
    scopedSlots: { customRender: "activity" },
  },
  {
    title: "题量",
    dataIndex: "assessment.item_count",
    width: "8%",
    ellipsis: true,
  },
  {
    title: "创建时间",
    dataIndex: "end_time",
    width: "160px",
    sorter: true,
    scopedSlots: { customRender: "end_time" },
  },
];

export default {
  name: "index",
  components: {
    AsideLayout,
    ACard: Card,
    userInfoTable,
  },

  data() {
    return {
      columns,
      overTimeTaskList: [],
      getListLoading: false,
      paging: {
        total: 0,
        offset: 0,
        pageSize: 10,
      },
      modalShowUser: {},
      viewStudentInfoVisible: false,
    };
  },

  computed: {},

  created() {
    this.getOverTimeList(this.paging);
    // this.getOverTimeTaskList();
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
    // async getOverTimeTaskList() {
    //   // const { data } = await MultiClassProduct.search({
    //   //   keywords: "",
    //   //   offset: 0,
    //   //   limit: 100000,
    //   // });
    //   const data = this.overTimeTaskList;

    //   const index = _.findIndex(
    //     this.columns,
    //     (item) => item.dataIndex === "task"
    //   );
    //   const taskItem = this.columns[index];

    //   taskItem.filters = [];
    //   _.forEach(data, (item) => {
    //     taskItem.filters.push({
    //       text: item.task,
    //       value: item.id,
    //     });
    //   });
    //   this.$set(this.columns, index, taskItem);
    // },
    async viewStudentInfo(id) {
      this.modalShowUser = await UserProfiles.get(id);
      this.viewStudentInfoVisible = true;
    },
    close() {
      this.viewStudentInfoVisible = false;
    },
    goToMultiClassManage(id) {
      this.$router.push({
        name: "MultiClassCourseManage",
        params: { id },
      });
    },
    change(pagination, filters, sorter) {
      console.log("pagination: ", pagination);
      console.log("filters: ", Object.keys(filters));
      console.log("sorter: ", sorter);
      const params = {};

      if (pagination) {
        params.offset = pagination.pageSize * (pagination.current - 1);
        (params.pageSize = pagination.pageSize),
          (params.current = pagination.current);
      }

      // if (filters && Object.keys(filters).length > 0) {
      //   _.forEach(Object.keys(filters), (key) => {
      //     params[key] = filters[key];
      //   });
      // }
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