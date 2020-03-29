import Api from '@/api/index';
import * as types from '@/store/mutation-types';
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
      isFinish: false,
      reportType: null
    };
  },
  methods: {
    initReportData(courseId, taskId, sourceType) {
      this.reportData = { courseId, taskId };
      this.reportType = sourceType;
      this.isFinish = false;
      this.reportIntervalTime = null;
      this.reportFinishCondition = null;
      this.reprtData();
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
          console.log(err);
        });
    },
    /**
     * 上报课时学习情况
     * @param {*} courseId
     * @param {*} taskId
     * @param {*} events  //doing finish
     */
    reprtData(events = 'doing') {
      if (this.isFinish) {
        return;
      }
      const params = {
        courseId: this.reportData.courseId,
        taskId: this.reportData.taskId,
        events
      };
      return new Promise((resolve, reject) => {
        Api.reportTask({ query: params })
          .then(res => {
            this.handleReprtResult(res);
            resolve(res);
          })
          .catch(err => {
            reject(err);
          });
      });
    },
    handleReprtResult(res) {
      if (res.result.status === 'finish') {
        this.clearReportIntervalTime();
        this.isFinish = true;
        this.$store.commit(types.SET_TASK_SATUS, 'finish');
      } else {
        this.$store.commit(types.SET_TASK_SATUS, 'start');
      }
    },
    /**
     * 1分钟上报一次
     */
    intervalReportData(min = 1) {
      const intervalTime = min * 60 * 1000;
      this.reportIntervalTime = setInterval(() => { this.reprtData('doing'); }, intervalTime);
      // this.checkoutTime();
    },
    /**
     * 到达时间
     */
    checkoutTime() {
      if (this.reportFinishCondition.type === 'time') {
        if (reportNowTime >= this.reportFinishCondition.time) {
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
