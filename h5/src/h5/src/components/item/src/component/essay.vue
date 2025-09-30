<template>
  <div>
    <div style="height: 100% !important">

      <div class="ibs-subject-material" v-if="currentItem.type === 'material'" >
        <div :class="['ibs-material-stem-nowrap', isShowDownIcon ? 'ibs-material-stem' : '']">
          <span class="ibs-material-tags">
            {{ $t('courseLearning.material') }}
          </span>
          <span :id="`current${currentItem.id}`" class="ibs-material-text" v-html="currentItem.material" @click="handleClickImage($event.currentTarget)"></span>
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
            <div class="ibs-rich-text" v-html="getStem()" @click="handleClickImage($event.currentTarget)" />
          </div>
        </div>
        <attachement-preview
          v-for="item in getAttachmentTypeData('stem')"
          :attachment="item"
          :key="item.id"
        />
        <div v-if="disabledData" class="ibs-subject ibs-essay">
          <div class="ibs-answer-paper">
            <van-field
              v-model="answerText"
              :placeholder="placeholder"
              :autosize="{ maxHeight: 132, minHeight: 132 }"
              :disabled="!disabledData"
              class="ibs-essay-input"
              label-width="0px"
              type="textarea"
              @input="changeAnswer"
            />
            <div v-if="!disabled" class="discussion-create__upload">
              <van-uploader
                v-model="fileList"
                :after-read="afterRead"
                :max-count="5"
                @delete="deleteImgItem"
              />
            </div>
          </div>
        </div>
        <div v-if="!disabledData" class="ibs-answer-paper">
          <div v-if="!disabledData" class="ibs-answer-paper">
            <div class="ibs-your-answer">{{ $t('courseLearning.yourAreAnswer') }}：</div>
            <div>
              <div>
                <img v-if="status === 'right' || commonData.report.status === 'right'" :src="rigth" alt="" class="ibs-fill-status">
                <img v-if="status === 'wrong' || commonData.report.status === 'wrong' || commonData.report.status === 'no_answer'" :src="wrongImg" alt="" class="ibs-fill-status">
                <span v-if="!answer" :class="['ibs-your-answer', {'ibs-is-wrong-answer': status === 'wrong'}, {'ibs-is-right-answer': status === 'right'}, {'ibs-is-right-answer' :  commonData.report.status === 'right'}, {'ibs-is-wrong-answer' : commonData.report.status === 'wrong'}]"> {{ $t('courseLearning.unanswered') }}</span>
                <span v-else :class="[
                    'ibs-essay-answer',
                    {'ibs-is-wrong-answer': status === 'wrong'},
                    {'ibs-is-right-answer': status === 'right'},
                    {'ibs-is-right-answer': commonData.report.status === 'right'},
                    {'ibs-is-wrong-answer': commonData.report.status === 'wrong'},
                  ]" style="color: #37393D;" v-html="answer" @click="handleClickImage($event.currentTarget)"></span>
              </div>
            </div>
            <div class="ibs-your-answer mt-16">
              {{ $t('courseLearning.correctAnswer') }}：
            </div>
            <div class="mb-16">
              <span class="ibs-is-right-answer" v-html="commonData.answer[0]" @click="handleClickImage($event.currentTarget)"/>
            </div>
            <div v-if="$route.query.type == 'assessment'" class="ibs-analysis-color mb-8">
              {{ $t('courseLearning.score') }}：<div>{{ commonData.report ? commonData.report.score : 0.0 }}</div>
            </div>
            <div v-if="$route.query.type == 'assessment'" class="ibs-analysis-color mb-8">
              {{ $t('courseLearning.comment') }}：<div>{{ commonData.report ? commonData.report.comment === '' ? '--' : commonData.report.comment : '' }}</div>
            </div>
            <div class="ibs-analysis-color">
              {{ $t('courseLearning.analyze') }}：
              <span v-if="commonData.analysis" v-html="commonData.analysis" @click="handleClickImage($event.currentTarget)"/>
              <div v-else ref="aiAnalysis">{{ $t('courseLearning.noParsing') }}</div>
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
              v-for="item in getAttachmentTypeData('analysis')"
              :attachment="item"
              :key="item.id" />
          </div>
        </div>
      </div>
      <div v-if="isShowFooterShadow()" class="ibs-footer-shadow">
      </div>
      <div v-if="!disabledData && currentItem.type === 'material'" class="ibs-subject-footer">
        {{ $t('courseLearning.analyze') }}：
        <span v-if="currentItem.analysis !== ''" v-html="currentItem.analysis" @click="handleClickImage($event.currentTarget)"/>
        <span v-else>{{ $t('courseLearning.noParsing') }}</span>
        <attachement-preview
          v-for="item in getAttachementByType('analysis')"
          :attachment="item"
          :key="item.id" />
      </div>
    </div>
    <div class="self-judging" v-if="!disabledData && mode === 'do'">
      <div class="self-judging-title">{{ $t('courseLearning.selfJudging') }}</div>
      <div class="self-judging-change-radio">
        <van-radio-group v-model="status" :disabled="reviewDisabled" class="self-judging-group-radio" @change="changeEssayRadio">
          <van-radio name="right" class="self-judging-radio">
            {{ $t('courseLearning.haveMastered') }}
            <template #icon="props">
              <img
                class="img-icon"
                :src="props.checked ? activeIcon : defaultIcon"
              />
              <i v-show="props.checked" class="iconfont icon-check"></i>
            </template>
          </van-radio>
          <van-radio name="wrong" class="self-judging-radio">
            {{ $t('courseLearning.notQuiteUnderstand') }}
            <template #icon="props">
              <img
                class="img-icon"
                :src="props.checked ? activeQuestions : defaultQuestions"
              />
              <i v-show="props.checked" class="iconfont icon-check"></i>
            </template>
          </van-radio>
        </van-radio-group>
      </div>
    </div>
    <div v-if="disabledData && brushDo.exerciseModes === '1'" class="ibs-submit-footer" :style="{width:width+ 'px'}">
      <van-button
        class="ibs-submit-footer-btn"
        :style="{width:width - 20 + 'px'}"
        type="primary"
        @click="submitTopic"
        >{{ $t('courseLearning.submitATopic') }}</van-button
      >
    </div>
    <div v-if="!reviewDisabled && !disabledData && mode === 'do'" class="ibs-submit-footer" :style="{width:width+ 'px'}" @click="singleQuestionSubmission">
      <van-button
        class="ibs-submit-footer-btn"
        :style="{width:width - 20 + 'px'}"
        type="primary"
        >{{ $t('wrongQuestion.completeReview') }}</van-button
      >
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
</template>

<script>
import questionMixins from "@/src/mixins/questionMixins.js";
import answerMode from "@/src/utils/filterAnswerMode";
import itemBankMixins from "@/src/mixins/itemBankMixins.js"
import attachementPreview from "./attachement-preview.vue";
import Api from '@/api';
import { Dialog, Toast } from 'vant'

import { debounce } from "@/src/utils/debounce.js";
import store from "@/store";

const WINDOWWIDTH = document.documentElement.clientWidth
export default {
  name: "essay",
  mixins: [questionMixins, itemBankMixins],
  inject: ['brushDo'],
  data() {
    return {
      answer: this.itemData.userAnwer[0],
      fileList: [],
      imgs: [],
      fileStr: '',
      answerText: '',
      width: WINDOWWIDTH,
      isShowDownIcon: null,
      isShowUpIcon: false,
      status: '',
      activeIcon: 'static/images/itemBankExercise/grasp-active.png',
      defaultIcon: 'static/images/itemBankExercise/grasp.png',
      activeQuestions: 'static/images/itemBankExercise/not-master-active.png',
      defaultQuestions: 'static/images/itemBankExercise/not-master.png',
      question: [],
      refreshKey: true,
      reviewDisabled: false,
      rigth: 'static/images/exercise/rigth.png',
      wrongImg: 'static/images/exercise/wrong.png',
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
  computed: {
    placeholder: {
      get() {
        if (this.disabled) {
          return this.$t('wrongQuestion.unanswered');
        } else {
          return this.$t('courseLearning.yourAreAnswer') + '...';
        }
      }
    },
    subject() {
      return `${answerMode(this.commonData.questionsType)}`;
    }
  },
  watch: {
    answerText: {
      handler(data) {
        this.answer = data + this.fileStr
      },
    },
    imgs: {
      handler(data) {
        this.fileStr = ''
        data.forEach(item => {
          this.fileStr += `<img src="${item}"></img>`
        })
        this.answer = this.answerText + this.fileStr
        this.changeAnswer()
      },
      deep: true,
    }
  },
  mounted() {
    this.initReviewQuestion()
    this.refreshReviewStatus()
    this.initEssayRadio()

    const stemDom = document.getElementById(`current${this.currentItem.id}`)
    if (stemDom) {
      this.isShowDownIcon = stemDom.childNodes[0].offsetWidth > 234
    }
  },
  methods: {
    // 批阅按钮变化状态保留
    initEssayRadio() {
      const essayRadio = this.EssayRadio.filter(item => item.questionId + '' === this.itemData.question.id)
      if (essayRadio.length > 0) {
        this.status = essayRadio[0].status
        const reviewQuestioned = this.reviewedQuestion.filter(item => item.questionId + '' === this.itemData.question.id)
        if (reviewQuestioned.length === 0 && this.reviewedQuestion.length !== 0) {
          this.reviewDisabled = false
        }
      }
    },
    // 批阅完成状态保留
    initReviewQuestion() {
      const reviewQuestioned = this.reviewedQuestion.filter(item => item.questionId + '' === this.itemData.question.id)
      if (reviewQuestioned.length > 0) {
        this.status = reviewQuestioned[0].status
        this.reviewDisabled = true
      }
    },
    // 刷新后获取状态
    refreshReviewStatus() {
      const arr = this.exerciseInfo.filter(item => item.questionId + '' === this.itemData.question.id)
      if (arr.length > 0) {
        this.status = arr[0].status
        this.reviewDisabled = true
      }
    },
    changeAnswer(data) {
      const that = this;
      debounce(
        function() {
          const currentInfo = {
            item_id: that.commonData.item_id,
            question_id: that.commonData.questionId,
            type: 'essay',
          }
          that.$nextTick(()=> {
            that.$emit("changeAnswer", that.answer, that.itemData.keys, currentInfo);
          })
        },
        500,
        true
      )();
    },
    afterRead(file) {
      const formData = new FormData();
      formData.append('file', file.content);
      formData.append('group', 'course');
      Api.updateFile({
        data: formData,
      })
        .then(res => {
          this.imgs.push(res.uri);
        })
        .catch(err => {
          Toast.fail(err.message);
        });
    },
    deleteImgItem(e, detail) {
      this.imgs.splice(detail.index, 1);
    },
    getAttachmentTypeData(type) {
      return this.attachements.filter(item => item.module === type);
    },
    getAttachementByType(type) {
      return this.currentItem.attachments.filter(item => item.module === type);
    },
    submitTopic() {
      const data = {
        item_id: this.commonData.item_id,
        question_id: this.commonData.questionId,
        seq: Number(this.commonData.current)
      }
      const currentAnswer = new Array(this.answer)

      if (this.answer) {
        this.$emit('changeTouch')
        this.$emit('submitSingleAnswer', currentAnswer, data);
        return
      }

      Dialog.confirm({
        message: this.$t('courseLearning.questionNotAnswer'),
        confirmButtonText: this.$t('courseLearning.continueAnswer'),
        cancelButtonText: this.$t('btn.confirm')
      })
      .then(() => {
        // on confirm
      })
      .catch(() => {
        this.$emit('submitSingleAnswer', currentAnswer, data);
        this.$emit('changeTouch')
      });
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
    singleQuestionSubmission() {
      if (this.status === '') {
        Dialog.alert({
          message: this.$t('wrongQuestion.questionNotReviewed'),
        }).then(() => {
          // on close
        });
        return
      }

      Api.singleQuestionSubmission({
        query:{
          id: this.brushDo.answerRecord.id
        },
        data:{
          admission_ticket: this.brushDo.answerRecord.admission_ticket,
          assessment_id: this.brushDo.answerRecord.assessment_id,
          section_id: this.currentItem.section_id,
          item_id: this.commonData.item_id,
          question_id: this.commonData.questionId,
          status: this.status
        }
      }).then((res)=> {
        Toast({
          message: this.$t('wrongQuestion.completeReview'),
          icon: 'passed',
        });
        this.status = res.status
        this.reviewDisabled = true
        const data = {
          status: res.status,
          questionId: res.questionId
        }
        if (res.status === 'right') {
          this.$emit('nextSkipQuestion')
        }
        this.$emit("updataIsAnswerFinished", res.isAnswerFinished, true, data, res.questionId)
      }).catch((err)=>{
        Toast.fail(err.message)
      })
    },
    changeEssayRadio(e) {
      const data = {
        status: e,
        questionId: this.commonData.questionId
      }
      this.$emit('changeEssayRadio', data)
    },
    async getAiAnalysis() {
      const questionId = this.commonData.questionId;
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
      const questionId = this.commonData.questionId;
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
  }
};
</script>
<style scoped lang="scss">
  .discussion-create__upload {
    margin-top: vw(12);

    /deep/.van-uploader__wrapper :nth-child(4) {
      margin: 0 0;
    }
    /deep/.van-uploader__upload {
      margin: 0 0;
      width: vw(64) !important;
      height: vw(64) !important;
      border-radius: vw(4);
      overflow: hidden;
    }
    /deep/.van-uploader__preview {
      margin: 0 vw(16) vw(16) 0;
    }
    /deep/.van-uploader__preview-image {
      width: vw(64) !important;
      height: vw(64) !important;
      border-radius: vw(4);
      overflow: hidden;
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
  .ibs-show-down-icon {
    display: block;
    cursor: pointer;
  }
  .ibs-show-up-icon {
    display: block;
    cursor: pointer;
  }
  .self-judging-group-radio {
    ::v-deep .van-radio__icon {
      position: relative;
      margin-bottom: vw(8);
      height: vw(64);
    }

    ::v-deep .van-radio__label {
      margin-left: 0;
      color: #919399;
      font-size: 12px;
      line-height: 22px;
    }
    .img-icon {
      display: block;
      width: vw(64);
      height: vw(64);
    }

    .icon-check {
      position: absolute;
      left: vw(25);
      bottom: vw(-10);
      font-size: vw(14);
      color: #00be63;
    }
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
