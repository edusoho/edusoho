<template>
  <div>
    <div v-if="itemdata.parentTitle" class="subject-material">
      <div :class="['material-stem-nowrap', isShowDownIcon ? 'material-stem' : '']">
        <span v-if="itemdata.parentTitle" :class="['material-tags']">
          {{ subject }}
        </span>
        <span class="material-text material-icon" v-html="stem" @click="handleClickImage($event.target.src)" >
        </span>
      </div>
      <i @click="changeUpIcon" :class="['iconfont', 'icon-arrow-up', {'show-up-icon': isShowDownIcon }]"></i>
      <i @click="changeDownIcon" :class="['iconfont', 'icon-arrow-down', {'show-down-icon': isShowUpIcon }]"></i>
      <attachement-preview 
        v-for="item in getAttachementMaterialType('material')"
        :canLoadPlayer="isCurrent"
        :attachment="item"
        :key="item.id" />
    </div>
    <div class="subject">
      <span v-if="!itemdata.parentTitle" class="tags">
        {{ subject }}
      </span>
      <div v-if="!itemdata.parentTitle" class="subject-stem">
        <span class="serial-number">{{ itemdata.seq }}、</span>
        <div class="subject-stem__content rich-text" v-html="stem" @click="handleClickImage($event.target.src)" />
      </div>
  
      <div  v-if="itemdata.parentTitle" :class="['material-title',{'material-title-weight': itemdata.parentTitle}]">
        <span class="serial-number"><span class="material-type">[{{ $t('courseLearning.determine') }}] </span> {{ itemdata.materialIndex }}、</span>
        <div class="rich-text" v-html="itemdata.stem" @click="handleClickImage($event.target.src)" />
      </div>
  
      <attachement-preview 
        v-for="item in getAttachementByType('stem')"
        :canLoadPlayer="isCurrent"
        :attachment="item"
        :key="item.id" />
      <van-radio-group v-model="radio"  :class="['answer-paper',{'convention': mode !== 'exercise'}]" @change="choose">
        <van-radio
          :name="1"
          :disabled="!disabledData"
          class="subject-option subject-option--determine"
          :class="[
            !canDo ? checkAnswer(1, itemdata) : '' , 
            { active: 1 === currentItem && disabledData } , 
            { 'van-checked__right' : itemdata.answer && itemdata.answer[0] === 1 ? itemdata.testResult.answer && itemdata.testResult.answer[0] === 1 ? true : true : false },
            {isRight: question.length > 0 &&  question[0].answer[0] === 'T'},
            {isWrong: question.length > 0 &&  'F' !== question[0].response[0] &&  myRadioAnswer !== question[0].answer[0]}
          ]"
        >
          <i class="iconfont icon-a-Frame34723"></i>
          <i class="iconfont icon-cuowu2"></i>
          <i class="iconfont icon-zhengque1"></i>
          <div class="subject-option__content">正确</div>
        
        </van-radio>
        <van-radio
          :name="0"
          :disabled="!disabledData"
          class="subject-option subject-option--determine"
          :class="[
            !canDo ? checkAnswer(0, itemdata) : '' , 
            { active: 0 === currentItem } , 
            { 'van-checked__right' :  itemdata.answer && itemdata.answer[0] === 0 ? itemdata.testResult.answer && itemdata.testResult.answer[0] === 0 ? true : true : false },
            {isRight: question.length > 0 &&  question[0].answer[0] === 'F'},
            {isWrong: question.length > 0 &&  'F' === question[0].response[0] &&  myAnswer !== question[0].answer[0]}
          ]"
        >
          <i class="iconfont icon-a-Frame34723"></i>
          <i class="iconfont icon-cuowu2"></i>
          <i class="iconfont icon-zhengque1"></i>
          <div class="subject-option__content">错误</div>
          
        </van-radio>
      </van-radio-group>
      <div v-if="!disabledData" class="one-questions-analysis">
        <div class="flex justify-between analysis-answer">
          <div class="flex items-center">
            <span class="answer">{{ $t('courseLearning.referenceAnswer') }}：</span>
            <span class="options" style="color:#00B42A;" v-if="question.length > 0">
              {{ question[0].answer[0] === 'T' ? '对' : '错' }}
            </span>
            <span class="options" v-if="!canDo" style="color:#00B42A;">
              {{ itemdata.answer[0] === 1 ? '对' : '错' }}
            </span>
          </div>
          <div class="flex items-center" v-if="itemdata.testResult.answer && itemdata.testResult.answer.length > 0 || question.length > 0">
            <span class="answer">{{ $t('courseLearning.selectedAnswer') }}：</span>
            <span class="options" v-if="question.length > 0">
              {{ question[0].response[0] === 'T' ? '对' : '错' }}
            </span>
            <span v-if="!canDo">
              <span class="options">{{ itemdata.testResult.answer[0] === 1 ? '对' : '错'  }}</span>
            </span>
          </div>
        </div>
        <div v-if="mode === 'exam'" class="analysis-color mb-8">
          {{ $t('courseLearning.score') }}：{{ itemdata.testResult ? itemdata.testResult.score : 0.0 }}
        </div>
        <div v-if="mode === 'exam'" class="analysis-color mb-8">
          {{ $t('courseLearning.comment') }}：{{ itemdata.testResult ? itemdata.testResult.teacherSay === null ? '--' : itemdata.testResult.teacherSay : '' }}
        </div>
        <div class="analysis-color">
          <span class="float-left">{{ $t('courseLearning.analyze') }}：</span>
          <span v-if="analysis" v-html="analysis" @click="handleClickImage($event.target.src)" />
          <span v-else>{{ $t('courseLearning.noParsing') }}</span>
        </div>
        <attachement-preview 
          v-for="item in getAttachementByType('analysis')"
          :canLoadPlayer="isCurrent"
          :attachment="item"
          :key="item.id" />
      </div>
    </div>
    <div v-if="isShowFooterShardow()" class="footer-shadow">
    </div>
    <div v-if="parentType && parentType === 'material' && !disabledData" class="subject-footer">
      <span class="float-left">{{ $t('courseLearning.analyze') }}：</span>
      <span v-if="parentTitleAnalysis !== ''" v-html="parentTitleAnalysis" @click="handleClickImage($event.target.src)" />
      <span v-else>{{ $t('courseLearning.noParsing') }}</span>
      <attachement-preview 
        v-for="item in getAttachementMaterialType('analysis')"
        :canLoadPlayer="isCurrent"
        :attachment="item"
        :key="item.id" />
    </div>
    <div v-if="totalCount === reviewedCount" class="submit-footer">
      <van-button
        class="submit-footer-btn"
        :style="{width:width - 20 + 'px'}"
        type="primary"
        @click="goResults()"
        >{{ $t('courseLearning.viewResult2') }}</van-button
      >
    </div>
  </div>
</template>

<script>
import refreshChoice from '@/mixins/lessonTask/swipeRefResh.js';
import checkAnswer from '@/mixins/lessonTask/itemBank';
import isShowFooterShardow from '@/mixins/lessonTask/footerShardow';
import attachementPreview from './attachement-preview.vue';
import handleClickImage from '@/mixins/lessonTask/handleClickImage.js';

const WINDOWWIDTH = document.documentElement.clientWidth

export default {
  name: 'DetermineType',
  mixins: [checkAnswer, isShowFooterShardow, refreshChoice, handleClickImage],
  components: {
    attachementPreview
  },
  props: {
    itemdata: {
      type: Object,
      default: () => {},
    },
    isCurrent: Boolean,
    answer: {
      type: Array,
      default: () => [],
    },
    canDo: {
      type: Boolean,
      default: true,
    },
    subject: {
      type: String,
      default: '',
    },
    number: {
      type: Number,
      default: null,
    },
    showShadow: {
      type: String,
      default: ''
    },
    exerciseMode: {
      type: String,
      default: '',
    },
    status: {
      type: String,
      default: null
    },
    exerciseInfo: {
      type: Object,
      default: () => {}
    },
    analysis: {
      type: String,
      default: '',
    },
    mode: {
      type: String,
      default: '',
    },
    disabledData: {
      type: Boolean,
      default: false
    },
    parentTitleAnalysis: {
      type: String,
      default: '',
    },
    parentType: {
      type: String,
      default: '',
    },
    totalCount: {
      type: Number,
      default: 0
    },
    reviewedCount: {
      type: Number,
      default: 0
    },
  },
  data() {
    return {
      radio: this.answer[0],
      currentItem: null,
      isShowDownIcon: null,
      isShowUpIcon: false,
      myAnswer: 'F',
      question: [],
      width: WINDOWWIDTH,
      myRadioAnswer: 'T'
    };
  },
  computed: {
    stem: {
      get() {
        if (this.itemdata.parentTitle) {
          return this.itemdata.parentTitle.stem;
        } else {
          return this.itemdata.stem;
        }
      },
    },
  },
  mounted() {
    if (this.canDo && this.mode === 'exercise') {
      this.refreshChoice()
    }
    this.isShowDownIcon = document.getElementsByClassName('material-icon')[this.number]?.childNodes[0].offsetWidth > 234
  },
  methods: {
    // 向父级提交数据
    choose(name) {
      this.currentItem = name

      this.$emit('determineChoose', this.radio, this.itemdata);
    },
    getAttachementByType(type) {
      return this.itemdata.attachments.filter(item => item.module === type) || []
    },
    getAttachementMaterialType(type) {
      return this.itemdata.parentTitle.attachments.filter(item => item.module === type) || []
    },
    changeUpIcon() {
      this.isShowUpIcon = true
      this.isShowDownIcon = false
    },
    changeDownIcon() {
      this.isShowUpIcon = false
      this.isShowDownIcon = true
    },
    goResults() {
      this.$emit('goResults');
    },
    isWrong() {
      if (!this.question.response) return true
      // const rightanswer = itemdata.answer;
      // if (itemdata.testResult && itemdata.testResult.answer) {
      //   const answer = itemdata.testResult.answer || [];
      //   if (rightanswer.includes(index)) {
      //     return 'subject-option__order_right';
      //   } else if (
      //     (rightanswer.includes(index) && !answer.includes(index)) ||
      //     (!rightanswer.includes(index) && answer.includes(index))
      //   ) {
      //     return 'van-checked__wrong';
      //   }
      // }
      // return '';
    },
  },
};
</script>
<style scoped lang="scss">
  ::v-deep .van-radio {
    position: relative;
    display: block;
  }
  ::v-deep .van-radio__icon {
    display: none;
  }
  .icon-a-Frame34723 {
    display: none;
    position: absolute;
    top: -2px;
    right: -5px;
    color: #428FFA;
    width: 20px;
    height: 20px;
  }
  .icon-zhengque1 {
    display: none;
    position: absolute;
    top: 50%;
    right: 16px;
    margin-top: -8px;
    color: #00B42A;
  }
  .icon-cuowu2 {
    display: none;
    position: absolute;
    top: 50%;
    right: 16px;
    margin-top: -8px;
    color: #F53F3F;
  }
  .exercise-do .active,
  .convention .active {
      background: #F6F9FF;
      border: 1px solid #428FFA;
      .icon-a-Frame34723 {
        display: block;

      }
    }
    .icon-arrow-up {
    display: none;
    position: absolute;
    top: vw(26);
    right: vw(18);
    margin-top: vw(-8);
    color: #D2D3D4;
  }
  .icon-arrow-down {
    display: none;
    position: absolute;
    top: vw(26);
    right: vw(18);
    margin-top: vw(-12);
    color: #D2D3D4;
  }
  /deep/.material-text {
    img {
      display: block !important;
      margin-bottom: vw(8);
      width: vw(156);
      height: vw(88);
      border-radius: vw(8);
    }
    p {
      display: inline !important;
      font-size: vw(14);
      overflow: hidden;
    }
  }
  .show-down-icon {
    display: block;
    cursor: pointer;
  }
  .show-up-icon {
    display: block;
    cursor: pointer;
  }
</style>