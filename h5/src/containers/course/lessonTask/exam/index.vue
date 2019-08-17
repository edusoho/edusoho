<template>
  <div class="paper-swiper">
    <e-loading v-if="isLoading"></e-loading>
    <van-swipe
      ref="swipe"
      :height="height"
      @change="changeswiper"
      :show-indicators="false"
      :loop="false"
      v-if="info.length>0"
    >
      <van-swipe-item v-for="(paper,index) in info" :key="paper.id">
        <div :ref="`paper${index}`">
          <head-top
            :all="info.length"
            :current="current+1"
            :subject="subject(paper)"
            :score="`${parseFloat(paper.score)}`"
          />

          <single-choice
            v-if=" paper.type=='single_choice' "
            :itemdata="paper"
            :answer="answer[paper.id]"
            :number="index+1"
            @singleChoose="singleChoose"
          />

          <choice-type 
            v-if=" paper.type=='choice' || paper.type=='uncertain_choice' "
            :itemdata="paper"
            :answer="answer[paper.id]"
            :number="index+1"
            @choiceChoose="choiceChoose"
          />
          
          <determine-type 
            v-if=" paper.type=='determine'"
            :itemdata="paper"
            :answer="answer[paper.id]"
            :number="index+1"
            @determineChoose="determineChoose"
          />

          <essay-type 
            v-if=" paper.type=='essay'"
            :itemdata="paper"
            :answer="answer[paper.id]"
            :number="index+1"
          />

          <fill-type 
            v-if=" paper.type=='fill'"
            :itemdata="paper"
            :answer="answer[paper.id]"
            :number="index+1"
          />

        </div>
      </van-swipe-item>
    </van-swipe>

    <!-- <div class="guide">
      <div class="guide__text">左右切换滑动</div>
      <div class="guide__gesture">
        <img src="static/images/leftslide.png"/>
        <img src="static/images/rightslide.png"/>
      </div>
    </div>-->

    <!-- 左右滑动按钮 -->
    <div>
      <div :class="['left-slide__btn',current==0 ?'slide-disabled':'']" @click="last()">
        <i class="iconfont icon-arrow-left"></i>
      </div>
      <div
        :class="['right-slide__btn',(current==info.length-1) ?'slide-disabled':'']"
        @click="next()"
      >
        <i class="iconfont icon-arrow-right"></i>
      </div>
    </div>

    <!-- 底部 -->
    <div class="paper-footer">
      <div>
        <span @click="cardShow=true">
          <i class="iconfont icon-Questioncard"></i>
          题卡
        </span>
      </div>
      <div>
        <span @click="submitPaper()">
        <i class="iconfont icon-submit"></i>
        交卷
        </span>
      </div>
    </div>

    <!-- 答题卡 -->
    <van-popup
      v-model="cardShow"
      position="bottom"
    >
      <div class="card" v-if="info.length>0">
        <div class="card-title">
          <div>
              <span class="card-finish">已完成</span>
              <span class="card-nofinish">未完成</span>
          </div>
          <i class="iconfont icon-no" @click="cardShow=false"> </i>
        </div>
        <div class="card-list">
          <div class="card-item" v-for="(cards,name) in items" :key="name">
            <div class="card-item-title">{{name | type}}</div>
            <div class="card-item-list" v-if="name!='material'">
              <div 
               :class="['list-cicle',formatStatus(craditem.type,craditem.id)==1 ? 'cicle-active' :'']" 
               v-for="(craditem) in items[name]" 
               :key="craditem.id" @click="slideToNumber(craditem.seq)">
               {{craditem.seq}}
               </div>
            </div>
             <div class="card-item-list" v-if="name=='material'">
               <template v-for="(craditem) in items[name]" >
                 <div 
                 :class="['list-cicle',formatStatus(materialitem.type,materialitem.id)==1 ? 'cicle-active' :'']" 
                 v-for="(materialitem) in craditem.subs" 
                 :key="materialitem.id" @click="slideToNumber(materialitem.seq)">
                 {{materialitem.seq}}
                </div>
               </template>
            </div>
          </div>
        </div> 
      </div>
    </van-popup>

    <!-- 考试时间 -->
    <div :class="['time',timeWarn ? 'warn' : '']" >
        {{time}}
    </div>
  </div>
</template>

<script>
const WINDOWHEIGHT = document.documentElement.clientHeight - 44;
import Api from "@/api";
import { mapState, mapMutations , mapActions} from "vuex";
import * as types from "@/store/mutation-types";
import { Toast,Overlay,Popup,Dialog } from "vant";

import fillType from "../component/fill";
import essayType from "../component/essay";
import headTop from "../component/head";
import choiceType from "../component/choice";
import singleChoice from "../component/single-choice";
import determineType from "../component/determine";
import { resolve } from 'url';
export default {
  name: "testpaperDo",
  data() {
    return {
      height: 0,//滑动卡片当前高度
      current: 0,//滑动索引
      testpaper: {},
      testpaperResult: {},
      info: [], //试卷信息
      answer: {}, //答案
      cardShow:false,//答题卡显示标记
      card:{},//答题卡
      items:{},//分组题目
      time:null,//倒计时
      timeMeter:null,//计时器
      timeWarn:false,//倒计时警告标记
      isHandExam:false,//是否已交卷
      localtime:null,//本地时间计时器
      localtimeName:null,//本地存储的时间key值
      localanswerName:null,//本地存储的答案key值
      lastAnswer:null,//本地存储的答案
      lastTime:null,//本地存储的时间
      uselocalData:false//是否沿用本地数据
    };
  },
  created() {
    this.uselocalData=this.$route.query.uselocalData;
    this.getData();
  },
  components: {
    fillType,
    essayType,
    headTop,
    choiceType,
    singleChoice,
    determineType,
    vanOverlay:Overlay
  },
  filters:{
    type:function(type){
      switch (type) {
        case "single_choice":
          return "单选题";
          break;
        case "choice":
          return "多选题";
          break;
        case "essay":
          return "问答题";
          break;
        case "uncertain_choice":
          return "不定项选择题";
          break;
        case "determine":
          return "判断题";
          break;
        case "fill":
          return "填空题";
          break;
        case "material":
          return "材料题";
          break;
      }
    }
  },
  mounted() {
    this.$nextTick(() => {
      this.height = WINDOWHEIGHT;
    });
  },
  beforeRouteLeave (to, from, next) { //可捕捉离开提醒
    if(this.info.length==0){
      next();
    }else{
      this.submitPaper().then(()=>{
          next();
      }).catch(()=>{
        next(false);
      });
    }
  },
  beforeDestroy() { //清楚定时器
    this.clearTime();
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading,
      user:state => state.user
    }),
  },
  watch: {
    answer:{
      handler: 'saveAnswer',
      immediate: true,
      deep: true
    },
  },
  methods: {
    ...mapActions('course', [
        'handExamdo',
    ]),
    //废弃暂存
    getlocalData(){
       // this.lastAnswer=JSON.parse(localStorage.getItem(this.localanswerName));


      // let answer=this.lastAnswer;
      // let time=this.lastTime;

      // if( time && answer){
      //     //如果有时间限制
      //     if(this.testpaperResult.limitedTime){
      //       //超出时间限制
      //       let alUsed=Math.ceil((time-(this.testpaperResult.beginTime*1000))/1000/60);
      //       if(alUsed>this.testpaperResult.limitedTime){
      //           //交卷
      //           Object.keys(answer).forEach(key=>{
      //             answer[key]= answer[key].filter(t => t!=='') 
      //           })
      //           let usedTime=this.testpaperResult.beginTime+this.testpaperResult.limitedTime*60;
      //           this.handExamdo(answer,usedTime);
      //           return
      //       } 
      //     }

      //     //没有时间限制 或者没有超出限制时间
      //     this.costTime=time;

      //     Dialog.confirm({
      //           title: '提示',
      //           cancelButtonText:'放弃考试',
      //           confirmButtonText:'继续考试',
      //           message: '您有未完成的考试，是否继续？'
      //       }).then(() => {
      //         Object.keys(answer).forEach(key=>{
      //           answer[key].forEach((item,index)=>{
      //               this.$set(this.answer[key],index,item)
      //           })
      //         })
      //       })
      //       .catch(() => {
      //           //交卷
      //           Object.keys(answer).forEach(key=>{
      //             answer[key]= answer[key].filter(t => t!=='') 
      //           })
      //           this.handExamdo(answer)
      //       });  
      // }
    },
    getData() {
      let testId=this.$route.query.testId;
      let targetId=this.$route.query.targetId;
      Api.getExamInfo({
        query: {
          testId
        },
        data: {
          action: "do",
          targetId: 666,
          targetType: "task"
        }
      })
        .then(res => {
          //设置导航栏题目
          this.$store.commit(types.SET_NAVBAR_TITLE,res.testpaper.name)
          //赋值数据
          this.items=res.items;
          this.testpaper = res.testpaper;
          this.testpaperResult = res.testpaperResult;

          this.localanswerName=`${this.user.id}-${this.testpaperResult.id}`;
          this.localtimeName=`${this.user.id}-${this.testpaperResult.id}-time`;
          this.lastTime=localStorage.getItem(this.localtimeName);
          this.lastAnswer=JSON.parse(localStorage.getItem(this.localanswerName));

          //处理数据格式
          this.formatData(res);

          //设置首页高度
          this.changeswiper(0)

          //本地实时存储时间
          this.saveTime();

          //如果有限制考试时长，开始计时
          if(this.testpaperResult.limitedTime){
              this.timer();
          }
        })
        .catch(err => {
          Toast.fail(err.message);
        });
    },
    //遍历数据类型去做对应处理
    formatData(res) {
      let paper = res.items;
      let info = [];
      let answer = [];
      Object.keys(paper).forEach(key => {
        if (key != "material") {
          paper[key].forEach(item => {
            this.sixType(item.type, item);
          });
        }
        if (key == "material") {//材料题下面有子题需要特殊处理
          paper[key].forEach(item => {
            let title = Object.assign({}, item, { subs: "" });
            item.subs.forEach((sub, index) => {
              sub.parentTitle = title;//子题的父级题干
              sub.parentType = item.type;//子题的父级题型
              sub.materialIndex = index+1;//子题的索引值
              this.sixType(sub.type, sub);
            });
          });
        }
      });
    },
    //处理六大题型数据
    sixType(type, item) {
      if (type == "single_choice") {
        let length = item.metas.choices.length;
        //刷新页面或意外中断回来要回显，因此要判断本地是否有缓存
        if(this.lastAnswer){
          this.$set(this.answer,item.id,this.lastAnswer[item.id])
        }else{
          this.$set(this.answer,item.id,[])
        }
        this.info.push(item);
      }
      if (type == "choice" || type == "uncertain_choice") {
        if(this.lastAnswer){
          this.$set(this.answer,item.id,this.lastAnswer[item.id])
        }else{
          this.$set(this.answer,item.id,[])
        }
        this.info.push(item);
      }
      if (type == "essay") {
        if(this.lastAnswer){
          this.$set(this.answer,item.id,this.lastAnswer[item.id])
        }else{
          this.$set(this.answer,item.id,[""])
        }
        this.info.push(item);
      }

      if (type == "fill") {
        let fillstem = item.stem;
        let { stem, index } = this.fillReplce(fillstem, 0);
        item.stem = stem;
        item.fillnum = index;
        if(this.lastAnswer){
          this.$set(this.answer,item.id,this.lastAnswer[item.id])
        }else{
          this.$set(this.answer,item.id,new Array(index).fill(""))
        }
        this.info.push(item);
      }

      if (type == "determine") {
        if(this.lastAnswer){
          this.$set(this.answer,item.id,this.lastAnswer[item.id])
        }else{
          this.$set(this.answer,item.id,[])
        }
        this.info.push(item);
      }
    },
    //处理富文本，并统计填空题的空格个数
    fillReplce(stem, index) {
      const reg = /\[\[.+?\]\]/;
      while (reg.exec(stem)) {
        stem = stem.replace(reg, () => {
          return `<span>${++index}</span>`;
        });
      }
      return { stem, index };
    },
    //由于swiper的高度无法自适应内容高度，所以切换页面要动态更改索引和设置高度
    changeswiper(index) {
      this.current = index;
      this.$nextTick(() => {
        let docHeight = window.getComputedStyle(this.$refs[`paper${index}`][0])
          .height;
        let heights = Math.max(
          Number(docHeight.substring(0, docHeight.length - 2))
        );
        if (heights == this.height) {
          return;
        }
        if (heights <= WINDOWHEIGHT) {
          this.height = WINDOWHEIGHT;
          return;
        }
        this.height = heights;
      });
    },
    //左滑动
    last() {
      if (this.current == 0) {
        return;
      }
      this.$refs.swipe.swipeTo(this.current - 1);
    },
    //右滑动
    next() {
      if (this.current == this.info.length - 1) {
        return;
      }
      this.$refs.swipe.swipeTo(this.current + 1);
    },
    //题目类型过滤
    subject(paper) {
      let type;
      if (paper.parentType) {
        type = paper.parentType;
      } else {
        type = paper.type;
      }
      switch (type) {
        case "single_choice":
          return "单选题";
          break;
        case "choice":
          return "多选题";
          break;
        case "essay":
          return "问答题";
          break;
        case "uncertain_choice":
          return "不定项选择题";
          break;
        case "determine":
          return "判断题";
          break;
        case "fill":
          return "填空题";
          break;
        case "material":
          return "材料题";
          break;
      }
    },
    //单选题选择
    singleChoose(name, id) {
      this.$set(this.answer[id], 0, name);
    },
    //多选题和不定项选择
    choiceChoose(name, id){
      this.$set(this.answer, id, name);
    },
    //判断题选择
    determineChoose(name, id){
      this.$set(this.answer[id], 0, Number(name));
    },
    //答题卡状态判断
    formatStatus(type,id){
      let finish=0
      if((type=="single_choice"|| type=="choice" || type=="uncertain_choice"|| type=="determine") && this.answer[id].length>0){
          finish=1;
          return finish
      }
      if(type=="essay" &&  this.answer[id][0]!=''){
          finish=1;
          return finish
      }
      if(type=="fill"){
        finish=Number(this.answer[id].every(item=>{
          return item!=''
        }));
        return finish
      }
      return finish;
    },
    //答题卡定位
    slideToNumber(num){
      let index=Number(num);
      this.$refs.swipe.swipeTo(index-1);
      //关闭弹出层
      this.cardShow=false;
    },
    //考试倒计时
    timer(timeStr){
        let i=0;
        
        let time=this.testpaperResult.limitedTime

        //如果考试过程中中断，剩余时间=考试限制时间-中断时间
        if(this.lastTime){
          let gotime=Math.ceil((Number(this.lastTime)-this.testpaperResult.beginTime*1000)/1000/60);
          if(gotime>this.testpaperResult.limitedTime){
            return
          }
          time=this.testpaperResult.limitedTime-gotime;
        }

        this.timeMeter =setInterval(()=>{
            let nowTime = (Number(time) * 60 * 1000)-(i++ * 1000);
            let minutes = parseInt(nowTime / 1000 / 60 % 60, 10);//计算剩余的分钟
            let seconds = parseInt(nowTime / 1000 % 60, 10);//计算剩余的秒数
            minutes = this.checkTime(minutes);
            seconds = this.checkTime(seconds);
            let hours = parseInt(nowTime / ( 1000 * 60 * 60), 10); //计算剩余的小时
            hours = this.checkTime(hours);
            this.time=`${hours}:${minutes}:${seconds}`
            if(hours==0 && minutes==0 && seconds<60){
              this.timeWarn=true
            }
            if(hours==0 && minutes==0 && seconds==0){
              //直接交卷
              let answer=JSON.parse(JSON.stringify(this.answer))
              Object.keys(answer).forEach(key=>{
                //去除空数据
                answer[key]= answer[key].filter(t => t!=='')
              })
              let datas={
                answer,
                resultId:this.testpaperResult.id,
                userId:this.user.id
              }
              this.handExamdo(answer).then(res=>{
                this.clearTime();
                //跳转到结果页
                this.showResult();
              }).catch((err)=>{
                Toast.fail(err.message);
              })
            }
        },1000);
    },
    //清空定时器
    clearTime(){
      clearInterval(this.timeMeter);        
      this.timeMeter = null;

      clearInterval(this.localtime);        
      this.localtime = null;
    },
    checkTime(i) { //将0-9的数字前面加上0，例1变为01
        if (i < 10) {
            i = "0" + i;
        }
        return i;
    },
    //提交试卷
    submitPaper(){
      let index=0;
      let message='确认交卷?'
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
         message=`还有题${index}未做，确认交卷吗？`
      }
      return new Promise((resolve,reject)=>{
          Dialog.confirm({
            title: '交卷',
            cancelButtonText:'确认交卷',
            confirmButtonText:'检查一下',
            message: message
          }).then(() => { reject()})
          .catch(() => {
            this.clearTime();

            let datas={
              answer,
              resultId:this.testpaperResult.id,
              userId:this.user.id
            }
            this.handExamdo(datas).then(res=>{
              resolve();
              //跳转到结果页
              this.showResult();
            }).catch((err)=>{
                Toast.fail(err.message);
            })
          });
      }) 
    },
    //实时存储答案
    saveAnswer(val){
        localStorage.setItem(this.localanswerName, JSON.stringify(val));
    },
    //实时存储时间
    saveTime(){
        this.localtime=setInterval(()=>{
            localStorage.setItem(this.localtimeName, new Date().getTime());
        },1000);
    },
    //跳转到结果页
    showResult() {
      this.$router.push({
        name: 'testpaperResult',
        params: {
          resultId: this.testpaperResult.id,
         // testpaperInfo: this.info,
          targetId:this.targetId
        }
      })
    }
  }
};
</script>
