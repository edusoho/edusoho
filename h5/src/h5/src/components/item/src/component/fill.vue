<template>
  <div>
    <div class="ibs-subject-material" v-if="currentItem.type === 'material'" >
      <div :class="['ibs-material-stem-nowrap', isShowDownIcon ? 'ibs-material-stem' : '']">
        <span class="ibs-material-tags">
          {{ $t('courseLearning.material') }}
        </span>
        <span :id="`current${currentItem.id}`" class="ibs-material-text" v-html="currentItem.material" @click="handleClickImage($event.target.src)"></span>
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
      <div v-if="disabledData" class="ibs-subject ibs-fill">
        <div>
          <div v-for="(i, index) in itemData.question.answer" :key="index" :class="{'ibs-field-bottom': index != commonData.answer.length - 1 }">
            <van-field
              v-model="answer[index]"
              :placeholder="brushDo.status === 'doing' ? '('+ (index + 1) + ')' + $t('courseLearning.pleaseEnterYourAnswer') : $t('courseLearning.unanswered')"
              class="ibs-fill-input"
              label-width="0px"
              type="textarea"
              rows="1"
              autosize
              :disabled="!disabledData"
              @input="changeAnswer"
            />
          </div>
        </div>
      </div>
      <div v-if="!disabledData" class="ibs-answer-paper">
          <div class="ibs-your-answer">{{ $t('courseLearning.yourAreAnswer') }}：</div>
          <div>
            <img v-if="status === 'right' || commonData.report.status === 'right'" :src="rigth" alt="" class="ibs-fill-status">
            <img v-if="status === 'wrong' || commonData.report.status === 'wrong' || commonData.report.status === 'no_answer'" :src="wrongImg" alt="" class="ibs-fill-status">
            <span v-if="!disabledData && answer.length === 0" class="ibs-your-answer ibs-is-wrong-answer"> {{ $t('courseLearning.unanswered') }}</span>
            <span v-for="(i, index) in answer" :key="index" :class="[status === 'right' ? 'ibs-is-right-answer' : 'ibs-is-wrong-answer', commonData.report.status === 'right' ? 'ibs-is-right-answer' : 'ibs-is-wrong-answer']">{{ answer.length - 1 === index ? i === '' ? $t('courseLearning.unanswered') : i : ((i === '' ? $t('courseLearning.unanswered') : i)+ ';') }}</span>
          </div>
          <div class="ibs-your-answer mt-16">
            {{ $t('courseLearning.correctAnswer') }}：
          </div>
          <div class="mb-16">
            <span v-for="(i, index) in commonData.answer" :key="index" class="ibs-is-right-answer"> {{  commonData.answer.length - 1 === index ? i : i + ';' }}</span>
          </div>
          <div v-if="$route.query.type == 'assessment'" class="ibs-analysis-color mb-8">
            {{ $t('courseLearning.score') }}：<div>{{ commonData.report ? commonData.report.score : 0.0 }}</div>
          </div>
          <div v-if="$route.query.type == 'assessment'" class="ibs-analysis-color mb-8">
            {{ $t('courseLearning.comment') }}：<div>{{ commonData.report ? commonData.report.comment === '' ? '--' : commonData.report.comment : '' }}</div>
          </div>
          <div class="ibs-analysis-color">
            {{ $t('courseLearning.analyze') }}：
            <span v-if="commonData.analysis" v-html="commonData.analysis" @click="handleClickImage($event.target.src)"/>
            <div v-else>{{ $t('courseLearning.noParsing') }}</div>
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
      {{ $t('courseLearning.analyze') }}：
      <span v-if="currentItem.analysis !== ''" v-html="currentItem.analysis" @click="handleClickImage($event.target.src)"/>
      <span v-else>{{ $t('courseLearning.noParsing') }}</span>
      <attachement-preview 
        v-for="item in getAttachementByType('analysis')"
        :attachment="item"
        :key="item.id" />
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
import { Dialog } from 'vant'

const WINDOWWIDTH = document.documentElement.clientWidth

export default {
  name: "fill",
  mixins: [questionMixins, itemBankMixins],
  data() {
    return {
      width: WINDOWWIDTH,
      answer: this.itemData.userAnwer,
      rigth: 'static/images/exercise/rigth.png',
      wrongImg: 'static/images/exercise/wrong.png',
      isShowDownIcon: null,
      isShowUpIcon: false,
      question: [],
      refreshKey: true,
      status: ''
    };
  },
  components: {
    attachementPreview
  },
  inject: ['brushDo'],
  computed: {
    placeholder: {
      get() {
        if (this.disabled) {
          return "未作答";
        } else {
          return "请填写答案";
        }
      }
    },
    subject() {
      return `${answerMode(this.commonData.questionsType)}`;
    }
  },
  watch: {
    questionStatus: {
      handler(newVal, oldVal) {
        if (newVal.length > 0) {
          this.status = newVal
        }
      }
    }
  },
  mounted() {
    const questionStatus = this.fillStatus.filter(item => item.question_id + '' === this.commonData.questionId)
    if (questionStatus.length > 0) {
      this.status = questionStatus[0].status
    }
  
    const stemDom = document.getElementById(`current${this.currentItem.id}`)
    if (stemDom) {
      this.isShowDownIcon = stemDom.childNodes[0].offsetWidth > 234
    }
  },
  methods: {
    filterFillHtml(text) {
      const reg = /\[\[\]\]/g;
      if (!text.match(reg)) {
        return text;
      }
      let index = 1;
      return text.replace(reg, function() {
        return `<span> (${index++}) ____</span>`;
      });
    },
    changeAnswer() {
      this.$emit("changeAnswer", this.answer, this.itemData.keys);
    },
    getAttachmentTypeData(type) {
      return this.attachements.filter(item => item.module === type);
    },
    getAttachementByType(type) {
      return this.currentItem.attachments.filter(item => item.module === type);
    },
    submitTopic() {
      const thereNoAnswer = this.answer.some(item => {
        return item === '' 
      })
      const data = {
        item_id: this.commonData.item_id,
        question_id: this.commonData.questionId,
        seq: Number(this.commonData.current)
      }
      
      if (!thereNoAnswer) {
        this.$emit('changeTouch')
        this.$emit('submitSingleAnswer', this.answer, data, 'fill');
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
        this.$emit('submitSingleAnswer', this.answer, data, 'fill');
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
  }
};
</script>
<style scoped lang="scss">
  /deep/.van-field__control::placeholder {
    color: #D2D3D4 !important;
    font-size: vw(14);
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
</style>