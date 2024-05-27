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
          {{ $t('courseLearning.correctAnswer') }}：
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
          <div v-else ref="aiAnalysis">{{ $t('courseLearning.noParsing') }}</div>
        </div>
        <div class="ai-analysis" v-show="itemdata.aiAnalysisEnable">
          <p class="ai-tittle">{{$t('courseLearning.aiAssistant')}}</p>
          <div class="ai-content">
            <div class="ai-content-left">
              <button class="ai-btn"  @click="aiGeneration()"  v-show="isShowAiExplain">
                <img src="static/images/explain-ai.png" class="ai-img" />
                <span class="ai-left-text">{{$t('courseLearning.analysis')}}</span>
              </button>
              <button class="ai-stopbtn" @click="stopAiGeneration()"  v-show="stopAiExplain">
                <img src="static/images/explain-stop.png" class="ai-img" />
                <span class="ai-left-text">{{$t('courseLearning.stopGeneration')}}</span>
              </button>
              <button class="ai-stopbtn" @click="anewAiGeneration" v-show="anewAiExplain">
                <img src="static/images/explain-anew.png" class="ai-img" />
                <span class="ai-left-text">{{$t('courseLearning.reGenerate')}}</span>
              </button>
              <p class="ai-left-tittle" v-show="stopAiExplain">{{$t('courseLearning.beGenerating')}}</p>
            </div>
            <div ai-content-right>
              <img src="static/images/explain-ai-img.png" class="ai-right-img" />
            </div>
          </div>
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
import isShowFooterShardow from '@/mixins/lessonTask/footerShardow';
import refreshChoice from '@/mixins/lessonTask/swipeRefResh.js';
import handleClickImage from '@/mixins/lessonTask/handleClickImage.js';
import { Dialog } from 'vant'
import store from "@/store";

const WINDOWWIDTH = document.documentElement.clientWidth

export default {
  name: 'FillType',
  components: {
    attachementPreview
  },
  mixins: [isShowFooterShardow, refreshChoice, handleClickImage],
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
      answerData: {},
      stopAnswer: {},
      isShowAiExplain: true,
      stopAiExplain: false,
      anewAiExplain: false
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
          return this.$t('courseLearning.placeEntAnswer');
        } else {
          return this.$t('wrongQuestion.unanswered');
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
          message: this.$t('courseLearning.questionNotAnswer'),
          confirmButtonText: this.$t('courseLearning.continueAnswer'),
          cancelButtonText: this.$t('btn.confirm')
        })
        .then(() => {
          // on confirm
        })
        .catch(() => {
          this.$emit('changeTouch')
          this.$emit('submitSingleAnswer', this.answer, this.itemdata);
        });
      } else {
        this.$emit('changeTouch')
        this.radioDisabled = true
        this.$emit('submitSingleAnswer', this.answer, this.itemdata);
      }
    },
    goResults() {
      this.$emit('goResults');
    },
    async getAiAnalysis() {
      console.log(this.itemdata)
      const questionId = this.itemdata.id
      const data = {
        role: "student",
        questionId,
        answerRecordId: this.exerciseInfo.id,
      }
      let messageEnd = false;
      let answers = [];
      this.answerData[questionId] = '';
      this.stopAnswer[questionId] = false;
      const typingTimer = setInterval(() => {
        if (answers.length === 0) {
          return;
        }
        if (this.stopAnswer[questionId]) {
          clearInterval(typingTimer);
        }
        this.answerData[questionId] += answers.shift();
        if (answers.length === 0 && messageEnd) {
          clearInterval(typingTimer)
          this.stopAiExplain = false;
          this.anewAiExplain = true;
        }
        this.$refs.aiAnalysis.innerHTML = this.answerData[questionId];
      }, 50);
      const response = await fetch("/api/ai/question_analysis/generate", {
        method: "POST",
        headers: {
          "Content-Type": "application/json;charset=utf-8",
          Accept: "application/vnd.edusoho.v2+json",
          'X-Auth-Token': store.state.token,
        },
        body: JSON.stringify(data),
      });
      const reader = response.body.getReader();
      const decoder = new TextDecoder();
      let lastMessgae = "";
      while (true) {
        const { done, value } = await reader.read();
        const messages = (lastMessgae + decoder.decode(value)).split("\n\n");
        let key = 1;
        for (let message of messages) {
          if (key == messages.length) {
            lastMessgae = message;
          } else {
            const parseMessage = JSON.parse(message.slice(5));
            if (parseMessage.event === "message") {
              answers.push(parseMessage.answer);
            }
            key++;
          }
        }
        if (done) {
          messageEnd = true;
          break;
        }
      }
    },
    stopAiAnalysis() {
      const questionId = this.itemdata.id;
      this.stopAnswer[questionId] = true;
    },
    aiGeneration() {
      this.isShowAiExplain = false;
      this.stopAiExplain = true;
      this.anewAiExplain = false;
      this.getAiAnalysis();
    },
    stopAiGeneration() {
      this.stopAiExplain = false;
      this.isShowAiExplain = false;
      this.anewAiExplain = true;
      this.stopAiAnalysis();
    },
    anewAiGeneration() {
      this.getAiAnalysis();
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
  .ai-analysis {
    margin-top: 16px;
    padding: 16px;
    background-color: #F5F5F5;
    border: 1px dashed rgba(66, 143, 250, 0.30);
    line-height: 20px;
    border-radius: 4px;
    .ai-tittle {
      color: #428FFA;
      font-size: 12px;
      font-style: normal;
      font-weight: 400;
      line-height: 20px;
    }
    .ai-content {
      display: flex;
      justify-content: space-between;
      .ai-content-left {
        .ai-btn {
          margin-top: 16px;
          padding: 4px 15px;
          font-size: 14px;
          color: #fff;
          border-style: none;
          background-color: #428FFA;
          border-radius: 4px;
          .ai-img {
            margin-right: 5px;
            width: 23px;
            height: 23px;
          }
        }
        .ai-stopbtn {
          margin-top: 16px;
          padding: 4px 15px;
          font-size: 14px;
          color: #428FFA;
          border-radius: 4px;
          border: 1px solid #428FFA;
          .ai-img {
            margin-right: 5px;
            width: 18px;
            height: 18px;
          }
        }
        .ai-left-tittle {
          margin-top: 5px;
          color: #919399;
          font-size: 12px;
          font-style: normal;
          font-weight: 400;
          line-height: 20px;
        }
      }
    }
    .ai-right-img {
      width: 44.8px;
      height: 56px;
    }
  }
</style>
