<template>
  <div class="paper-swiper">
    <van-swipe
     v-if="testData.length>0"
      ref="swipe"
      :height="height"
      :show-indicators="false"
      :loop="false"
      :duration="100"
      @change="changeswiper"
    >
      <van-swipe-item v-for="(paper,index) in info" :key="paper.id" :style="{height:height+'px'}">
        <div :ref="`paper${index}`" class="paper-item">
          <head-top
            :all="all"
            :current="Number(paper.seq)"
            :subject="subject(paper)"
            :score="`${parseFloat(paper.score)}`"
            :showScore="showScore"
          />

          <single-choice
            v-if=" paper.type=='single_choice' "
            :itemdata="paper"
            :answer="testAnswer[paper.id]"
            :number="index+1"
            :canDo="canDo"
            @singleChoose="singleChoose"
          />

          <choice-type
            v-if=" paper.type=='choice' || paper.type=='uncertain_choice' "
            :itemdata="paper"
            :answer="testAnswer[paper.id]"
            :number="index+1"
            :canDo="canDo"
            @choiceChoose="choiceChoose"
          />

          <determine-type
            v-if=" paper.type=='determine'"
            :itemdata="paper"
            :answer="testAnswer[paper.id]"
            :number="index+1"
            :canDo="canDo"
            @determineChoose="determineChoose"
          />

          <essay-type
            v-if=" paper.type=='essay'"
            :itemdata="paper"
            :answer="testAnswer[paper.id]"
            :canDo="canDo"
            :number="index+1"
          />

          <fill-type
            v-if=" paper.type=='fill'"
            :itemdata="paper"
            :answer="testAnswer[paper.id]"
            :canDo="canDo"
            :number="index+1"
          />

          <analysis 
            v-if="!canDo" 
            :testResult="paper.testResult"
            :analysis="paper.analysis"
            :answer="paper.answer"
            :subject="paper.type"
          />
        </div>
      </van-swipe-item>
    </van-swipe>
    <!-- 左右滑动按钮 -->
    <div>
      <div :class="['left-slide__btn',currentIndex==0 ?'slide-disabled':'']" @click="last()">
        <i class="iconfont icon-arrow-left"></i>
      </div>
      <div
        :class="['right-slide__btn',(currentIndex==info.length-1) ?'slide-disabled':'']"
        @click="next()"
      >
        <i class="iconfont icon-arrow-right"></i>
      </div>
    </div>
  </div>
</template>

<script>
const NAVBARHEIGHT=44;
const WINDOWHEIGHT = document.documentElement.clientHeight - NAVBARHEIGHT;
import { mapState, mapMutations , mapActions} from "vuex";
import fillType from "../component/fill";
import essayType from "../component/essay";
import headTop from "../component/head";
import choiceType from "../component/choice";
import singleChoice from "../component/single-choice";
import determineType from "../component/determine";
import analysis from "../component/analysis";
import { setTimeout } from 'timers';

export default {
    name:'item-bank',
    data(){
        return{
            testData:this.info,
            testAnswer:this.answer,
            currentIndex:this.current,
            height:WINDOWHEIGHT
        }
    },
    props:{
        info:{
            type:Array,
            default:()=>[]
        },
        answer:{
            type:Object,
            default:()=>{}
        },
        current:{
            type:Number,
            default:0
        },
        showScore:{
            type:Boolean,
            default:true
        },
        canDo:{
          type:Boolean,
          default:true
        },
        all:{
            type:Number,
            default:0
        },
    },
    watch:{
        answer(val){
            this.$emit('update:answer', val)
        },
        current(val,oldval){
          //答题卡定位
          let index=Number(val);
          this.$refs.swipe.swipeTo(val-1);
        }
    },
    components: {
        fillType,
        essayType,
        headTop,
        choiceType,
        singleChoice,
        determineType,
        analysis
    },
    methods:{
        changeswiper(index) {
            this.currentIndex = index;
            this.$emit('update:slideIndex', index);
        },
         //左滑动
        last() {
            if (this.currentIndex == 0) {
                return;
            }
            this.$refs.swipe.swipeTo(this.currentIndex - 1);
        },
        //右滑动
        next() {
            if (this.currentIndex == this.info.length - 1) {
                return;
            }
            this.$refs.swipe.swipeTo(this.currentIndex + 1);
        },
        //题目类型过滤
        subject(paper) {
            var parentType='';
            var type = paper.type;
            var typeName;

            if (paper.parentType) {
              parentType = "材料题-";
            }

            switch (type) {
              case "single_choice":
                typeName='单选题';
                break;
              case "choice":
                typeName='多选题';
                break;
              case "essay":
                typeName='问答题';
                break;
              case "uncertain_choice":
                typeName='不定项选择题';
                break;
              case "determine":
                typeName='判断题';
                break;
              case "fill":
                typeName='填空题';
                break;
              case "material":
                typeName='材料题';
                break;
              default:
                ''
            }
            return parentType + typeName
        },
        //单选题选择
        singleChoose(name, id) {
            this.$set(this.testAnswer[id], 0, name);
        },
        //多选题和不定项选择
        choiceChoose(name, id){
            this.$set(this.testAnswer, id, name);
        },
        //判断题选择
        determineChoose(name, id){
            this.$set(this.testAnswer[id], 0, Number(name));
        }
    }
}
</script>

<style>

</style>
