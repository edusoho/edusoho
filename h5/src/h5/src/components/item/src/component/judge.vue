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
      <div class="ibs-subject">
        <van-radio-group
          v-model="answer"
          class="ibs-answer-paper"
          @change="changeAnswer"
          :disabled="!disabledData"
          :refreshKey="refreshKey"
        >
          <van-radio
            v-for="(item, index) in itemData.question.response_points"
            :key="index"
            :name="item.radio.val"
            :class="['ibs-subject-option', 
            'ibs-subject-option--determine', 
            { active: brushDo.status === 'doing' && item.radio.val === answer[0] },
            {'van-checked__right': RadioRight(item.radio.val)},
            {'van-checked__wrong': RadioWrong(item.radio.val)}
            ]"
          >
            <i class="iconfont icon-a-Frame34723"></i>
            <i class="iconfont icon-zhengque1"></i>
            <i class="iconfont icon-cuowu2"></i>
            <div class="ibs-subject-option__content">{{ item.radio.text }}</div>
          </van-radio>
        </van-radio-group>
      </div>
      <div v-if="!disabledData" class="ibs-one-questions-analysis">
        <div class="flex justify-between ibs-analysis-answer">
          <div class="flex items-center">
            <span class="ibs-answer">{{ $t('courseLearning.referenceAnswer') }}：</span>
            <span class="ibs-options" style="color:#00B42A;">{{ commonData.answer[0] === 'T' ? $t('courseLearning.right') : $t('courseLearning.wrong') }}</span>
          </div>
          <div v-if="answer.length > 0" class="flex items-center">
            <span class="ibs-answer">{{ $t('courseLearning.selectedAnswer') }}：</span>
            <span class="ibs-options">{{ answer[0] === 'T' ? $t('courseLearning.right') : $t('courseLearning.wrong') }}</span>
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

const WINDOWWIDTH = document.documentElement.clientWidth

export default {
  name: "judge",
  mixins: [questionMixins, reportAnswer, itemBankMixins],
  data() {
    return {
      width: WINDOWWIDTH,
      answer: this.itemData.userAnwer,
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
    changeAnswer(e) {
      if (this.itemData.mode !== "do") {
        return;
      }
      const data = {
        item_id: this.commonData.item_id,
        question_id: this.commonData.questionId,
        type: 'judge',
        seq: Number(this.commonData.current)
      }
      this.$emit("changeAnswer", e, this.itemData.keys, data);
    },
    getIcons(types) {
      if (types === "F") {
        return "wap-icon-no";
      }
      if (types === "T") {
        return "wap-icon-yes";
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
    RadioRight(radioItem) {
      if (!this.wrong) {
        if(this.disabledData) return false;
      }
      
      // 未填写答案显示正确答案 || 选中错误，正确答案显示
      if(this.answer.length === 0 && this.commonData.answer[0] === radioItem || radioItem === this.commonData.answer[0]) {
        return true;
      }

      // 选中项与正确答案一致
      if (radioItem === this.answer[0] && this.answer[0] === this.commonData.answer[0]) {
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