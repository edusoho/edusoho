import Api from '@/api';
import Event from './event';

const DEFAULT_OPTIONS = {
  reportMap: {
    doing: 'reportTaskDoing',
    finish: 'reportTaskFinish',
    testResult: 'testpaperResult',
    courseData: 'getCourseData',
    courseList: 'getCourseLessons',
    learningProcess: 'getNextStudy'
  },
  learnTimeSec: 60,
  reportData: {},
  formatReportData: data => data
};

const FINISH_JUDGEMENT = {
  end: () => true,
  time: (time, limit) => time >= limit
};

class TaskPipe extends Event {
  constructor(options) {
    super(options);
    this.delta = 0;
    this.options = Object.assign(DEFAULT_OPTIONS, options);
    this.reportData = this.options.reportData;
    this.startDate = Date.now();
    this.getReportData('courseData').then(res => {
      const limitData = res.activity && res.activity.finishCondition;

      if (!limitData) {
        return;
      }
      this.setEndLimit(limitData);
    });
  }

  getDuration() {
    return Date.now() - this.startDate;
  }

  setEndLimit(data) {
    this.on(data.type, currentData => {
      if (FINISH_JUDGEMENT[data.type](data.data, currentData)) {
        this.flush('finish');
      }
    });
  }

  setReportParams(data) {
    return Object.assign(this.reportData, data);
  }

  initInterval() {
    window.onbeforeunload = () => {
      this.clearInterval();
      this.flush();
    };
    this.clearInterval();
    this.intervalId = this.waittingExecute(() => this.flush(), this.options.learnTimeSec * 1000);
  }

  clearInterval() {
    if (this.timer) {
      clearTimeout(this.timer);
      this.timer = null;
    }
  }

  getReportData(state, data) {
    this.setReportParams(data);
    return Api[this.options.reportMap[state]]({
      query: this.reportData,
      data: this.options.formatReportData(data)
    }).then(res => {
      this.trigger(state, res);
      return res;
    });
  }

  flush(state = 'doing', data = {}, param = {}) {
    if (state === 'finish') {
      this.clearInterval();
    }
    this.getReportData(state, { lastTime: this.lastTime, ...data }).then(res => {
      this.trigger(state, res);
      if (res.lastTime) {
        this.lastTime = res.lastTime;
      }
      if (res.result && res.result.status) {
        if (param.data) {
          res.playerMsg = param.data.playerMsg;
        }
        if (res.result.status === 'finish') {
          this.clearInterval();
        }
        this.trigger(res.result.status, res);
      }
    }).catch(error => {
      if (error.status === 403) {
        this.clearInterval();
        this.trigger('error', error);
      }
    });
  }

  waittingExecute(cb, time) {
    this.triggerDate = Date.now();
    cb();
    this.timer = setTimeout(() => {
      this.clearInterval();
      this.delta = Date.now() - this.triggerDate - time;
      this.waittingExecute(cb, time);
    }, time - this.delta);
  }
}

export default TaskPipe;
