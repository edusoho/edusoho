<template>
  <div class="dashboard-card">
    <a-row :gutter="24">
      <a-col :span="6">
        <a-card :bordered="false" style="border-top: 2px solid #5CCFEA">
          <a-row>
            <div class="card">
              <svg-icon class="icon icon-add-student" icon="icon-add-student" />
              <div class="right-block">
                <p class="card-title text-size">昨日新增班课学员总数</p>
                <span class="card-count text-size" style="color: #5CCFEA">{{ graphicData.totalNewStudentNum }}</span>
              </div>
            </div>
          </a-row>
        </a-card>
      </a-col>
      <a-col :span="6">
        <a-card :bordered="false" style="border-top: 2px solid #FFC173">
          <a-row>
            <div class="card">
              <svg-icon class="icon icon-add-student-success" icon="icon-add-student-success" />
              <div class="right-block">
                <p class="card-title text-size">昨日班课结课学员总数</p>
                <span class="card-count text-size" style="color: #FFC173">{{ graphicData.totalFinishedStudentNum }}</span>
              </div>
            </div>
          </a-row>
        </a-card>
      </a-col>
      <a-col :span="6">
        <router-link :to="{ name: 'MultiClassInspection' }">
          <a-card :bordered="false" style="border-top: 2px solid #42D668">
            <a-row>
              <div class="card">
                <svg-icon class="icon icon-live-broad" icon="icon-live-broad" />
                <div class="right-block">
                  <p class="card-title text-size">今日直播课程数</p>
                  <span class="card-count text-size" style="color: #42D668">{{ graphicData.todayLiveData.overLiveNum }}/<span style="color: #BEBEBE">{{ graphicData.todayLiveData.totalLiveNum }}</span></span>
                </div>
              </div>
            </a-row>
          </a-card>
        </router-link>
      </a-col>
      <a-col :span="6">
        <router-link :to="{ name: 'Overtime' }">
          <a-card :bordered="false" style="border-top: 2px solid #FF6666">
            <a-row>
              <div class="card">
                <svg-icon class="icon icon-edit-group" icon="icon-edit-group" />
                <div class="right-block">
                  <p class="card-title text-size">超时未批阅数</p>
                  <span class="card-count text-size" style="color: #FF6666">{{ graphicData.timeoutReviewNum }}</span>
                </div>
              </div>
            </a-row>
          </a-card>
        </router-link>
      </a-col>
    </a-row>
  </div>

</template>

<script>
export default {
  name: "dashboardCard",
  components: {},
  props: {
    graphicData: {
      type: Object,
      require: true,
    },
  },
  data() {
    return {};
  },

  computed: {},

  mounted() {},

  methods: {},
};
</script>
<style lang="less">
.dashboard-card {
  .card {
    white-space: nowrap;
  }
  .ant-card {
    background: #ffffff;
    box-shadow: 0 0 16px 0 rgba(0, 0, 0, 0.1);
  }
  .ant-card-body {
    padding: 20px 24px;
    .card-title {
      font-size: 16px;
      color: #999999;
      font-weight: 400;
    }
    .card-count {
      font-size: 28px;
      text-align: left;
      font-weight: 400;
    }
    .icon {
      position: relative;
      top: 8px;
      width: 80px;
      height: 80px;
      border-radius: 4px;
    }
    .icon-add-student {
      color: #5ccfea;
      border: 1px solid rgba(92, 207, 234, 0.2);
    }
    .icon-add-student-success {
      color: #ffc173;
      border: 1px solid rgba(255, 193, 115, 0.2);
    }
    .icon-live-broad {
      color: #42d668;
      border: 1px solid rgba(66, 214, 104, 0.2);
    }
    .icon-edit-group {
      color: #ff6666;
      border: 1px solid rgba(255, 102, 102, 0.2);
    }
    .right-block {
      display: inline-block;
      padding-left: 20%;
    }
  }

  @media screen and (max-width: 1750px) {
    .icon {
      width: 60px !important;
      height: 60px !important;
    }
    .text-size {
      font-size: 14px !important;
    }
    .right-block {
      padding-left: 10% !important;
    }
  }
  @media screen and (max-width: 1440px) {
    .icon {
      position: unset !important;
      width: 48px !important;
      height: 48px !important;
    }
    .text-size {
      font-size: 12px !important;
    }
    .right-block {
      padding-left: 5% !important;
    }
  }
}
</style>