<template>
  <div class="paper-swiper">
    <van-swipe
      ref="swipe"
      :height="height"
      @change="changeswiper"
      :show-indicators="false"
      :loop="false"
      :duration="100"
      v-if="testData.length>0"
    >
      <van-swipe-item v-for="(paper,index) in info" :key="paper.id">
        <div :ref="`paper${index}`" class="paper-item">
          <head-top
            :all="testData.length"
            :current="currentIndex+1"
            :subject="subject(paper)"
            :score="`${parseFloat(paper.score)}`"
          />

          <single-choice
            v-if=" paper.type=='single_choice' "
            :itemdata="paper"
            :answer="testAnswer[paper.id]"
            :number="index+1"
            @singleChoose="singleChoose"
          />

          <choice-type 
            v-if=" paper.type=='choice' || paper.type=='uncertain_choice' "
            :itemdata="paper"
            :answer="testAnswer[paper.id]"
            :number="index+1"
            @choiceChoose="choiceChoose"
          />
          
          <determine-type 
            v-if=" paper.type=='determine'"
            :itemdata="paper"
            :answer="testAnswer[paper.id]"
            :number="index+1"
            @determineChoose="determineChoose"
          />

          <essay-type 
            v-if=" paper.type=='essay'"
            :itemdata="paper"
            :answer="testAnswer[paper.id]"
            :number="index+1"
          />

          <fill-type 
            v-if=" paper.type=='fill'"
            :itemdata="paper"
            :answer="testAnswer[paper.id]"
            :number="index+1"
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
const WINDOWHEIGHT = document.documentElement.clientHeight - 44;
import { mapState, mapMutations , mapActions} from "vuex";
import fillType from "../component/fill";
import essayType from "../component/essay";
import headTop from "../component/head";
import choiceType from "../component/choice";
import singleChoice from "../component/single-choice";
import determineType from "../component/determine";
import { setTimeout } from 'timers';

export default {
    name:'item-bank',
    data(){
        return{
            testData:this.info,
            testAnswer:this.answer,
            height: 0,//滑动卡片当前高度
            currentIndex:this.current
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
        }
    },
    watch:{
        answer(val){
            this.$emit('update:answer', val)
        },
        current:{
            handler:'slideToNumber'
        },
        info:{
          immediate:true,
          deep:true,
          handler(val){
              console.log(val.length)
          }
        }
    },
    components: {
        fillType,
        essayType,
        headTop,
        choiceType,
        singleChoice,
        determineType
    },
    mounted() {
     setTimeout(()=>{
       this.$nextTick(() => {
          this.changeswiper(0)
        });
     },500)
    },
    methods:{
        //由于swiper的高度无法自适应内容高度，所以切换页面要动态更改索引和设置高度
        changeswiper(index) {
            this.currentIndex = index;
            this.$emit('update:current', index)
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
            this.$set(this.testAnswer[id], 0, name);
        },
        //多选题和不定项选择
        choiceChoose(name, id){
            this.$set(this.testAnswer, id, name);
        },
        //判断题选择
        determineChoose(name, id){
            this.$set(this.testAnswer[id], 0, Number(name));
        },
        //答题卡定位
        slideToNumber(num){
            let index=Number(num);
            if(num===this.currentIndex){
                return
            }
            this.$refs.swipe.swipeTo(index-1);
        },
    }
}
</script>

<style>

</style>