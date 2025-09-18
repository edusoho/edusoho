<template>
  <div>
    <div v-if="itemdata.parentTitle" class="subject-material">
      <div :class="['material-stem-nowrap', isShowDownIcon ? 'material-stem' : '']">
        <span v-if="itemdata.parentTitle" class="material-tags">
          {{ subject }}
        </span>
        <span class="material-text material-icon" v-html="stem" @click="handleClickImage($event.currentTarget)" >
        </span>
      </div>
      <i @click="changeUpIcon" :class="['iconfont', 'icon-arrow-up', {'show-up-icon': isShowDownIcon }]"></i>
      <i @click="changeDownIcon" :class="['iconfont', 'icon-arrow-down', {'show-down-icon': isShowUpIcon}]"></i>
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
        <div class="rich-text" v-html="stem" @click="handleClickImage($event.currentTarget)" />
      </div>

      <div v-if="itemdata.parentTitle" :class="['material-title',{'material-title-weight': itemdata.parentTitle}]">
        <span class="serial-number"><span class="material-type">[{{ $t('courseLearning.singleChoice') }}] </span> {{ itemdata.materialIndex }}、</span>
        <div class="rich-text" v-html="itemdata.stem" @click="handleClickImage($event.currentTarget)" />
      </div>
      <attachement-preview
        v-for="item in getAttachementByType('stem')"
        :canLoadPlayer="isCurrent"
        :attachment="item"
        :key="item.id" />
      <van-radio-group v-model="radio" :class="['answer-paper',{'convention': mode !== 'exercise'}]" :key="refreshKey" @change="choose">
        <van-radio
          v-for="(item, index) in itemdata.metas.choices"
          :key="index"
          :name="index"
          :disabled="!disabledData"
          :class="['subject-option',
                    !canDo ? checkAnswer(index, itemdata) : '',
                    { active: index === currentItem },
                    { 'van-checked__right' : itemdata.answer ? itemdata.answer[0] === index : ''},
                    {isRight: question.length > 0 && filterOrder(index).replace('.','') === question[0].answer[0]},
                    {isWrong: question.length > 0 && filterOrder(index).replace('.','') === question[0].response[0] && question[0].response[0] !== question[0].answer[0]}
                  ]"
        >
          <i class="iconfont icon-cuowu2"></i>
          <i class="iconfont icon-zhengque1"></i>
          <i class="iconfont icon-a-Frame34723"></i>
          <div :class="['subject-option__content', canDo ? '' : 'not-can-do' ]" v-html="item" @click="handleClickImage($event.currentTarget)" />
          <span slot="icon" class="subject-option__order">
            {{ index | filterOrder }}</span
          >
        </van-radio>
      </van-radio-group>
      <div v-if="!disabledData" class="one-questions-analysis">
        <div class="flex justify-between analysis-answer">
          <div class="flex items-center">
            <span class="answer">{{ $t('courseLearning.referenceAnswer') }}：</span>
            <span class="options" style="color:#00B42A;">{{ itemdata.answer | filterAnswer }}</span>
          </div>
          <div v-if="question.length > 0 && question[0].response[0].length > 0 || testResult.answer && testResult.answer.length > 0" class="flex items-center">
            <span class="answer">{{ $t('courseLearning.selectedAnswer') }}：</span>
            <span class="options">{{ question.length > 0 ? question[0].response[0] : filterAnswer(testResult.answer) }}</span>
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
          <span v-if="analysis" v-html="analysis" @click="handleClickImage($event.currentTarget)" />
          <span v-else ref="aiAnalysis">{{ $t('courseLearning.noParsing') }}</span>
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
              <button class="ai-stopbtn" @click="aiGeneration()" v-show="anewAiExplain">
                <img src="static/images/explain-anew.png" class="ai-img" />
                <span class="ai-left-text">{{$t('courseLearning.reGenerate')}}</span>
              </button>
              <p class="ai-left-tittle" v-show="stopAiExplain">{{$t('courseLearning.beGenerating')}}</p>
            </div>
            <div>
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
      <span class="float-left">{{ $t('courseLearning.analyze') }}：</span>
      <span v-if="parentTitleAnalysis !== ''" v-html="parentTitleAnalysis" @click="handleClickImage($event.currentTarget)" />
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
import checkAnswer from '@/mixins/lessonTask/itemBank';
import isShowFooterShardow from '@/mixins/lessonTask/footerShardow';
import refreshChoice from '@/mixins/lessonTask/swipeRefResh.js';
import attachementPreview from './attachement-preview.vue';
import store from '@/store';
import itemBankMixins from '@/src/mixins/itemBankMixins';

const WINDOWWIDTH = document.documentElement.clientWidth

export default {
  name: 'SingleChoice',
  filters: {
    filterOrder(index) {
      const arr = ['A.', 'B.', 'C.', 'D.', 'E.', 'F.', 'G.', 'H.', 'I.', 'J.', 'K.', 'L.', 'M.', 'N.', 'O.', 'P.', 'Q.', 'R.', 'S.', 'T.', 'U.', 'V.', 'W.', 'X.', 'Y.', 'Z.'];
      return arr[index];
    },
    filterAnswer(index) {
      const arr = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
      return arr[index];
    },
  },
  mixins: [checkAnswer, isShowFooterShardow, refreshChoice, itemBankMixins],
  components: {
    attachementPreview
  },
  props: {
    itemdata: {
      type: Object,
      default: () => {},
    },
    answer: {
      type: Array,
      default: () => [],
    },
    canDo: {
      type: Boolean,
      default: true,
    },
    isCurrent: Boolean,
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
    status: {
      type: String,
      default: null
    },
    exerciseMode: {
      type: String,
      default: '',
    },
    exerciseInfo: {
      type: Object,
      default: () => {}
    },
    analysis: {
      type: String,
      default: '',
    },
    parentTitleAnalysis: {
      type: String,
      default: '',
    },
    parentType: {
      type: String,
      default: '',
    },
    testResult: {
      type: Object,
      default: () => {},
    },
    mode: {
      type: String,
      default: '',
    },
    disabledData: {
      type: Boolean,
      default: false
    },
    isExercise: {
      type: Boolean,
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
      question: [],
      refreshKey: true,
      myAnswer:'C',
      width: WINDOWWIDTH,
      answerData: {},
      stopAnswer: {},
      isShowAiExplain: true,
      stopAiExplain: false,
      anewAiExplain: false
    };
  },
  mounted() {
    if (this.canDo && this.mode === 'exercise') {
      this.refreshChoice()
    }
    this.isShowDownIcon = document.getElementsByClassName('material-icon')[this.number]?.childNodes[0].offsetWidth > 234
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
  methods: {
    filterOrder(index) {
      return this.$options.filters.filterOrder(index);
    },
    filterAnswer(index) {
      return this.$options.filters.filterAnswer(index);
    },

    // 向父级提交数据
    choose(name) {
      this.currentItem = name
      this.$emit('singleChoose', this.radio, this.itemdata);
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
    async getAiAnalysis() {
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
          clearInterval(typingTimer);
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
    }
  },
};
</script>
<style scoped lang="scss">
::v-deep .van-radio {
  position: relative;
  display: block;
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
.not-can-do {
  margin-right: vw(40);
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

.ai-analysis {
  margin-top: 12px 16px;
  padding: 16px;
  background-color: #FAFAFA;
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
        height: 32px;
        color: #fff;
        border-radius: 4px;
        border: 1px solid #428FFA;
        border-style: none;
        background-color: #428FFA;
        .ai-left-text {
          font-size: 14px;
          color: #fff;
          line-height: 16px;
        }
        .ai-img {
          margin-right: 5px;
          width: 16px;
          height: 17px;
        }
      }
      .ai-stopbtn {
        margin-top: 16px;
        padding: 4px 15px;
        height: 32px;
        color: #428FFA;
        border-radius: 4px;
        border: 1px solid #428FFA;
        background: #fff;
        .ai-left-text {
          font-size: 14px;
          color: #428FFA;
          line-height: 16px;
        }
        .ai-img {
          margin-right: 10px;
          width: 16px;
          height: 16px;
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
