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
      absorbed: 0, // 是否无效学习
    };
  },
  beforeDestroy() {
    this.clearReportIntervalTime();
    document.removeEventListener('visibilitychange', this.visibilityState);

    if (this.sign.length > 0) {
      localStorage.setItem('flowSign', this.sign);
    }

    this.toggleReportMaskHidden('remove');
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
    reprtData(param = { eventName: 'doing', ContinuousReport: false }) {
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

        this.start(param, data);
      } else {
        this.reportTaskEvent(param);
      }
    },

    start(param, data) {
      Api.reportTaskEvent({
        query: {
          courseId: this.reportData.courseId,
          taskId: this.reportData.taskId,
          eventName: 'start',
        },
        data,
      }).then(res => {
        this.handleReportResult(res);

        if (!res.learnControl.allowLearn) {
          let status = res.learnControl.denyReason;
          this.reportJudge(status);
          return;
        }

        this.sign = res.record.flowSign;
        this.record = res.record;
        this.reportTaskEvent(param);
      });
    },

    reportTaskEvent(param) {
      if (this.sign.length === 0) {
        return;
      }
      let data = {
        client: 'h5',
        sign: this.sign,
        duration: this.learnTime,
        status: this.absorbed,
      };
      if (param.reActive) {
        data.reActive = param.reActive;
      }
      Api.reportTaskEvent({
        query: {
          courseId: this.reportData.courseId,
          taskId: this.reportData.taskId,
          eventName: param.eventName,
        },
        data: data,
      })
        .then(res => {
          this.handleReportResult(res);
          this.record = res.record;
          this.learnTime = 0;

          if (res.learnControl.allowLearn) return;
          let status = res.learnControl.denyReason;
          this.reportJudge(status);
        })
        .catch(error => {
          this.clearReportIntervalTime();
        });
    },

    /**
     * 课时finish后去做一些操作
     * @param {*} res
     */
    handleReportResult(res) {
      this.reportResult = res;
      if (res.taskResult && res.taskResult.status === 'finish') {
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
        this.reprtData({ eventName: 'doing', ContinuousReport: true });
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
          this.reprtData({ eventName: 'finish', ContinuousReport: true });
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

    reportJudge(status) {
      if (status === 'kick_previous') {
        this.kickEachOther('kick_previous');
      } else if (status === 'reject_current') {
        this.clearReportIntervalTime();
        this.kickEachOther('reject_current');
      }
    },

    /**
     * 遮罩层关闭
     * @param {*} type
     */
    outFocusMask(type) {
      this.absorbed = 0;
      this.isShowOutFocusMask = false;
      if (this.player && this.reportType === 'video') {
        this.player.play();
      }

      this.toggleReportMaskHidden('remove');

      this.reprtData({
        eventName: 'doing',
        ContinuousReport: true,
        reActive: 1,
      });
    },

    /**
     * 互踢
     * @param {*} type
     * kick_previous          // 互踢，挤掉前面的
     * reject_current         // 互踢，不允许后来
     */
    kickEachOther(type) {
      this.outFocusMaskShow(type);
    },

    /**
     * 显示 无效学习 遮罩层
     * @param {*} type ineffective_learning
     */
    ineffectiveLearning(type) {
      if (this.isShowOutFocusMask) {
        return;
      }
      this.outFocusMaskShow(type);
      this.reprtData({ eventName: 'doing', ContinuousReport: true });
    },

    outFocusMaskShow(type) {
      this.absorbed = 1;
      this.isShowOutFocusMask = true;
      this.outFocusMaskType = type;
      if (this.player && this.reportType === 'video') {
        this.player.pause();
      }

      this.toggleReportMaskHidden('add');
    },

    toggleReportMaskHidden(type) {
      if (this.reportType === 'video' || this.reportType === 'audio') {
        return;
      }
      if (type === 'add') {
        document
          .getElementsByTagName('body')[0]
          .classList.add('report-mask-hidden');
      } else if (type === 'remove') {
        document
          .getElementsByTagName('body')[0]
          .classList.remove('report-mask-hidden');
      }
    },

    initVisibilitychange() {
      document.addEventListener('visibilitychange', this.visibilityState);
    },

    visibilityState() {
      if (this.reportType !== 'video') return;
      if (document.visibilityState === 'hidden') {
        this.ineffectiveLearning('ineffective_learning');
      } else if (document.visibilityState === 'visible') {
        this.player.pause();
      }
    },
  },
};
