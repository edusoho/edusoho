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
import { mapState, mapMutations } from "vuex";
import * as types from "@/store/mutation-types";
import { Toast,Overlay,Popup,Dialog } from "vant";

import fillType from "../component/fill";
import essayType from "../component/essay";
import headTop from "../component/head";
import choiceType from "../component/choice";
import singleChoice from "../component/single-choice";
import determineType from "../component/determine";
export default {
  name: "exam",
  data() {
    return {
      height: 0,
      current: 0,
      testpaper: {},
      testpaperResult: {},
      info: [], //试卷信息
      answer: {}, //答案
      cardShow:false,
      allType:[],//所有的题目类型
      card:{},//答题卡
      items:{},//分组题目
      time:null,//倒计时
      timeMeter:null,//计时器
      timeWarn:false,//倒计时警告标记
      isHandExam:false,//是否已交卷
      localtime:null,//本地时间计时器
      localtimeName:null,
      localanswerName:null,
      lastAnswer:null,
      lastTime:null,
      costTime:null //异常中断花费时间
    };
  },
  created() {
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
  beforeRouteLeave (to, from, next) {
    if(this.isHandExam){
      next();
    }else{
      this.submitPaper();
      next();
       // this.submitPaper()
    }
  },
  beforeDestroy() {
    clearInterval(this.timeMeter);        
    this.timeMeter = null;

    clearInterval(this.localtime);        
    this.localtime = null;
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
    getlocalData(){
      let answer=this.lastAnswer;
      let time=this.lastTime;

      if( time && answer){
          //如果有时间限制
          if(this.testpaperResult.limitedTime){
            //超出时间限制
            let alUsed=Math.ceil((time-(this.testpaperResult.beginTime*1000))/1000/60);
            if(alUsed>this.testpaperResult.limitedTime){
                //交卷
                Object.keys(answer).forEach(key=>{
                  answer[key]= answer[key].filter(t => t!=='') 
                })
                let usedTime=this.testpaperResult.beginTime+this.testpaperResult.limitedTime*60;
                this.handExamdo(answer,usedTime);
                return
            } 
          }

          //没有时间限制 或者没有超出限制时间
          this.costTime=time;

          Dialog.confirm({
                title: '提示',
                cancelButtonText:'放弃考试',
                confirmButtonText:'继续考试',
                message: '您有未完成的考试，是否继续？'
            }).then(() => {
              Object.keys(answer).forEach(key=>{
                answer[key].forEach((item,index)=>{
                    this.$set(this.answer[key],index,item)
                })
              })
            })
            .catch(() => {
                //交卷
                Object.keys(answer).forEach(key=>{
                  answer[key]= answer[key].filter(t => t!=='') 
                })
                this.handExamdo(answer)
            });  
      }
    },
    getData() {
      let testId = 16;
      Api.getExamInfo({
        query: {
          testId
        },
        data: {
          action: "redo",
          targetId: 666,
          targetType: "task"
        }
      })
        .then(res => {
          //设置导航栏题目
          this.$store.commit(types.SET_NAVBAR_TITLE,res.testpaper.name)
          //处理数据
          this.items=res.items;
          this.testpaper = res.testpaper;
          this.testpaperResult = res.testpaperResult;

          this.localanswerName=`${this.user.id}-${this.testpaper.id}`;
          this.localtimeName=`${this.user.id}-${this.testpaper.id}-time`;
          this.lastAnswer=JSON.parse(localStorage.getItem(this.localanswerName));
          this.lastTime=localStorage.getItem(this.localtimeName);

          //处理数据格式
          this.formatData(res);

          //获取本地数据
          this.getlocalData();

          //本地实时存储时间
          this.saveTime();

          //若果限制考试时长，开始计时
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
      this.allType=Object.keys(paper);
      Object.keys(paper).forEach(key => {
        if (key != "material") {
          paper[key].forEach(item => {
            this.sixType(item.type, item);
          });
        }
        if (key == "material") {
          paper[key].forEach(item => {
            let title = Object.assign({}, item, { subs: "" });
            item.subs.forEach((sub, index) => {
              sub.parentTitle = title;
              sub.parentType = item.type;
              sub.materialIndex = index+1;
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
        item[item.id] = new Array(length).fill(false);
        //因为单选和判断题回显有问题，所以这里需要在一开始处理数据时就赋值
        if(this.lastAnswer){
          this.$set(this.answer,item.id,this.lastAnswer[item.id])
        }else{
          this.$set(this.answer,item.id,[])
        }
        this.info.push(item);
      }
      if (type == "choice" || type == "uncertain_choice") {
        let length = item.metas.choices.length;
        item[item.id] = new Array(length).fill(false);
        this.$set(this.answer,item.id,[])
        this.info.push(item);
      }
      if (type == "essay") {
        item[item.id] = "";
        this.$set(this.answer,item.id,[""])
        this.info.push(item);
      }

      if (type == "fill") {
        let fillstem = item.stem;
        let { stem, index } = this.fillReplce(fillstem, 0);
        item.stem = stem;
        item.fillnum = index;
        this.$set(this.answer,item.id , new Array(index).fill(""))
        this.info.push(item);
      }

      if (type == "determine") {
        item[item.id] = new Array(2).fill(false);
        //因为单选和判断题回显有问题，所以这里需要在一开始处理数据时就赋值
        if(this.lastAnswer){
          this.$set(this.answer,item.id,this.lastAnswer[item.id])
        }else{
          this.$set(this.answer,item.id,[])
        }
        this.info.push(item);
      }
    },
    //处理富文本
    fillReplce(stem, index) {
      const reg = /\[\[.+?\]\]/;
      // if(!reg.test(stem)){
      //     return { stem , index}
      // }else{
      //     // s=s.macth(reg,`<span>${index}</span>`);
      //     // a(s)
      //     stem=stem.replace(reg,() => {
      //         return `<span>${++index}</span>`
      //     });
      //     this.fillReplce(stem,index)
      // }
      while (reg.exec(stem)) {
        stem = stem.replace(reg, () => {
          return `<span>${++index}</span>`;
        });
      }
      return { stem, index };
    },
    //切换页面，动态更改索引和设置高度
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
        if(this.costTime){
          let gotime=(this.costTime-this.testpaperResult.beginTime*1000)/1000/60;
          time=this.testpaperResult.limitedTime-gotime
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
            // this.hours=hours;
            // this.minutes=minutes;
            // this.seconds=seconds;
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
               this.handExamdo(answer)

              clearInterval(this.timeMeter);        
              this.timeMeter = null;
            }
        },1000);
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

      Dialog.confirm({
        title: '交卷',
        cancelButtonText:'确认交卷',
        confirmButtonText:'检查一下',
        message: message
      }).then(() => { })
      .catch(() => {
        clearInterval(this.timeMeter);        
        this.timeMeter = null;
        this.handExamdo(answer)
      });
    },
    //向后台提交答卷数据
    async handExamdo(answer,usedTime){
      if(!usedTime){
          let usedTime=new Date().getTime()/1000;
      }
      //this.$router.replace({path:'/'})
     // this.isHandExam=true;

      // await Api.handExam({
      //       data: {
      //         data: answer,
      //         resultId: this.testpaperResult.id,
      //         usedTime: usedTime
      //       }
      // })
      // .then(res => {
      //   this.isHandExam=true;
      //   localStorage.removeItem(`${this.user.id}-${this.testpaper.id}`);
      //   localStorage.removeItem(`${this.user.id}-${this.testpaper.id}-time`);
      //   //跳转到批阅页
      //   //this.$router.replace({path:'/'})
      //    next()
      //     console.log(res)
      // })
      // .catch(err => {
      //       Toast.fail(err.message);
      // });
    },
    //实时存储答案
    saveAnswer(val){
        localStorage.setItem(this.localanswerName, JSON.stringify(val));
    },
    saveTime(){
        this.localtime=setInterval(()=>{
            localStorage.setItem(this.localtimeName, new Date().getTime());
        },1000);
    }
  }
};
</script>
