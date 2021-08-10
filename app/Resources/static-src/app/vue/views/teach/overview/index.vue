<template>
  <aside-layout :breadcrumbs="[{ name: '教务仪表盘' }]">
    <a-spin :spinning="getListLoading">
      <dashboard-card />
      <a-row :gutter="24">
        <a-col :span="12">
          <div class="diagram-layout">
            <x-diagram></x-diagram>
          </div>
        </a-col>
        <a-col :span="12">
          <div class="diagram-layout">
            <y-diagram></y-diagram>
          </div>
        </a-col>
      </a-row>
      <a-row :gutter="[24,32]">
        <a-col :span="12">
          <table-data title="班课昨日新增学员数" :data="studentDataSource.addYesterday"></table-data>
        </a-col>
        <a-col :span="12">
          <table-data title="班课昨日完课率" :data="studentDataSource.endYesterday"></table-data>
        </a-col>
      </a-row>
      <a-row :gutter="[24,32]">
        <a-col :span="12">
          <table-data title="班课作业批改率" :data="studentDataSource.homeworkCorrection"></table-data>
        </a-col>
        <a-col :span="12">
          <table-data title="班课问答回答率" :data="studentDataSource.responseRate"></table-data>
        </a-col>
      </a-row>
    </a-spin>
  </aside-layout>
</template>

<script>
import AsideLayout from "app/vue/views/layouts/aside.vue";
import DashboardCard from "./dashboardCard.vue";
import XDiagram from "./components/xDiagram.vue";
import YDiagram from "./components/yDiagram.vue";
import TableData from "./components/TableData.vue";

export default {
  name: "index",
  components: {
    AsideLayout,
    DashboardCard,
    XDiagram,
    YDiagram,
    TableData,
  },

  data() {
    return {
      studentDataSource: {
        addYesterday: [
          {
            rank: 1,
            class: "教师资格证3月班",
            add: 200,
          },
          {
            rank: 2,
            class: "教师资格证3月班",
            add: 200,
          },
          {
            rank: 3,
            class: "教师资格证3月班",
            add: 200,
          },
          {
            rank: 4,
            class: "教师资格证3月班",
            add: 200,
          },
          {
            rank: 5,
            class: "教师资格证3月班",
            add: 200,
          },
        ],
        endYesterday: [
          {
            rank: 1,
            class: "教师资格证3月班",
            add: 200,
          },
          {
            rank: 2,
            class: "教师资格证3月班",
            add: 200,
          },
          {
            rank: 3,
            class: "教师资格证3月班",
            add: 200,
          },
          {
            rank: 4,
            class: "教师资格证3月班",
            add: 200,
          },
          {
            rank: 5,
            class: "教师资格证3月班",
            add: 200,
          },
        ],
        homeworkCorrection: [
          {
            rank: 1,
            class: "教师资格证3月班",
            add: 200,
          },
          {
            rank: 2,
            class: "教师资格证3月班",
            add: 200,
          },
          {
            rank: 3,
            class: "教师资格证3月班",
            add: 200,
          },
          {
            rank: 4,
            class: "教师资格证3月班",
            add: 200,
          },
          {
            rank: 5,
            class: "教师资格证3月班",
            add: 200,
          },
        ],
        responseRate: [
          {
            rank: 1,
            class: "教师资格证3月班",
            add: 200,
          },
          {
            rank: 2,
            class: "教师资格证3月班",
            add: 200,
          },
          {
            rank: 3,
            class: "教师资格证3月班",
            add: 200,
          },
          {
            rank: 4,
            class: "教师资格证3月班",
            add: 200,
          },
          {
            rank: 5,
            class: "教师资格证3月班",
            add: 200,
          },
        ],
      },
      getListLoading: false,
    };
  },
  created() {},
  computed: {},

  mounted() {},

  methods: {},
};
</script>
<style  scoped>
.diagram-layout {
  margin-top: 40px;
  background: #ffffff;
  box-shadow: 0 0 16px 0 rgba(0, 0, 0, 0.1);
  border-radius: 8px;
}
</style>