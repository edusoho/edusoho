/* eslint-disable */
import Api from '@/api/index';
import * as types from '@/store/mutation-types';

export default {
  data() {
    return {
      reportIntervalTime: null, //每分钟上报计时器
      reportLearnTime: null, //学习时长 计时器
      reportFinishCondition: null, //完成条件
      reportData: {
        courseId: null,
        taskId: null,
      },
      reportResult: null,
      isFinish: false, //是否完成
      reportType: null, //上报类型
      learnTime: 0, // 学习时长
      isShowOutFocusMask: false, // 是否显示遮罩层
      outFocusMaskType: '', // 显示遮罩层的类型
      sign: '',
      record: {},
      isFull: false, // 遮罩层是否全屏
    };
  },
  beforeDestroy() {
    this.clearReportIntervalTime();
    document.removeEventListener('visibilitychange', this.visibilityState);
    if (this.sign.length > 0) {
      localStorage.setItem('flowSign', this.sign);
    }
  },
  methods: {
    /**
     * 初始化上报数据
     * @param {*} courseId
     * @param {*} taskId
     * @param {*} sourceType  上报课程类型
     */
    initReportData(courseId, taskId, sourceType, reportNow = true) {
      this.clearReportIntervalTime();
      this.reportData = { courseId, taskId };
      this.reportType = sourceType;
      this.isFinish = false;
      this.reportIntervalTime = null;
      this.reportLearnTime = null;
      this.reportResult = null;
      this.learnTime = 0;
      this.reportFinishCondition = null;
      if (reportNow) {
        this.initReportEvent();
      }
      if (this.reportType === 'video') {
        this.initVisibilitychange();
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
    reprtData(param = { ContinuousReport: false }) {
      if (
        this.reportData.courseId === null ||
        this.reportData.taskId === null
      ) {
        return;
      }

      if (this.isFinish && !param.ContinuousReport) {
        return;
      }

      if (this.sign === '') {
        let data = {
          client: 'h5',
        };
        let flowSign = localStorage.getItem('flowSign');

        if (flowSign) {
          data.lastSign = flowSign;
          localStorage.removeItem('flowSign');
        }

        this.start(data);
      } else {
        this.doing();
      }
    },

    start(data) {
      Api.reportTaskEvent({
        query: {
          courseId: this.reportData.courseId,
          taskId: this.reportData.taskId,
          eventName: 'start',
        },
        data,
      }).then(res => {
        // this.handleReprtResult(res);
        this.reportJudge(res);
        this.sign = res.record.flowSign;
        this.record = res.record;
        this.doing();
      });
    },

    doing() {
      if (this.sign.length === 0) {
        return;
      }
      let data = {
        client: 'h5',
        sign: this.sign,
        duration: this.learnTime,
      };
      Api.reportTaskEvent({
        query: {
          courseId: this.reportData.courseId,
          taskId: this.reportData.taskId,
          eventName: 'doing',
        },
        data: data,
      })
        .then(res => {
          this.handleReprtResult(res);
          this.record = res.record;
          this.learnTime = 0;
          this.reportJudge(res);
        })
        .catch(error => {
          this.clearReportIntervalTime();
        });
    },

    /**
     * 课时finish后去做一些操作
     * @param {*} res
     */
    handleReprtResult(res) {
      this.reportResult = res;
      if (res.taskResult.status === 'finish') {
        this.isFinish = true;
        this.$store.commit(types.SET_TASK_SATUS, 'finish');
        this.$store.commit(
          `course/${types.UPDATE_PROGRESS}`,
          res.completionRate,
        );
      } else {
        this.$store.commit(types.SET_TASK_SATUS, 'start');
      }
    },

    intervalReportLearnTime() {
      this.reportLearnTime = setInterval(() => {
        // this.checkoutTime();
        this.learnTime++;
      }, 1000);
    },

    /**
     * 1分钟上报一次
     */
    intervalReportData(min = 1) {
      const intervalTime = min * 60 * 1000;
      this.reportIntervalTime = setInterval(() => {
        this.reprtData({ ContinuousReport: true });
      }, intervalTime);
    },

    /**
     * 检验是否到达完成条件时间
     */
    checkoutTime() {
      if (!this.reportFinishCondition) {
        return;
      }
      if (this.reportFinishCondition.type === 'time') {
        if (
          parseInt(this.learnTime / 60, 10) >=
          parseInt(this.reportFinishCondition.data, 10)
        ) {
          this.reprtData('finish');
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
    },

    /**
     * 判断用户当前状态
     * @param {*} res 数据上报返回参数
     */
    reportJudge(res) {
      if (
        !res.learnControl.allowLearn &&
        res.learnControl.denyReason === 'kick_previous'
      ) {
        this.outFocusMaskShow('kick_previous');
      } else if (
        !res.learnControl.allowLearn &&
        res.learnControl.denyReason === 'reject_current'
      ) {
        this.outFocusMaskShow('reject_current');
      }
    },

    /**
     * 遮罩层关闭
     * @param {*} type
     */
    outFocusMask(type) {
      this.isShowOutFocusMask = false;
      this.reprtData({ ContinuousReport: false });

      if (this.player && this.reportType === 'video') {
        this.player.play();
      }

      document.body.style.overflow = '';
    },

    /**
     * 遮罩层显示
     * @param { String } type 遮罩层类型
     * ineffective_learning   // 无效学习
     * kick_previous          // 互踢，挤掉前面的
     * reject_current         // 互踢，不允许后来
     */
    outFocusMaskShow(type) {
      if (this.isShowOutFocusMask && type === 'ineffective_learning') {
        return;
      }
      this.isShowOutFocusMask = true;
      this.outFocusMaskType = type;
      this.reprtData({ ContinuousReport: true });

      if (this.player && this.reportType === 'video') {
        this.player.pause();
      }

      document.body.style.overflow = 'hidden';
    },

    /**
     * 监控 tab 切换最小化
     */
    initVisibilitychange() {
      document.addEventListener('visibilitychange', this.visibilityState);
    },

    visibilityState() {
      if (document.visibilityState === 'hidden') {
        this.outFocusMaskShow('ineffective_learning');
      }
    },
  },
};
