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
import DashBoard from "common/vue/service/DashBoard";
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
        timeoutReviewNum: 2,
        multiClassData: {
          startNum: 4,
          notStartNum: 0,
        },
        studyStudentData: {
          studyNum: 17,
          notStudyNum: 68,
        },
      },
      studentDataSource: {
        newStudentRankList: {
          ascSort: [
            {
              id: 9,
              count: "1",
              courseId: "123",
              multiClass: "新建班课1",
            },
            {
              id: 10,
              count: "2",
              courseId: "120",
              multiClass: "班课2",
            },
          ],
          descSort: [
            {
              count: "2",
              courseId: "120",
              multiClass: "班课2",
            },
            {
              count: "1",
              courseId: "123",
              multiClass: "新建班课1",
            },
          ],
        },
        reviewData: {
          ascSort: [
            {
              id: 1,
              multiClass: "新建班课1",
              rate: 0.33,
            },
            {
              id: 2,
              multiClass: "班课2",
              rate: 1,
            },
          ],
          descSort: [
            {
              multiClass: "班课2",
              rate: 1,
            },
            {
              multiClass: "新建班课1",
              rate: 0.33,
            },
          ],
        },
        finishedRateList: {
          ascSort: [
            {
              id: 3,
              multiClass: "班课2",
              rate: 0,
            },
            {
              id: 7,
              multiClass: "新建班课1",
              rate: 0.33,
            },
            {
              id: 8,
              multiClass: "班课1",
              rate: 1,
            },
          ],
          descSort: [
            {
              multiClass: "班课1",
              rate: 1,
            },
            {
              multiClass: "新建班课1",
              rate: 0.33,
            },
            {
              multiClass: "班课2",
              rate: 0,
            },
          ],
        },
        questionAnswerRateList: {
          ascSort: [
            {
              id: 4,
              multiClass: "班课2",
              rate: 0,
            },
            {
              id: 5,
              multiClass: "新建班课1",
              rate: 0.33,
            },
            {
              id: 6,
              multiClass: "班课1",
              rate: 1,
            },
          ],
          descSort: [
            {
              multiClass: "班课1",
              rate: 1,
            },
            {
              multiClass: "新建班课1",
              rate: 0.33,
            },
            {
              multiClass: "班课2",
              rate: 0,
            },
          ],
        },
      },
      getListLoading: false,
    };
  },

  computed: {},

  created() {
    this.getGraphicData();
    this.getRankData();
  },

  methods: {
    async getGraphicData() {
      try {
        this.getListLoading = true;
        this.graphicData = await DashBoard.searchGraphicDatum();
      } finally {
        this.getListLoading = false;
      }
    },
    async getRankData() {
      try {
        this.getListLoading = true;
        this.studentDataSource = await DashBoard.searchRankList();
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