import Api from '@/api/index';
// import fastLogin from "../fastLogin/index"
export default {
  // mixins: [fastLogin],
  data() {
    return {
      reportIntervalTime: null,
      reportFinishCondition: null,
      reportNowTime: null,
      reportData: {
        courseId: null,
        taskId: null
      },
      reportType: null
    };
  },
  methods: {
    initReportData(courseId, taskId, sourceType) {
      this.reportData = { courseId, taskId };
      this.reportType = sourceType;
    },
    // 获取任务信息
    getCourseData(courseId, taskId) {
      const param = { courseId, taskId };
      /* eslint-disable no-new */
      return Api.getCourseData({ query: param })
        .then(res => {
          this.reportFinishCondition = res.activity.finishCondition;
          return res;
        })
        .catch(err => {
          comsole.log(err);
        });
    },
    /**
     * 上报课时学习情况
     * @param {*} courseId
     * @param {*} taskId
     * @param {*} events  //doing finish
     */
    reprtData(events = 'doing') {
      const params = {
        courseId: this.reportData.courseId,
        taskId: this.reportData.taskId,
        events
      };
      /* eslint-disable no-new */
      Api.reportTask({ query: params })
        .then(res => {
          this.handleReprtResult(res);
          return res;
        })
        .catch(err => {
          console.log(err);
        });
    },
    handleReprtResult(res) {
      if (res.result.status === 'finish') {
        this.clearReportIntervalTime();
      }
    },
    /**
     * 1分钟上报一次
     */
    intervalReportData(min = 0.1) {
      const intervalTime = min * 60 * 1000;
      this.reportIntervalTime = setInterval(() => { this.reprtData('doing'); console.log(1); }, 1000);
      this.checkoutTime();
    },
    /**
     * 到达时间
     */
    checkoutTime() {
      if (this.reportFinishCondition.type === 'time') {
        if (reportNowTime > this.reportFinishCondition.time) {
          this.reprtData('finish');
        }
      }
    },
    clearReportIntervalTime() {
      clearInterval(this.reportIntervalTime);
      this.reportIntervalTime = null;
    }
  }
};
