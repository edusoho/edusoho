<template>
  <div>
    <e-loading v-if="isLoading"></e-loading>
    <div class="intro-body" >
      <van-panel class="panel intro-panel" title="考试名称">
        <div class="intro-panel__content intro-panel__content--title">{{ testpaperTitle }}</div>
      </van-panel>
      <van-panel class="panel intro-panel" v-if=startTime title="开考时间">
        <div :class="['intro-panel__content',(result || !disabled) ?'':'intro-tip']">{{ formateStartTime(startTime) }}</div>
      </van-panel>
      <van-panel class="panel intro-panel" title="考试时长">
        <div :class="['intro-panel__content',(result || !disabled) ?'':'intro-tip']" v-if=limitTime>{{ limitTime }}分钟</div>
        <div class="intro-panel__content" v-else>不限制</div>
      </van-panel>
      <van-panel class="panel intro-panel" title="试卷满分">
        <div class="intro-panel__content">满分{{ score }}分</div>
      </van-panel>
      <van-panel class="panel intro-panel" title="题目数量">
        <div class="intro-panel__content">
          <van-cell class="intro-cell intro-cell--total" :border=false title="共计" :value="`${sum}题`" />
          <van-cell class="intro-cell" :border=false v-for="(item) in question_type_seq" :key="item" :title="obj[item]" :value="`${counts[item]}题`" />
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
import { formatTime } from '@/utils/date-toolkit.js'
import { Promise } from 'q';
import examMixin from '@/mixins/lessonTask/exam.js';
export default {
  name: 'testpaperIntro',
  mixins: [examMixin],
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
      result: null,         // 考试结果信息
      question_type_seq: [],  // 试卷已有题型
      answerName:null,
      timeName:null,
      answer:null,
      time:null,
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
    sum() {
      let sum = 0;
      for (let i in this.counts) {
        sum = sum + parseInt(this.counts[i]);
      }
      return sum;
    },
    disabled() {
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
    // filterStr(index) {
    //   const str = `${index}题`;
    //   return str;
    // },
    // filterDate(timestamp) {
    //   const dateObj = new Date(+timestamp)
    //   const year = dateObj.getFullYear();
    //   const month = dateObj.getMonth() + 1;
    //   const date = dateObj.getDate();
    //   const hours = dateObj.getHours();
    //   const minutes = dateObj.getMinutes();
    //   const dateNumFun = (num) => +num < 10 ? `0${num}` : num;
    //   const [Y, M, D, h, m, s] = [ year, dateNumFun(month), dateNumFun(date), dateNumFun(hours), dateNumFun(minutes)];
    //   return `${Y}年${M}月${D}日${h}:${m}`;
    // }
  },
  beforeRouteEnter(to, from, next) {
    document.getElementById("app").style.background="#f6f6f6"
    next()
  },
  beforeRouteLeave(to, from, next)  {
    document.getElementById("app").style.background=""
    next()
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
        this.result = res.testpaperResult;
        this.info = res.task.activity.testpaperInfo;

        this.score = this.testpaper.score;
        this.startTime = parseInt(this.info.startTime) * 1000;
        this.limitTime = parseInt(this.info.limitTime);
        this.question_type_seq = this.testpaper.metas.question_type_seq;

        this.canDoing(this.result,this.user.id).then(()=>{
          this.startTestpaper();
        }).catch(({answer,endTime})=>{
          this.submitExam(answer,endTime)
        })
      });
    },
    submitExam(answer,endTime){
        endTime= endTime ? endTime : new Date().getTime()
        let datas={
            answer,
            resultId:this.result.id,
            userId:this.user.id,
            beginTime:Number(this.result.beginTime),
            endTime
        }
        //交卷+跳转到结果页
        this.handExamdo(datas).then(res=>{
            this.showResult()
        }).catch((err)=>{
            Toast.fail(err.message);
        });
    },
    startTestpaper() {
      this.$router.push({
        name: 'testpaperDo',
        query: {
          testId: this.testId,
          targetId: this.targetId,
          action:'do'
        },
        params:{
          KeepDoing:true
        }
      })
    },
    showResult() {
      this.$router.push({
        name: 'testpaperResult',
        query: {
          resultId: this.result.id,
          testId:this.testId,
          targetId: this.targetId,
        }
      })
    },
    //开考时间
    formateStartTime(startTime) {
      startTime = formatTime(new Date(startTime));
      return startTime;
    }
  }
}
</script>
