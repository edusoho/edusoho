<template>
  <div>
    <div v-if="itemdata.parentTitle" class="subject-material">
      <div :class="['material-stem-nowrap', isShowDownIcon ? 'material-stem' : '']">
        <span v-if="itemdata.parentTitle" :class="['material-tags']">
          {{ subject }}
        </span>
        <span class="material-text material-icon" v-html="stem" @click="handleClickImage($event.target.src)">
        </span>
      </div>
      <i @click="changeUpIcon" :class="['iconfont', 'icon-arrow-up', {'show-up-icon': isShowDownIcon }]"></i>
      <i @click="changeDownIcon" :class="['iconfont', 'icon-arrow-down', {'show-down-icon': isShowUpIcon}]"></i>
      <attachement-preview 
        v-for="item in getAttachementByType('material')"
        :canLoadPlayer="isCurrent"
        :attachment="item"
        :key="item.id" />
    </div>
    <div class="fill">
      <span v-if="!itemdata.parentTitle" class="tags">
        {{ subject }}
      </span>
      <div v-if="!itemdata.parentTitle" class="subject-stem">
        <div class="serial-number">{{ itemdata.seq }}、</div>
        <div class="rich-text" v-html="stem" @click="handleClickImage($event.target.src)" />
      </div>
  
      <div v-if="itemdata.parentTitle" :class="['material-title',{'material-title-weight': itemdata.parentTitle}]">
        <span class="serial-number"><span class="material-type">[{{ $t('courseLearning.fill') }}] </span> {{ itemdata.materialIndex }}、</span>
        <div class="rich-text" v-html="itemdata.stem" @click="handleClickImage($event.target.src)" />
      </div>
  
      <attachement-preview 
        v-for="item in getAttachementByType('stem')"
        :canLoadPlayer="isCurrent"
        :attachment="item"
        :key="item.id" />
  
      <div v-if="disabledData" class="answer-paper">
        <div v-for="(i, index) in itemdata.fillnum" :key="index">
          <!-- <div class="fill-subject">填空题（{{ index + 1 }}）</div> -->
          <van-field
            v-model="answer[index]"
            :placeholder="canDo ? '('+ (index + 1) + ')' + $t('courseLearning.pleaseEnterYourAnswer') : $t('courseLearning.unanswered')"
            :disabled="!disabledData"
            class="fill-input"
            label-width="0px"
            type="textarea"
            rows="1"
            autosize
            :class="{'field-bottom': index != itemdata.fillnum - 1 }"
          />
        </div>
      </div>
      <div v-if="!disabledData" class="answer-paper">
        <div class="your-answer">{{ $t('courseLearning.yourAreAnswer') }}：</div>
        <div>
          <!-- (question.length > 0 && question[0].status === 'wrong' && question[0].status !== 'reviewing') || (itemdata.testResult.status === 'wrong' && itemdata.testResult.status !== 'noAnswer' && itemdata.testResult.status !== 'none') -->
          <!-- (question.length > 0 && question[0].status === 'right' && question[0].status !== 'reviewing') || (itemdata.testResult.status === 'right' && itemdata.testResult.status !== 'none') -->
          <img v-if="question.length > 0 && question[0].status === 'right' || itemdata.testResult.status === 'right'" :src="rigth" alt="" class="fill-status">
          <img v-if="question.length > 0 && question[0].status === 'wrong' || itemdata.testResult.status === 'wrong' || itemdata.testResult.status === 'none' || itemdata.testResult.status === 'noAnswer'" :src="wrong" alt="" class="fill-status">
          <span v-if="isunanswered()" class="your-answer is-wrong-answer"> {{ $t('courseLearning.unanswered') }}</span>
          <span v-for="(i, index) in answer" :key="index" :class="[question.length > 0 && question[0].status === 'right' ? 'is-right-answer' : 'is-wrong-answer']"> {{ answer.length - 1 === index ? i === '' ? $t('courseLearning.unanswered') : i : ((i === '' ? $t('courseLearning.unanswered') : i)+ ';') }}</span>
        </div>
        <div class="your-answer mt-16">
          正确答案：
        </div>
        <div class="mb-16">
          <span v-for="(i, index) in itemdata.answer" :key="index" class="is-right-answer"> {{  itemdata.answer.length - 1 === index ? i : i + ';' }}</span>
        </div>
        <div v-if="mode === 'exam'" class="analysis-color mb-8">
          {{ $t('courseLearning.score') }}：<div>{{ itemdata.testResult ? itemdata.testResult.score : 0.0 }}</div>
        </div>
        <div v-if="mode === 'exam'" class="analysis-color mb-8">
          {{ $t('courseLearning.comment') }}：<div>{{ itemdata.testResult ? itemdata.testResult.teacherSay === null ? '--' : itemdata.testResult.teacherSay : '' }}</div>
        </div>
        <div class="analysis-color">
          {{ $t('courseLearning.analyze') }}：
          <span v-if="analysis" v-html="analysis" @click="handleClickImage($event.target.src)" />
          <div v-else>{{ $t('courseLearning.noParsing') }}</div>
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
      {{ $t('courseLearning.analyze') }}：
      <span v-if="parentTitleAnalysis !== ''" v-html="parentTitleAnalysis" @click="handleClickImage($event.target.src)" />
      <div v-else>{{ $t('courseLearning.noParsing') }}</div>
      <attachement-preview 
        v-for="item in getAttachementMaterialType('analysis')"
        :canLoadPlayer="isCurrent"
        :attachment="item"
        :key="item.id" />
    </div>
    <div v-if="canDo && exerciseMode === '1' && disabledData" class="submit-footer" :style="{width:width+ 'px'}">
      <van-button
        class="submit-footer-btn"
        :style="{width:width - 20 + 'px'}"
        type="primary"
        @click="submitTopic"
        >{{ $t('courseLearning.submitATopic') }}</van-button
      >
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
import attachementPreview from './attachement-preview.vue';
import isShowFooterShardow from '../../../../mixins/lessonTask/footerShardow';
import { ImagePreview, Dialog } from 'vant'

const WINDOWWIDTH = document.documentElement.clientWidth

export default {
  name: 'FillType',
  components: {
    attachementPreview
  },
  mixins: [isShowFooterShardow],
  props: {
    filldata: {
      type: Object,
      default: () => {},
    },
    itemdata: {
      type: Object,
      default: () => {},
    },
    answer: {
      type: Array,
      default: () => [],
    },
    isCurrent: Boolean,
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
    exerciseInfo: {
      type: Object,
      default: () => {}
    },
    analysis: {
      type: String,
      default: '',
    },
    exerciseMode: {
      type: String,
      default: ''
    },
    mode: {
      type: String,
      default: ''
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
      index: 0,
      currentItem: null,
      isShowDownIcon: null,
      isShowUpIcon: false,
      width: WINDOWWIDTH,
      question: [],
      refreshKey: true,
      rigth: 'static/images/exercise/rigth.png',
      wrong: 'static/images/exercise/wrong.png',
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
    placeholder: {
      get() {
        if (this.canDo) {
          return '请填写答案';
        } else {
          return '未作答';
        }
      },
    },
  },
  mounted() {
    this.isShowDownIcon = document.getElementsByClassName('material-icon')[this.number]?.childNodes[0].offsetWidth > 234
    if (this.canDo && this.mode === 'exercise') {
      this.refreshChoice()
    }
  },
  methods: {
    getAttachementByType(type) {
      return this.itemdata.attachments.filter(item => item.module === type) || []
    },
    getAttachementMaterialType(type) {
      return this.itemdata.parentTitle.attachments.filter(item => item.module === type) || []
    },
    refreshChoice(res) {
      if (res) {
        this.$nextTick(() => {
          this.question[0] = res
          this.refreshKey = !this.refreshKey
        })
        return
        
      }
      const obj = this.exerciseInfo.submittedQuestions
      this.$nextTick(() => {
        this.question = obj.filter(item => item.questionId + '' === this.itemdata.id)
        // console.log(this.question);
        this.refreshKey = !this.refreshKey
      })
    },
    
    isunanswered() {
      if (this.answer) {
        return this.answer.every(item => {
          item === ''
        })
      }
      
      if (this.itemdata.testResult.answer !== undefined) {
        return this.itemdata.testResult.answer.every(item => {
          item === ''
        })
      }
    },
    handleClickImage (imagesUrl) {
      if (imagesUrl === undefined) return;
      const images = [imagesUrl]
      ImagePreview({
        images
      })
    },
    changeUpIcon() {
      this.isShowUpIcon = true
      this.isShowDownIcon = false
    },
    changeDownIcon() {
      this.isShowUpIcon = false
      this.isShowDownIcon = true
    },
    submitTopic() {
      const thereNoAnswer = this.answer.some(item => {
        return item === '' 
      })
      if (thereNoAnswer && this.exerciseMode === '1') {
        Dialog.confirm({
          message: '当前题目暂未作答，您确认提交吗？',
          confirmButtonText: '继续答题',
          cancelButtonText:'确认'
        })
        .then(() => {
          // on confirm
        })
        .catch(() => {
          this.$emit('submitSingleAnswer', this.answer, this.itemdata);
        });
      } else {
        this.radioDisabled = true
        this.$emit('submitSingleAnswer', this.answer, this.itemdata);
      }
    },
    goResults() {
      this.$emit('goResults');
    }
  },
};
</script>
<style scoped lang="scss">
  ::v-deep .van-field__control {
    padding: vw(12) vw(16);
    height: vw(46) !important;
    border-radius: 6px;
    border: 1px solid #E5E6EB;
  }

  // 改变placeholder颜色 谷歌 火狐 IE
  ::v-deep .van-field__control::-webkit-input-placeholder {
    color: #D2D3D4;
  }
  ::v-deep .van-field__control:-moz-placeholder {
    color: #D2D3D4;
  }
  ::v-deep .van-field__control::-moz-placeholder {
    color: #D2D3D4;
  }
  // 隐藏文本域的滚动条
  ::v-deep .van-field__control::-webkit-scrollbar {
    display: none;
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
  .fill-status {
    width: 18px;
    height: 18px;
  }
</style>