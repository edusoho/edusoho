<template>
  <div class="paper-swiper">
    <van-swipe
      v-if="testData.length > 0"
      ref="swipe"
      :height="height"
      :show-indicators="false"
      :loop="false"
      :duration="100"
      @change="changeswiper"
    >
      <van-swipe-item
        v-for="(paper, index) in info"
        :key="paper.id"
        :style="{ height: height + 'px' }"
      >
        <div :ref="`paper${index}`" class="paper-item">
          <head-top
            :all="all"
            :current="Number(paper.seq)"
            :subject="subject(paper)"
            :score="`${parseFloat(paper.score)}`"
            :show-score="showScore"
          />

          <single-choice
            v-if="paper.type == 'single_choice'"
            :itemdata="paper"
            :answer="testAnswer[paper.id]"
            :number="index + 1"
            :can-do="canDo"
            @singleChoose="singleChoose"
          />

          <choice-type
            v-if="paper.type == 'choice' || paper.type == 'uncertain_choice'"
            :itemdata="paper"
            :answer="testAnswer[paper.id]"
            :number="index + 1"
            :can-do="canDo"
            @choiceChoose="choiceChoose"
          />

          <determine-type
            v-if="paper.type == 'determine'"
            :itemdata="paper"
            :answer="testAnswer[paper.id]"
            :number="index + 1"
            :can-do="canDo"
            @determineChoose="determineChoose"
          />

          <essay-type
            v-if="paper.type == 'essay'"
            :itemdata="paper"
            :answer="testAnswer[paper.id]"
            :can-do="canDo"
            :number="index + 1"
          />

          <fill-type
            v-if="paper.type == 'fill'"
            :itemdata="paper"
            :answer="testAnswer[paper.id]"
            :can-do="canDo"
            :number="index + 1"
          />

          <analysis
            v-if="!canDo"
            :test-result="paper.testResult"
            :analysis="paper.analysis"
            :answer="paper.answer"
            :subject="paper.type"
            :is-exercise="isExercise"
            :result-show="resultShow"
          />
        </div>
      </van-swipe-item>
    </van-swipe>
    <!-- 左右滑动按钮 -->
    <div>
      <div
        :class="['left-slide__btn', currentIndex == 0 ? 'slide-disabled' : '']"
        @click="last()"
      >
        <i class="iconfont icon-arrow-left" />
      </div>
      <div
        :class="[
          'right-slide__btn',
          currentIndex == info.length - 1 ? 'slide-disabled' : '',
        ]"
        @click="next()"
      >
        <i class="iconfont icon-arrow-right" />
      </div>
    </div>
  </div>
</template>

<script>
import fillType from '../component/fill';
import essayType from '../component/essay';
import headTop from '../component/head';
import choiceType from '../component/choice';
import singleChoice from '../component/single-choice';
import determineType from '../component/determine';
import analysis from '../component/analysis';
const NAVBARHEIGHT = 44;
const WINDOWHEIGHT = document.documentElement.clientHeight - NAVBARHEIGHT;

export default {
  name: 'ItemBank',
  components: {
    fillType,
    essayType,
    headTop,
    choiceType,
    singleChoice,
    determineType,
    analysis,
  },
  props: {
    info: {
      type: Array,
      default: () => [],
    },
    answer: {
      type: Object,
      default: () => {},
    },
    current: {
      type: Number,
      default: 0,
    },
    showScore: {
      type: Boolean,
      default: true,
    },
    canDo: {
      // 是否是做题模式
      type: Boolean,
      default: true,
    },
    all: {
      // 所有题数
      type: Number,
      default: 0,
    },
    isWrongMode: {
      // 是否是错题模式,只有在解析的时候有
      type: Boolean,
      default: false,
    },
    isExercise: {
      type: Boolean,
      default: false,
    },
    resultShow: {
      type: Boolean,
      default: true,
    },
  },
  data() {
    return {
      testData: this.info,
      testAnswer: this.answer,
      currentIndex: this.current,
      height: WINDOWHEIGHT,
    };
  },
  watch: {
    answer(val) {
      this.$emit('update:answer', val);
    },
    isWrongMode(val) {
      // 更改为错题模式时需要手动改变当前的currentIndex,并跳转过去
      this.currentIndex = this.current - 1;
      // 设置 immediate: true后可以关闭动画,解决点错题的时候会闪一下的问题
      this.$refs.swipe.swipeTo(this.current - 1, { immediate: true });
    },
    current(val, oldval) {
      // 答题卡定位
      const index = Number(val);
      if (index - 1 === this.currentIndex) {
        return;
      }
      this.$refs.swipe.swipeTo(index - 1);
    },
  },
  methods: {
    changeswiper(index) {
      this.currentIndex = index;
      this.$emit('update:current', index + 1);
      this.$emit('update:slideIndex', index);
    },
    // 左滑动
    last() {
      if (this.currentIndex == 0) {
        return;
      }
      this.$refs.swipe.swipeTo(this.currentIndex - 1);
    },
    // 右滑动
    next() {
      if (this.currentIndex == this.info.length - 1) {
        return;
      }
      this.$refs.swipe.swipeTo(this.currentIndex + 1);
    },
    // 题目类型过滤
    subject(paper) {
      let parentType = '';
      const type = paper.type;
      let typeName;

      if (paper.parentType) {
        parentType = '材料题-';
      }

      switch (type) {
        case 'single_choice':
          typeName = this.$t('courseLearning.singleChoice');
          break;
        case 'choice':
          typeName = this.$t('courseLearning.choice');
          break;
        case 'essay':
          typeName = this.$t('courseLearning.essay');
          break;
        case 'uncertain_choice':
          typeName = this.$t('courseLearning.uncertainChoice');
          break;
        case 'determine':
          typeName = this.$t('courseLearning.determine');
          break;
        case 'fill':
          typeName = this.$t('courseLearning.fill');
          break;
        case 'material':
          typeName = this.$t('courseLearning.material');
          break;
        default:
          typeName = '';
      }
      return parentType + typeName;
    },
    // 单选题选择
    singleChoose(name, id) {
      this.$set(this.testAnswer[id], 0, name);
    },
    // 多选题和不定项选择
    choiceChoose(name, id) {
      this.$set(this.testAnswer, id, name);
    },
    // 判断题选择
    determineChoose(name, id) {
      this.$set(this.testAnswer[id], 0, Number(name));
    },
  },
};
</script>

<style></style>
