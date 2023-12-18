<template>
  <div>
    <div style="height: 100% !important; position: relative;">
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
        <div class="ibs-subject">
          <van-checkbox-group
            v-model="answer"
            class="ibs-answer-paper"
            @change="changeAnswer"
          >
            <van-checkbox
              v-for="(item, index) in itemData.question.response_points"
              :key="index"
              :name="item.checkbox.val"
              :disabled="!disabledData"
              :class="['ibs-subject-option',
              {active: brushDo.status === 'doing' && itemData.userAnwer.includes(item.checkbox.val)},
              {'van-checkbox__right': RadioRight(item.checkbox.val, index)},
              {'van-checkbox__wrong': RadioWrong(item.checkbox.val)},
              ]"
              :refreshKey="refreshKey"
            >
              <i class="iconfont icon-a-Frame34723"></i>
              <i class="iconfont icon-zhengque1"></i>
              <i class="iconfont icon-cuowu2"></i>
              <div class="ibs-subject-option__content" v-html="item.checkbox.text" />
              <span
                slot="icon"
                :class="[
                  'ibs-subject-option__order',
                  'ibs-subject-option__order--square',
                ]"
                >{{ item.checkbox.val + '.' }}</span
              >
            </van-checkbox>
          </van-checkbox-group>
        </div>
        <div v-if="!disabledData" class="ibs-one-questions-analysis">
          <div class="flex justify-between ibs-analysis-answer">
            <div class="flex items-center">
              <span class="ibs-answer">{{ $t('courseLearning.referenceAnswer') }}：</span>
              <span class="ibs-options" style="color:#00B42A;">{{ filterOrders() }}</span>
            </div>
            <div v-if="answer.length > 0" class="flex items-center">
              <span class="ibs-answer">{{ $t('courseLearning.selectedAnswer') }}：</span>
              <span class="ibs-options">{{ filterAnswerOrders(answer) }}</span>
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
            <span v-if="commonData.analysis" v-html="commonData.analysis" @click="handleClickImage($event.target.src)"/>
            <span v-else>{{ $t('courseLearning.noParsing') }}</span>
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
        <span v-if="currentItem.analysis !== ''" v-html="currentItem.analysis" @click="handleClickImage($event.target.src)"/>
        <span v-else>{{ $t('courseLearning.noParsing') }}</span>
        <attachement-preview 
          v-for="item in getAttachementByType('analysis')"
          :attachment="item"
          :key="item.id" />
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
import reportAnswer from "@/src/mixins/reportAnswer.js";
import answerMode from "@/src/utils/filterAnswerMode";
import itemBankMixins from "@/src/mixins/itemBankMixins.js"
import attachementPreview from "./attachement-preview.vue";
import { Dialog } from 'vant'

const WINDOWWIDTH = document.documentElement.clientWidth

export default {
  mixins: [reportAnswer, questionMixins, itemBankMixins],
  data() {
    return {
      answer: this.itemData.userAnwer.sort(),
      width: WINDOWWIDTH,
      isShowDownIcon: null,
      isShowUpIcon: false,
      question: [],
      refreshKey: true,
    };
  },
  components: {
    attachementPreview
  },
  inject: ['brushDo'],
  computed: {
    subject() {
      return `${answerMode(this.commonData.questionsType)}`;
    }
  },
  mounted() {
    this.isShowDownIcon = document.getElementById(`current${this.currentItem.id}`)?.childNodes[0].offsetWidth > 234
  },
  methods: {
    filterAnswerOrders(answer = []) {
      const arr = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
      const formateAnswer = answer.map((element,index) => {
        if (arr.indexOf(answer[index]) != -1) {
          return arr[arr.indexOf(answer[index])]
        }
      });
      return formateAnswer.length > 0 ? formateAnswer.sort().join('') : '';
    },
    changeAnswer(e) {
      if (this.itemData.mode !== "do") {
        return;
      }
      this.$emit("changeAnswer", e, this.itemData.keys);
    },
    RadioRight(radioItem, index) {
      if (!this.wrong) {
        if(this.disabledData) return false;
      }

      // 没有选择默认显示答案
      if(this.answer.length === 0 && this.commonData.answer.includes(radioItem)) {
        return true;
      }
      
      // 答案部分正确
      if (this.commonData.answer.includes(radioItem) && !this.answer.includes(radioItem)) {
        return true;
      }

      return this.commonData.answer.includes(radioItem)
    },
    RadioWrong(radioItem, index) {
      if (!this.wrong) {
        if(this.disabledData) return false;
      }

      if (this.commonData.answer.includes(radioItem)) {
        return false
      }
      
      // 选择的部分答案正确 
      if((this.commonData.answer.includes(radioItem) && !this.answer.includes(radioItem)) || (!this.commonData.answer.includes(radioItem) && this.answer.includes(radioItem))) {
        return true;
      }

    },
    filterOrders: function() {
      return this.commonData.answer.length > 0 ? this.commonData.answer.join('') : '';
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

      if (this.answer.length === 0) {
        Dialog.confirm({
          message: this.$t('courseLearning.questionNotAnswer'),
          confirmButtonText: this.$t('courseLearning.continueAnswer'),
          cancelButtonText: this.$t('btn.confirm')
        })
        .then(() => {
          // on confirm
        })
        .catch(() => {
          this.$emit('submitSingleAnswer', this.answer, data);
          this.$emit('changeTouch')
        });
      } 
      else {
        this.$emit('changeTouch')
        this.$emit('submitSingleAnswer', this.answer, data);
      }
    },
    goBrushResult() {
      if(this.brushDo.type === "wrongQuestionBook") {
        this.brushDo.goResult()
      } else {
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
</style>