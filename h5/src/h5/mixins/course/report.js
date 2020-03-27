import Api from "@/api/course";
// import fastLogin from "../fastLogin/index"
export default {
  // mixins: [fastLogin],
  data(){
    return {
      reportIntervalTime:null
    }
  },
  methods: {
    //获取任务信息
    getCourseData(courseId, taskId) {
      const params = { courseId, taskId };
      new Promise((resolve, reject) => {
        Api.getCourseData({ query: params })
          .then(res => {
            resolve(res)
          })
          .catch(err => {
            reject(err)
          });
      });
    },
    /**
     * 上报课时学习情况
     * @param {*} courseId 
     * @param {*} taskId 
     * @param {*} events  //doing finish 
     */
    reprtData(courseId,taskId,events="doing"){
      const params = { courseId, taskId,events}
      new Promise((resolve, reject) => {
        Api.reportTask({ query: params })
          .then(res => {
            this.handleReprtResult(res);
            resolve(res)
          })
          .catch(err => {
            reject(err)
          });
      });
    },
    handleReprtResult(){
      if(res.result.status==='finish'){
        this.intervalReport();
      }
    },
    /**
     * 1分钟上报一次
     */
    intervalReport(min=1){
      const intervalTime=min*60*1000;
      this.reportIntervalTime=setInterval(this.reprtData(),intervalTime);
    },
    clearReportIntervalTime(){
      this.reportIntervalTime=null
    }
  }
};
