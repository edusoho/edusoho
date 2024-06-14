<template>
  <div class="paper-swiper">
    <van-swipe
      v-if="testData.length > 0 && sdkLoaded"
      ref="swipe"
      :height="height"
      :show-indicators="false"
      :touchable="touchable"
      :loop="false"
      :duration="100"
      :lazy-render="true"
      @change="changeswiper"
    >
      <van-swipe-item
        v-for="(paper, index) in info"
        :key="paper.id"
        :style="{ height: height + 'px' }"
        :class="canDo && iscando[index] ? 'exercise-do' : 'exercise-analysis'"
      >
        <div :ref="`paper${index}`" class="paper-item">
          <head-top
            :all="all"
            :can-do="canDo"
            :current="Number(paper.seq)"
            :subject="subject(paper)"
            :score="`${parseFloat(paper.score)}`"
            :show-score="showScore"
            :exerciseMode="exerciseMode"
            :totalCount="info.length"
            :reviewedCount="reviewedCount ? reviewedCount : exerciseInfo ? exerciseInfo.reviewedCount : 0"
          />

          <single-choice
            v-if="paper.type == 'single_choice'"
            :ref="'submit'+index"
            :itemdata="paper"
            :answer="testAnswer[paper.id]"
            :is-current="currentIndex === index"
            :can-do="canDo"
            :cloudSdkCdn="cloudSdkCdn"
            :showShadow = "info[info.length - 1].id"
            :number="index"
            :status="status"
            :subject="subject(paper)"
            :exerciseInfo="exerciseInfo"
            :exerciseMode="exerciseMode"
            :parentTitleAnalysis="paper.parentTitle ? paper.parentTitle.analysis : ''"
            :parentType="paper.parentType ? paper.parentType : ''"
            :analysis="paper.analysis"
            :test-result="paper.testResult"
            :key="refreshKey"
            :mode="mode"
            :isExercise="isExercise"
            :disabledData="mode === 'exercise' ? canDo && iscando[index] : canDo"
            @singleChoose="singleChoose"
            @goResults="goResults"
            :totalCount="info.length"
            :reviewedCount="reviewedCount ? reviewedCount : exerciseInfo ? exerciseInfo.reviewedCount : 0"
          />

          <choice-type
            v-if="paper.type == 'choice' || paper.type == 'uncertain_choice'"
            :itemdata="paper"
            :ref="'submit'+ index"
            :answer="testAnswer[paper.id]"
            :is-current="currentIndex === index"
            :number="index"
            :can-do="canDo"
            :showShadow = "info[info.length - 1].id"
            :subject="subject(paper)"
            :exerciseInfo="exerciseInfo"
            :exerciseMode="exerciseMode"
            :analysis="paper.analysis"
            :myAnswer="myAnswer"
            :test-result="paper.testResult"
            :mode="mode"
            :parentTitleAnalysis="paper.parentTitle ? paper.parentTitle.analysis : ''"
            :parentType="paper.parentType ? paper.parentType : ''"
            :disabledData="mode === 'exercise' ? canDo && iscando[index] : canDo"
            @choiceChoose="choiceChoose"
            @submitSingleAnswer = "submitSingleAnswer"
            @changeTouch="changeTouch"
            @goResults="goResults"
            :totalCount="info.length"
            :reviewedCount="reviewedCount ? reviewedCount : exerciseInfo ? exerciseInfo.reviewedCount : 0"
          />

          <determine-type
            v-if="paper.type == 'determine'"
            :ref="'submit'+ index"
            :itemdata="paper"
            :answer="testAnswer[paper.id]"
            :is-current="currentIndex === index"
            :number="index"
            :can-do="canDo"
            :showShadow = "info[info.length - 1].id"
            :subject="subject(paper)"
            :status="status"
            :exerciseMode="exerciseMode"
            :exerciseInfo="exerciseInfo"
            :analysis="paper.analysis"
            :mode="mode"
            :parentTitleAnalysis="paper.parentTitle ? paper.parentTitle.analysis : ''"
            :parentType="paper.parentType ? paper.parentType : ''"
            :disabledData="mode === 'exercise' ? canDo && iscando[index] : canDo"
            @determineChoose="determineChoose"
            @goResults="goResults"
            :totalCount="info.length"
            :reviewedCount="reviewedCount ? reviewedCount : exerciseInfo ? exerciseInfo.reviewedCount : 0"
          />

          <essay-type
            v-if="paper.type == 'essay'"
            :ref="'submit'+index"
            :itemdata="paper"
            :answer="testAnswer[paper.id]"
            :can-do="canDo"
            :showShadow = "info[info.length - 1].id"
            :is-current="currentIndex === index"
            :number="index"
            :mode="mode"
            :parentTitleAnalysis="paper.parentTitle ? paper.parentTitle.analysis : ''"
            :parentType="paper.parentType ? paper.parentType : ''"
            :exerciseMode="exerciseMode"
            :subject="subject(paper)"
            :analysis="paper.analysis"
            :exerciseInfo="exerciseInfo"
            :disabledData="mode === 'exercise' ? canDo && iscando[index] : canDo"
            @submitSingleAnswer = "submitSingleAnswer"
            @goResults="goResults"
            :totalCount="info.length"
            @changeTouch="changeTouch"
            :reviewedCount="reviewedCount ? reviewedCount : exerciseInfo ? exerciseInfo.reviewedCount : 0"
          />

          <fill-type
            v-if="paper.type == 'fill'"
            :ref="'submit'+index"
            :itemdata="paper"
            :answer="testAnswer[paper.id]"
            :can-do="canDo"
            :showShadow = "info[info.length - 1].id"
            :is-current="currentIndex === index"
            :number="index"
            :mode="mode"
            :parentTitleAnalysis="paper.parentTitle ? paper.parentTitle.analysis : ''"
            :parentType="paper.parentType ? paper.parentType : ''"
            :subject="subject(paper)"
            :exerciseMode="exerciseMode"
            :exerciseInfo="exerciseInfo"
            :analysis="paper.analysis"
            :disabledData="mode === 'exercise' ? canDo && iscando[index] : canDo"
            @submitSingleAnswer = "submitSingleAnswer"
            @goResults="goResults"
            :totalCount="info.length"
            @changeTouch="changeTouch"
            :reviewedCount="reviewedCount ? reviewedCount : exerciseInfo ? exerciseInfo.reviewedCount : 0"
          />

          <!-- <analysis
            v-if="!canDo"
            :test-result="paper.testResult"
            :analysis="paper.analysis"
            :answer="paper.answer"
            :subject="paper.type"
            :is-current="currentIndex === index"
            :attachments="paper.attachments"
            :is-exercise="isExercise"
            :result-show="resultShow"
          /> -->
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
import Api from '@/api';
import { mapState, mapActions } from 'vuex';
import loadScript from 'load-script';
import * as types from '@/store/mutation-types';
import fillType from '../component/fill';
import essayType from '../component/essay';
import headTop from '../component/head';
import choiceType from '../component/choice';
import singleChoice from '../component/single-choice';
import determineType from '../component/determine';
// import analysis from '../component/analysis';
import { Toast } from 'vant';
import _ from 'lodash';

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
    // analysis
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
    exerciseMode: {
      // 做题模式选择
      type: String,
      default: '',
    },
    exerciseInfo: {
      // 答题记录id
      type: Object,
      default: () => {},
    },
    // 试卷iD
    assessment_id: {
      type: String,
      default: ''
    },
    admission_ticket: {
      // 答题凭证
      type: String,
      default: ''
    },
    mode: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      testData: this.info,
      testAnswer: this.answer,
      currentIndex: this.current,
      height: WINDOWHEIGHT,
      sdkLoaded: false,
      reviewedCount: null,
      status: null,
      iscando: [],
      refreshKey: true,
      myAnswer: null,
      touchable: true
    };
  },
  computed: {
    ...mapState(['cloudSdkCdn', 'isLoadedSdk']),
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
    reviewedCount() {
      if (this.reviewedCount === this.info.length) {
        this.$emit('reviewedCount')
      }
    }
  },
  created() {
    if (!this.cloudSdkCdn) {
      this.setCloudAddress();
    }

    if (!this.isLoadedSdk) {
      loadScript(`https://${this.cloudSdkCdn}/js-sdk-v2/sdk-v1.js?${Date.now()}`, () => {
        this.$store.commit(types.LOADED_CLOUD_SDK);
      })
    }

    this.sdkLoaded = true
    if (this.canDo && this.mode === 'exercise') {

      this.info.forEach((item, index) => {
        if (this.exerciseInfo.submittedQuestions.filter(subItem => subItem.questionId + '' === item.id).length > 0) {
          this.iscando[index] = false
        } else {
          this.iscando[index] = true
        }
      });

    }

  },
  methods: {
    ...mapActions(['setCloudAddress']),
    changeswiper(index) {
      this.currentIndex = index;
      this.$emit('update:current', index + 1);
      this.$emit('update:slideIndex', index);
    },
    // 左滑动
    last() {
      if (this.currentIndex == 0 || !this.touchable) {
        return;
      }
      this.$refs.swipe.swipeTo(this.currentIndex - 1);
    },
    // 右滑动
    next() {
      if (this.currentIndex == this.info.length - 1 || !this.touchable) {
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
        parentType = this.$t('courseLearning.material');
        return parentType;
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
    singleChoose(response, data) {
      if ( this.exerciseMode === '1' ) {
      this.touchable = false
        this.submitSingleAnswer(this.numberFormatterCode(response) , data)
      }
      this.$set(this.testAnswer[data.id], 0, response);
    },
    // 多选题和不定项选择
    choiceChoose(response, data) {
      this.$set(this.testAnswer, data.id, response);
    },
    changeTouch() {
      this.touchable = false
    },
    // 判断题选择
    determineChoose(response, data) {
      if(this.exerciseMode === '1') {
        this.touchable = false
        this.submitSingleAnswer(this.numberFormatterCode(response, data.type) , data)
      }
        this.$set(this.testAnswer[data.id], 0, Number(response));
    },
    // 单题提交
    submitSingleAnswer: _.debounce(function (response, data) {
      if(data.type === "choice" || data.type === "uncertain_choice"){
        response = this.numberFormatterCode(response)
        response = response.sort()
      }
      Api.submitSingleAnswer({
        query:{
          id: this.exerciseInfo.id
        },
        data:{
          admission_ticket: this.admission_ticket,
          assessment_id: this.assessment_id,
          exerciseMode: this.exerciseMode,
          section_id: data.sectionId,
          item_id: data.itemId,
          question_id: data.id,
          response: response,
        }
      }).then(res=> {
        const idx = this.current === 0 ? this.current : this.current - 1
        this.$refs['submit'+idx][0].refreshChoice(res)
        this.reviewedCount = res.reviewedCount
        this.status = res.status
        this.myAnswer = res.response

        this.touchable = true
        if (res.status === 'right') {
          setTimeout(() => {
            this.next()
          }, 1000);
          this.iscando[idx] = false
        } else {
          this.iscando[idx] = false
        }
      }).catch(err=> {
        this.touchable = true
        Toast.fail(err.message)
      })
    }, 1000),
    // 数值转换英文
    numberFormatterCode(response, type) {
      if (Array.isArray(response)) {
        return response.map((item) => {
          return String.fromCharCode(item + 65)
        })
      } else if (type === "determine") {
        return response === 1 ? String.fromCharCode(84).split('') : String.fromCharCode(70).split('')
      } else {
        return String.fromCharCode(response + 65).split('')
      }
    },

    goResults() {
      this.$emit('goResults');
    }
  },
  destroyed(){
    this.$store.commit(types.DESTROY_CLOUD_SDK);
  }
};
</script>

<style></style>
