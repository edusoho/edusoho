<template>
  <div class="paper-swiper">
    <e-loading v-if="isLoading"></e-loading>

    <!-- 做题 -->
    <item-bank
      v-if="info.length>0"
      :current.sync="current"
      :info="info"
      :answer.sync="answer"
      :showScore="false"
    />

    <!-- 引导页 -->
    <guide-page />

    <!-- 底部 -->
    <div class="paper-footer">
      <div>
        <span @click="cardShow=true">
          <i class="iconfont icon-Questioncard"></i>
          题卡
        </span>
      </div>
      <div>
        <span @click="submitpaper">
          <i class="iconfont icon-submit"></i>
          提交
        </span>
      </div>
    </div>

    <!-- 答题卡 -->
    <van-popup v-model="cardShow" position="bottom">
      <div class="card" v-if="info.length>0">
        <div class="card-title">
          <div>
            <span class="card-finish">已完成</span>
            <span class="card-nofinish">未完成</span>
          </div>
          <i class="iconfont icon-no" @click="cardShow=false"></i>
        </div>
        <div class="card-list">
          <div class="card-homework-item">
            <div class="card-item-list" >
              <div
                v-for="cards in info" :key="cards.id"
                :class="['list-cicle',formatStatus(cards.type,cards.id)==1 ? 'cicle-active' :'']"
                @click="slideToNumber(cards.seq)"
              >{{cards.seq}}</div>
            </div>          
          </div>
        </div>
      </div>
    </van-popup>
  </div>
</template>

<script>
import Api from "@/api";
import * as types from "@/store/mutation-types";
import { mapState, mapMutations, mapActions } from "vuex";

import { Toast, Overlay, Popup, Dialog, Lazyload } from "vant";
import fillType from "../component/fill";
import essayType from "../component/essay";
import headTop from "../component/head";
import choiceType from "../component/choice";
import singleChoice from "../component/single-choice";
import determineType from "../component/determine";
import guidePage from "../component/guide-page";
import itemBank from "../component/itemBank";

import homeworkMixin from '@/mixins/lessonTask/homework.js';

let backUrl=''
export default {
  name: "homework-do",
  mixins: [homeworkMixin],
  data() {
    return {
      info: [], //作业信息
      answer: {}, //答案
      lastAnswer: null,
      current: 0, //滑动索引
      homework: null,
      cardShow: false, //答题卡显示标记
      localanswerName:null,
      localtimeName:null,
      lastUsedTime:null,
      lastAnswer:null,
      usedTime:null,//使用时间，本地实时计时
      isHandHomework:false
    };
  },
  components: {
    itemBank,
    guidePage
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading,
      user: state => state.user
    })
  },
  watch: {
    answer:{
      handler: 'saveAnswer',
      deep: true
    }
  },
  created() {
    this.getData();
  },
  beforeRouteEnter (to, from, next) {
      if(from.fullPath==="/"){
        backUrl="/"
      }else{
        backUrl=""
      }
      next();
  },
  beforeRouteLeave (to, from, next) { //可捕捉离开提醒
    if(this.info.length==0 || this.isHandHomework || this.homework.status!='doing'){
      next();
    }else{
      this.submitHomework().then(()=>{
          next();
      }).catch(()=>{
        next(false);
      });
    }
  },
  beforeDestroy() { //清除定时器
    this.clearTime();
    Dialog.close();
  },
  methods: {
    ...mapMutations({
      setNavbarTitle: types.SET_NAVBAR_TITLE
    }),
    ...mapActions('course', [
        'handHomeworkdo',
    ]),
    //请求接口获取数据
    getData() {
      let homeworkId = this.$route.query.homeworkId;
      let targetId = this.$route.query.targetId;
      let action = this.$route.query.action;
      Api.getHomeworkInfo({
        query: {
          homeworkId
        },
        data: {
          targetId,
          targetType: "task"
        }
      })
        .then(res => {
          this.afterGetData(res);
        })
        .catch(err => {
         const toast =  Toast.fail(err.message);
        /**
         * 4036706:试卷正在批阅中
         */
          if(err.code="4036706"){
              setTimeout(()=>{
                this.toIntro();
                toast.clear();
              },2000)
          }
        });
    },
    //获取到数据后进行操作
    afterGetData(res) {
      this.setNavbarTitle(res.paperName);

      this.homework = res;

      //判断是否做题状态
       if(this.isDoing()){
         return
       }

      this.getLocalData();

      this.formatData(res);

      this.interruption();

      this.saveTime();
    },
    //判断是否做题状态
    isDoing(){
      if(this.homework.status != 'doing'){
        //this.showResult();
        return true
      }else{
        return false
      }
    },
    //异常中断
    interruption(){
      if(!this.$route.params.KeepDoing){
          //异常中断或者刷新页面
            this.canDoing(this.homework,this.user.id).then(()=>{

            }).catch(({answer})=>{
              this.submitpaper(answer);
              return;
            })
      }
    },
    //遍历数据类型去做对应处理
    formatData(res) {
      let paper = res.items;
      let info = [];
      let answer = [];
      paper.forEach(item => {
        if (item.type != "material") {
          this.sixType(item.type, item);
        }
        if (item.type == "material") {
          let title = Object.assign({}, item, { subs: "" });
          item.subs.forEach((sub, index) => {
            sub.parentTitle = title; //材料题题干
            sub.parentType = item.type; //材料题题型
            sub.materialIndex = index + 1; //材料题子题的索引值，在页面要显示
            this.sixType(sub.type, sub);
          });
        }
      });
    },
    //处理六大题型数据
    sixType(type, item) {
      if (type == "single_choice") {
        //刷新页面或意外中断回来数据会丢失，因此要判断本地是否有缓存数据，如果有要把数据塞回
        if (this.lastAnswer) {
          this.$set(this.answer, item.id, this.lastAnswer[item.id]);
        } else {
          this.$set(this.answer, item.id, []);
        }
        this.info.push(item);
      }
      if (type == "choice" || type == "uncertain_choice") {
        if (this.lastAnswer) {
          this.$set(this.answer, item.id, this.lastAnswer[item.id]);
        } else {
          this.$set(this.answer, item.id, []);
        }
        this.info.push(item);
      }
      if (type == "essay") {
        if (this.lastAnswer) {
          this.$set(this.answer, item.id, this.lastAnswer[item.id]);
        } else {
          this.$set(this.answer, item.id, [""]);
        }
        this.info.push(item);
      }

      if (type == "fill") {
        let fillstem = item.stem;
        let { stem, index } = this.fillReplce(fillstem, 0);
        item.stem = stem;
        item.fillnum = index;
        if (this.lastAnswer) {
          this.$set(this.answer, item.id, this.lastAnswer[item.id]);
        } else {
          this.$set(this.answer, item.id, new Array(index).fill(""));
        }
        this.info.push(item);
      }

      if (type == "determine") {
        if (this.lastAnswer) {
          this.$set(this.answer, item.id, this.lastAnswer[item.id]);
        } else {
          this.$set(this.answer, item.id, []);
        }
        this.info.push(item);
      }
    },
    //处理富文本，并统计填空题的空格个数
    fillReplce(stem, index) {
      const reg = /\[\[.+?\]\]/;
      while (reg.exec(stem)) {
        stem = stem.replace(reg, () => {
          return `<span class="fill-bank">（${++index}）</span>`;
        });
      }
      return { stem, index };
    },
     //答题卡状态判断,finish 0是未完成  1是已完成
    formatStatus(type,id){
      let finish=0
      //单选题、多选题、不定项选择题、判断题
      if((type=="single_choice"||
          type=="choice" ||
          type=="uncertain_choice"||
          type=="determine") &&
          this.answer[id].length>0
        ){
          finish=1;
          return finish
      }
      //问答题
      if(type=="essay" &&  this.answer[id][0]!=''){
          finish=1;
          return finish
      }
      //填空题，规则：只要填了一个就算完成
      if(type=="fill"){
        finish=Number(this.answer[id].some(item=>{
          return item!=''
        }));
        return finish
      }
      return finish;
    },
    //答题卡定位
    slideToNumber(num){
      let index=Number(num);
      this.current=index;
      //关闭弹出层
      this.cardShow=false;
    },
    //获取本地数据
    getLocalData(){
        this.localanswerName=`${this.user.id}-${this.homework.id}`;
        this.localuseTime=`${this.user.id}-${this.homework.id}-usedTime`;
        this.lastAnswer=JSON.parse(localStorage.getItem(this.localanswerName));
    },
    //实时存储答案
    saveAnswer(val){
        localStorage.setItem(this.localanswerName, JSON.stringify(val));
    },
    //实时存储时间
    saveTime(){
        let time=localStorage.getItem(this.localuseTime) || 0;
        this.usedTime=setInterval(()=>{
              localStorage.setItem(this.localuseTime, ++time);
        },1000);
    },
    clearTime(){
      clearInterval(this.usedTime);
      this.usedTime = null;
    },
    //提交作业
    submitpaper(){
      let index=0;
      let message='题目已经做完，确认提交吗?'
      let answer=JSON.parse(JSON.stringify(this.answer))
      Object.keys(answer).forEach(key=>{
        //去除空数据
        answer[key]= answer[key].filter(t => t!=='')
        //统计未做个数
        if(answer[key].length===0){
          index++
        }
      })

      if(index>0){
         message=`还有${index}题未做，确认提交吗？`
      }
      return new Promise((resolve,reject)=>{
          Dialog.confirm({
            title: '提交',
            cancelButtonText:'立即提交',
            confirmButtonText:'检查一下',
            message: message
          }).then(() => {
            //显示答题卡
            this.cardShow=true;
            reject()
          })
          .catch(() => {
            this.clearTime();
           //提交作业
           this.submitHomework(answer).then(res=>{
              resolve();
            }).catch((err)=>{
                reject();
            })
          });
      })
    },
     //dispatch给store，提交答卷
    submitHomework(answer){
      //如果已经遍历过answer就不要再次遍历，直接用
      if(!answer){
        answer=JSON.parse(JSON.stringify(this.answer));
        Object.keys(answer).forEach(key=>{
            answer[key]= answer[key].filter(t => t!=='')
        })
      }

      let datas={
          answer,
          homeworkId:this.$route.query.homeworkId,
          userId:this.user.id,
          homeworkResultId:this.homework.id
      }

      return new Promise((resolve,reject)=>{
        this.handHomeworkdo(datas).then(res=>{
            this.isHandHomework=true;
             resolve();
            //跳转到结果页
            this.showResult();
          }).catch((err)=>{
              reject()
              Toast.fail(err.message);
          })
      })
    },
    //跳转到结果页
    showResult() {
      this.$router.replace({
        name: 'homeworkResult',
        query: {
          homeworkId: this.$route.query.homeworkId,
          homeworkResultId:this.homework.testId,
          backUrl:backUrl
        }
      })
    },
    //跳转到说明页
    toIntro(){
      this.$router.push({
          name: 'homeworkIntro',
          query: {
            courseId: this.$route.query.courseId,
            taskId: this.$route.query.targetId
        }
      })
    }
  }
};
</script>

<style>
</style>