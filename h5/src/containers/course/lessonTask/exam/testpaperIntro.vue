<template>
  <div>
    <e-loading v-if="isLoading"></e-loading>
    <div class="intro-body">
      <van-panel class="panel intro-panel" title="考试名称">
        <div class="intro-panel__content intro-panel__content--title">{{ testpaperTitle }}</div>
      </van-panel>
      <van-panel class="panel intro-panel" v-if=startTime title="开考时间">
        <div class="intro-panel__content intro-tip">{{ startTime|filterDate }}</div>
      </van-panel>
      <van-panel class="panel intro-panel" title="考试时长">
        <div class="intro-panel__content intro-tip" v-if=limitTime>{{ limitTime }}分钟</div>
        <div class="intro-panel__content" v-else>不限制</div>
      </van-panel>
      <van-panel class="panel intro-panel" title="试卷满分">
        <div class="intro-panel__content">满分{{ score }}分</div>
      </van-panel>
      <van-panel class="panel intro-panel" title="题目数量">
        <div class="intro-panel__content">
          <van-cell class="intro-cell intro-cell--total" :border=false title="共计" :value="sum|filterStr" />
          <van-cell class="intro-cell" :border=false v-for="(item) in question_type_seq" :key="item" :title="obj[item]" :value="counts[item]|filterStr" />
        </div>
      </van-panel>
    </div>
    <div class="intro-footer">
      <van-button class="intro-footer__btn" type="primary" v-if="result" @click="showResult">查看成绩</van-button>
      <van-button class="intro-footer__btn" type="primary" v-else @click="startTestpaper()" :disabled="disabled">开始考试</van-button>
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import { mapState,mapActions } from 'vuex';
import { Dialog } from "vant";
export default {
  name: 'testpaperIntro',
  data() {
    return {
      testpaper: null,    // 考试数据
      testpaperTitle: '', // 考试标题
      info: {},           // 考试类型说明，是否能重考相关信息
      startTime: null,    // 考试开始时间
      limitTime: null,    // 考试限制时间/分钟
      score: null,        // 考试满分
      total: 0,           // 考试题目总计数量
      testId: null,       // 考试试卷ID
      targetId: null,     // 任务ID
      counts: {},         // 考试题型数量对象
      result: {},         // 考试结果信息
      question_type_seq: [],  // 试卷已有题型
      obj: {
        "single_choice": '单选题',
        "choice": '多选题',
        "essay": '问答题',
        "uncertain_choice": '不定项选择题',
        "determine": '判断题',
        "fill": '填空题',
        "material": '材料题'
      },
    }
  },
  computed: {
    sum: function () {
      let sum = 0;
      for (let i in this.counts) {
        sum = sum + parseInt(this.counts[i]);
      }
      return sum;
    },
    disabled: function() {
      const nowTime = new Date().getTime();
      return this.startTime > nowTime ? true: false;
    },
    ...mapState({
      isLoading: state => state.isLoading,
      user:state => state.user
    }),
  },
  created() {
    this.getInfo();
  },
  filters: {
    filterStr(index) {
      const str = `${index}题`;
      return str;
    },
    filterDate(timestamp) {
      const dateObj = new Date(+timestamp)
      const year = dateObj.getFullYear();
      const month = dateObj.getMonth() + 1;
      const date = dateObj.getDate();
      const hours = dateObj.getHours();
      const minutes = dateObj.getMinutes();
      const dateNumFun = (num) => +num < 10 ? `0${num}` : num;
      const [Y, M, D, h, m, s] = [ year, dateNumFun(month), dateNumFun(date), dateNumFun(hours), dateNumFun(minutes)];
      return `${Y}年${M}月${D}日${h}:${m}`;
    }
  },
  methods: {
     ...mapActions('course', [
        'handExamdo',
       ]),
    getInfo() {
      this.testId = this.$route.query.testId;
      this.targetId = this.$route.query.targetId;
      Api.testpaperIntro({
        params: {
          targetId: this.targetId,
          targetType: 'task'
        },
        query: {
          testId: this.testId
        }
      }).then(res => {
        this.counts = res.items;
        this.testpaperTitle = res.task.title;
        this.testpaper = res.testpaper;
        this.score = this.testpaper.score;
        this.info = res.task.activity.testpaperInfo;
        this.startTime = parseInt(this.info.startTime) * 1000;
        this.limitTime = parseInt(this.info.limitTime);
        this.result = res.testpaperResult;
        this.question_type_seq = this.testpaper.metas.question_type_seq;
        this.hasDoing()
      });
    },
    hasDoing(){
      if(this.result && this.result.status=='doing'){
          //获取localstorge数据
         let answerName=`${this.user.id}-${this.result.id}`;
         let timeName=`${this.user.id}-${this.result.id}-time`;
         let answer=JSON.parse(localStorage.getItem(answerName));
         let time=Number(localStorage.getItem(timeName));
           if( time && answer){
             //过滤空数据
              Object.keys(answer).forEach(key=>{
                answer[key]= answer[key].filter(t => t!=='')
              })
              //如果有时间限制
              if(this.result.limitedTime){
                //超出时间限制
                let alUsed=Math.ceil((time-(this.result.beginTime*1000))/1000/60);
                if(alUsed>this.result.limitedTime){
                    let usedTime=Number(this.result.beginTime)+(Number(this.result.limitedTime*60*1000));
                    let datas={
                      answer,
                      resultId:this.result.id,
                      usedTime,
                      userId:this.user.id
                    }
                    //交卷+跳转到结果页
                    this.handExamdo(datas).then(res=>{
                      this.showResult()
                    });
                    return
                }
              }
              //没有时间限制 或者没有超出限制时间
              Dialog.confirm({
                    title: '提示',
                    cancelButtonText:'放弃考试',
                    confirmButtonText:'继续考试',
                    message: '您有未完成的考试，是否继续？'
                }).then(() => {
                    this.startTestpaper(true)
                })
                .catch(() => {
                    let datas={
                      answer,
                      resultId:this.result.id,
                      userId:this.user.id
                    }
                    //交卷+跳转到结果页
                    this.handExamdo(datas).then(res=>{
                      this.showResult()
                    });
                });
          }
      }
    },
    startTestpaper(uselocal) {
      let uselocalData=uselocal || false
      this.$router.push({
        name: 'testpaperDo',
        query: {
          testId: this.testId,
          targetId: this.targetId,
          uselocalData
        }
      })
    },
    showResult() {
      this.$router.push({
        name: 'testpaperResult',
        query: {
          resultId: this.result.id,
          doTimes: this.info.doTimes,
          redoInterval: this.info.redoInterval,
          targetId: this.targetId
        }
      })
    }
  },
  beforeRouteEnter(to, from, next) {
    document.getElementById("app").style.background="#f6f6f6"
    next()
  },
  beforeRouteLeave(to, from, next)  {
    document.getElementById("app").style.background=""
    next()
  }
}
</script>
