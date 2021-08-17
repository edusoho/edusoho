<template>
  <aside-layout :breadcrumbs="[{ name: '教务仪表盘' }]">
    <a-spin :spinning="getListLoading">
      <dashboard-card :graphicData="graphicData" />
      <a-row :gutter="24">
        <a-col :span="12">
          <div class="diagram-layout">
            <x-diagram :graphicData="graphicData.multiClassData"></x-diagram>
          </div>
        </a-col>
        <a-col :span="12">
          <div class="diagram-layout">
            <y-diagram :graphicData="graphicData.studyStudentData"></y-diagram>
          </div>
        </a-col>
      </a-row>
      <a-row :gutter="[24,32]">
        <a-col :span="12">
          <table-data title="班课昨日新增学员数" :data="studentDataSource.newStudentRankList"></table-data>
        </a-col>
        <a-col :span="12">
          <table-data title="班课昨日完课率" :data="studentDataSource.finishedRateList"></table-data>
        </a-col>
      </a-row>
      <a-row :gutter="[24,32]">
        <a-col :span="12">
          <table-data title="班课作业批改率" :data="studentDataSource.reviewData"></table-data>
        </a-col>
        <a-col :span="12">
          <table-data title="班课问答回答率" :data="studentDataSource.questionAnswerRateList"></table-data>
        </a-col>
      </a-row>
    </a-spin>
  </aside-layout>
</template>

<script>
import { Dashboard, DashboardRank } from "common/vue/service";
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
      graphicData: {
        totalNewStudentNum: 0,
        totalFinishedStudentNum: 0,
        todayLiveData: {
          totalLiveNum: 0,
          overLiveNum: 0,
        },
        timeoutReviewNum: 0,
        multiClassData: {
          startNum: 0,
          notStartNum: 0,
        },
        studyStudentData: {
          studyNum: 0,
          notStudyNum: 0,
        },
      },
      studentDataSource: {
        newStudentRankList: {
          ascSort: [],
          descSort: [],
        },
        reviewData: {
          ascSort: [],
          descSort: [],
        },
        finishedRateList: {
          ascSort: [],
          descSort: [],
        },
        questionAnswerRateList: {
          ascSort: [],
          descSort: [],
        },
      },
      getListLoading: false,
    };
  },

  computed: {},

  mounted() {
    this.getGraphicData();
    this.getRankData();
  },

  methods: {
    async getGraphicData() {
      try {
        this.getListLoading = true;
        this.graphicData = await Dashboard.search();
      } finally {
        this.getListLoading = false;
      }
    },
    async getRankData() {
      try {
        this.getListLoading = true;
        this.studentDataSource = await DashboardRank.search();
        console.log("11", this.studentDataSource);
      } finally {
        this.getListLoading = false;
      }
    },
  },
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