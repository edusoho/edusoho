/* eslint-disable */
import Api from "@/api/index";
import * as types from "@/store/mutation-types";

export default {
  data() {
    return {
      reportIntervalTime: null,//每分钟上报计时器
      reportLearnTime: null,//学习时长 计时器
      reportFinishCondition: null,//完成条件
      reportData: {
        courseId: null,
        taskId: null
      },
      reportResult:null,
      isFinish: false,//是否完成
      reportType: null,//上报类型
      learnTime: 0 // 学习时长
    };
  },
  beforeDestroy(){
    this.clearReportIntervalTime();
  },
  methods: {
    /**
     * 初始化上报数据
     * @param {*} courseId 
     * @param {*} taskId 
     * @param {*} sourceType  上报课程类型
     */
    initReportData(courseId, taskId, sourceType,reportNow=true) {
      this.clearReportIntervalTime();
      this.reportData = { courseId, taskId };
      this.reportType = sourceType;
      this.isFinish = false;
      this.reportIntervalTime = null;
      this.reportLearnTime = null;
      this.reportResult = null;
      this.learnTime=0;
      this.reportFinishCondition = null;
      if (reportNow) {
        this.initReportEvent();
      }
    },
    /**
     * 初始化上报所需方法
     */
    initReportEvent() {
      this.reprtData();
      this.intervalReportData();
      this.intervalReportLearnTime();
    },
    /**
     * 获取当前task信息
     * @param {*} courseId 
     * @param {*} taskId 
     */
    getCourseData(courseId, taskId) {
      const param = { courseId, taskId };
      return new Promise((resolve, reject) => {
        Api.getCourseData({ query: param })
          .then(res => {
            this.reportFinishCondition = res.activity.finishCondition;
            resolve(res);
          })
          .catch(err => {
            reject(err);
          });
      });
    },
    /**
     * 上报课时学习情况
     * @param {*} courseId
     * @param {*} taskId
     * @param {*} events  //doing finish
     *  @param {*} ContinuousReport //是否每间隔一分钟上报
     */
    reprtData(events = "doing",ContinuousReport=false,watchTime=null) {
      if (this.isFinish&&!ContinuousReport) {
        return;
      }
      let params = {};
      if (events === "doing") {
        if(this.reportResult!==null){
          let lastTime=this.reportResult.lastTime
          params = { lastTime };
        }
        if(watchTime){
          params.watchTime = watchTime ;
        }
      }

      const query = {
        courseId: this.reportData.courseId,
        taskId: this.reportData.taskId,
        events
      };
      return new Promise((resolve, reject) => {
        Api.reportTask({ query, data:params })
          .then(res => {
            this.handleReprtResult(res);
            resolve(res);
          })
          .catch(err => {
            reject(err);
          });
      });
    },
    /**
     * 课时finish后去做一些操作
     * @param {*} res 
     */
    handleReprtResult(res) {
      this.reportResult=res;
      if (res.result.status === "finish") {
        this.isFinish = true;
        this.$store.commit(types.SET_TASK_SATUS, "finish");
        this.$store.commit(`course/${types.UPDATE_PROGRESS}`, res.completionRate);
      } else {
        this.$store.commit(types.SET_TASK_SATUS, "start");
      }
    },
    intervalReportLearnTime() {
      this.reportLearnTime = setInterval(() => {
        this.checkoutTime();
        this.learnTime++;
      }, 1000);
    },
    /**
     * 1分钟上报一次
     */
    intervalReportData(min = 1) {
      const intervalTime = min * 60 * 1000;
      this.reportIntervalTime = setInterval(() => {
        this.reprtData("doing",true);
      }, intervalTime);
    },
    /**
     * 检验是否到达完成条件时间
     */
    checkoutTime() {
      if (!this.reportFinishCondition) {
        return;
      }
      if (this.reportFinishCondition.type === "time") {
        if (
          parseInt(this.learnTime / 60, 10) >=
          parseInt(this.reportFinishCondition.data, 10)
        ) {
          this.reprtData("finish");
        }
      }
    },
    /**
     * 清除定时器
     */
    clearReportIntervalTime() {
      clearInterval(this.reportIntervalTime);
      clearInterval(this.reportLearnTime);
      this.reportIntervalTime = null;
      this.reportLearnTime = null;
    }
  }
};
