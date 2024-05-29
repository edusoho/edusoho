<template>
  <div>
    <div style="height: 100% !important">

      <div class="ibs-subject-material" v-if="currentItem.type === 'material'" >
        <div :class="['ibs-material-stem-nowrap', isShowDownIcon ? 'ibs-material-stem' : '']">
          <span class="ibs-material-tags">
            {{ $t('courseLearning.material') }}
          </span>
          <span :id="`current${currentItem.id}`" class="ibs-material-text ibs-material-icon" v-html="currentItem.material" @click="handleClickImage($event.target.src)"></span>
        </div>
        <i @click="changeUpIcon" :class="['iconfont', 'icon-arrow-up', {'ibs-show-up-icon': isShowDownIcon }]"></i>
        <i @click="changeDownIcon" :class="['iconfont', 'icon-arrow-down', {'ibs-show-down-icon': isShowUpIcon}]"></i>
        <attachement-preview
          v-for="item in getAttachementByType('material')"
          :attachment="item"
          :key="item.id"
        />
      </div>
      <div class="ibs-subject-card">
        <div class="ibs-subject-stem">
          <span v-if="currentItem.type !== 'material'" class="ibs-tags">
            {{ subject }}
          </span>
          <div>
            <span v-if="currentItem.type === 'material'" class="ibs-serial-number"><span class="ibs-material-type">[{{ subject }}] </span> {{ Number(commonData.current) }}、</span>
            <span v-else class="ibs-serial-number">{{ Number(commonData.current) }}、</span>
            <div class="ibs-rich-text" v-html="getStem()" @click="handleClickImage($event.target.src)"/>
          </div>
        </div>
        <attachement-preview
          v-for="item in getAttachmentTypeData('stem')"
          :attachment="item"
          :key="item.id"
        />
        <div class="ibs-subject">
          <van-radio-group
            v-model="answer"
            class="ibs-answer-paper"
            :disabled="!disabledData"
            @change="changeAnswer"
            :refreshKey="refreshKey"
          >
            <van-radio
              v-for="(item, index) in itemData.question.response_points"
              :key="index"
              :name="item.radio.val"
              :class="['ibs-subject-option',
              {active: brushDo.status === 'doing' && answer[0] === item.radio.val},
              {'van-checked__right': RadioRight(item.radio.val)},
              {'van-checked__wrong': RadioWrong(item.radio.val)}
              ]"
            >
              <i class="iconfont icon-a-Frame34723"></i>
              <i class="iconfont icon-zhengque1"></i>
              <i class="iconfont icon-cuowu2"></i>
              <div class="ibs-subject-option__content" v-html="item.radio.text" />
              <template #icon="props">
                <span
                  :class="[
                    'ibs-subject-option__order',
                    props,
                    reportAnswer(
                      itemData.mode,
                      item.radio.val,
                      itemData.userAnwer,
                      itemData.question.answer
                    )
                  ]"
                  >{{ item.radio.val + '.' }}</span
                >
              </template>
            </van-radio>
          </van-radio-group>
        </div>
        <div v-if="!disabledData" class="ibs-one-questions-analysis">
          <div class="flex justify-between ibs-analysis-answer">
            <div class="flex items-center">
              <span class="ibs-answer">{{ $t('courseLearning.referenceAnswer') }}：</span>
              <span class="ibs-options" style="color:#00B42A;">{{ commonData.answer[0] }}</span>
            </div>
            <div v-if="answer.length > 0 && answer[0] != ''" class="flex items-center">
              <span class="ibs-answer">{{ $t('courseLearning.selectedAnswer') }}：</span>
              <span class="ibs-options">{{ answer[0] }}</span>
            </div>
          </div>
          <div v-if="$route.query.type == 'assessment'" class="ibs-analysis-color mb-8">
            {{ $t('courseLearning.score') }}：{{ commonData.report ? commonData.report.score : 0.0 }}
          </div>
          <div v-if="$route.query.type == 'assessment'" class="ibs-analysis-color mb-8">
            {{ $t('courseLearning.comment') }}：{{ commonData.report ? commonData.report.comment === '' ? '--' : commonData.report.comment : '' }}
          </div>
          <div class="ibs-analysis-color">
            <span class="float-left">{{ $t('courseLearning.analyze') }}：</span>
            <span v-if="commonData.analysis" v-html="commonData.analysis" />
            <span v-else ref="aiAnalysis">{{ $t('courseLearning.noParsing') }}</span>
          </div>
          <div class="ai-analysis" v-show="commonData.aiAnalysisEnable">
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
            v-for="item in getAttachmentTypeData('analysis')"
            :attachment="item"
            :key="item.id" />
        </div>
      </div>
      <div v-if="isShowFooterShadow()" class="ibs-footer-shadow">
      </div>
      <div v-if="!disabledData && currentItem.type === 'material'" class="ibs-subject-footer">
        <span class="float-left">{{ $t('courseLearning.analyze') }}：</span>
        <span v-if="currentItem.analysis !== ''" v-html="currentItem.analysis" />
        <span v-else>{{ $t('courseLearning.noParsing') }}</span>
        <attachement-preview
          v-for="item in getAttachementByType('analysis')"
          :attachment="item"
          :key="item.id" />
      </div>
      <div v-if="isAnswerFinished == 1" class="ibs-submit-footer" :style="{width:width+ 'px'}">
        <van-button
          class="ibs-submit-footer-btn"
          :style="{width:width - 20 + 'px'}"
          type="primary"
          @click="goBrushResult()"
          >{{ $t('courseLearning.viewResult2') }}</van-button
        >
      </div>
    </div>
  </div>
</template>
<script>
import questionMixins from "@/src/mixins/questionMixins.js";
import reportAnswer from "@/src/mixins/reportAnswer.js";
import answerMode from "@/src/utils/filterAnswerMode";
import itemBankMixins from "@/src/mixins/itemBankMixins.js"
import attachementPreview from "./attachement-preview.vue";
import store from "@/store";

const WINDOWWIDTH = document.documentElement.clientWidth

export default {
  name: "single-choice",
  mixins: [questionMixins, reportAnswer, itemBankMixins],
  data() {
    return {
      answer: this.itemData.userAnwer,
      width: WINDOWWIDTH,
      isShowDownIcon: null,
      isShowUpIcon: false,
      question: [],
      refreshKey: true,
      answerData: {},
      stopAnswer: {},
      isShowAiExplain: true,
      stopAiExplain: false,
      anewAiExplain: false
    };
  },
  components: {
    attachementPreview
  },
  props: {
    exerciseInfo: {
      type: Object,
      default: () => {}
    },
  },
  inject: ['brushDo'],
  computed: {
    subject() {
      return `${answerMode(this.commonData.questionsType)}`;
    }
  },
  mounted() {
    const stemDom = document.getElementById(`current${this.currentItem.id}`)
    if (stemDom) {
      this.isShowDownIcon = stemDom.childNodes[0].offsetWidth > 234
    }
  },
  methods: {
    changeAnswer(e) {
      if (this.itemData.mode !== "do") {
        return;
      }
      const data = {
        item_id: this.commonData.item_id,
        question_id: this.commonData.questionId,
        type: 'single-choice',
        seq: Number(this.commonData.current)
      }
      this.$emit("changeAnswer", e, this.itemData.keys, data);
    },

    RadioRight(radioItem) {
      if (!this.wrong) {
        if(this.disabledData) return false;
      }

      // 没有答题显示选中正确答案 || 选中错误，正确答案显示
      if(this.answer.length === 0 && radioItem === this.commonData.answer[0] || radioItem === this.commonData.answer[0]) {
        return true;
      }

      // 选中项与正确答案一致
      if (radioItem === this.answer[0] && this.answer[0] === this.commonData.answer[0]) {
        return true;
      }
    },
    RadioWrong(radioItem) {
      if (!this.wrong) {
        if(this.disabledData) return false;
      }
      // 选择项等于当前项，并且选择项不等于正确答案
      if(this.answer[0] === radioItem && this.answer[0] !== this.commonData.answer[0]){
        return true;
      }
    },
    getAttachmentTypeData(type) {
      return this.attachements.filter(item => item.module === type);
    },
    getAttachementByType(type) {
      return this.currentItem.attachments.filter(item => item.module === type);
    },
    goBrushResult() {
      if(this.brushDo.type === "wrongQuestionBook") {
        this.brushDo.goResult()
      } else if (this.brushDo.type === "lessonTask") {
        this.$emit('goBrushResult')
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
    async getAiAnalysis() {
      const questionId = this.commonData.questionId
      const data = {
        role: "student",
        questionId,
        answerRecordId: this.exerciseInfo.id,
      }
      let messageEnd = false;
      const answers = [];
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
        for (const message of messages) {
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
      const questionId = this.commonData.questionId
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
  }
};
</script>
<style lang="scss" scoped>
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
  .ibs-show-down-icon {
    display: block;
    cursor: pointer;
  }
  .ibs-show-up-icon {
    display: block;
    cursor: pointer;
  }
  .ai-analysis {
    margin-top: 12px 16px;
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
            height: 16px;
          }
        }
        .ai-stopbtn {
          margin-top: 16px;
          padding: 4px 15px;
          height: 32px;
          color: #428FFA;
          border-radius: 4px;
          border: 1px solid #428FFA;
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
