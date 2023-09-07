<template>
  <div>
    <div v-if="itemdata.parentTitle" class="subject-material">
      <div :class="['material-stem-nowrap', isShowUpIcon ? 'material-stem' : '', {'exist-material': getAttachementMaterialType('material').length > 0 && isShowUpIcon }]">
        <span v-if="itemdata.parentTitle" :class="['material-tags']">
          {{ subject }}
        </span>
        <span class="material-text material-icon" v-html="stem" @click="handleClickImage($event.target.src)" >
        </span>
        <attachement-preview 
          v-for="item in getAttachementMaterialType('material')"
          :canLoadPlayer="isCurrent"
          :attachment="item"
          :key="item.id" />
      </div>
      <i @click="changeUpIcon" :class="['iconfont', 'icon-arrow-up', {'show-up-icon': isShowUpIcon }]"></i>
      <i @click="changeDownIcon" :class="['iconfont', 'icon-arrow-down', {'show-down-icon': isShowDownIcon}]"></i>
    </div>
    <div class="subject">
      <span v-if="!itemdata.parentTitle" class="tags">
        {{ subject }}
      </span>
      <div v-if="!itemdata.parentTitle" class="subject-stem">
        <span class="serial-number">{{ itemdata.seq }}、</span>
        <div class="subject-stem__content rich-text" v-html="stem" @click="handleClickImage($event.target.src)" />
        <attachement-preview 
          v-for="item in getAttachementByType('material')"
          :canLoadPlayer="isCurrent"
          :attachment="item"
          :key="item.id" />
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
      <van-radio-group v-model="radio" class="answer-paper" @change="choose">
        <van-radio
          :name="1"
          :disabled="!disabledData"
          class="subject-option subject-option--determine"
          :class="[
            !canDo ? checkAnswer(1, itemdata) : '' , 
            { active: 1 === currentItem } , 
            { 'van-checked__right' : itemdata.answer === radio},
            {isRight: question.length > 0 &&  question[0].answer[0] === 'T'},
            {isWrong: question.length > 0 &&  'F' !== question[0].response && question[0].response !== question[0].answer[0]}
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
            { 'van-checked__right' : itemdata.answer[0] === radio},
            {isRight: question.length > 0 &&  question[0].answer[0] === 'F'},
            {isWrong: question.length > 0 &&  'T' !== myAnswer &&  myAnswer !== question[0].answer[0]}
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
          <div class="flex items-center" v-if="itemdata.testResult.answer.length > 0">
            <span class="answer">{{ $t('courseLearning.selectedAnswer') }}：</span>
            <span class="options" v-if="question.length > 0">
              {{ question[0].response[0] === 'T' ? '对' : '错' }}
            </span>
            <span v-if="!canDo">
              <span class="options">{{ itemdata.testResult.answer[0] === 1 ? '对' : '错'  }}</span>
            </span>
          </div>
        </div>
        <div class="analysis-color">
          {{ $t('courseLearning.analyze') }}：
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
      {{ $t('courseLearning.analyze') }}：
      <span v-if="parentTitleAnalysis !== ''" v-html="parentTitleAnalysis" @click="handleClickImage($event.target.src)" />
      <span v-else>{{ $t('courseLearning.noParsing') }}</span>
      <attachement-preview 
        v-for="item in getAttachementMaterialType('analysis')"
        :canLoadPlayer="isCurrent"
        :attachment="item"
        :key="item.id" />
    </div>
  </div>
</template>

<script>
import checkAnswer from '../../../../mixins/lessonTask/itemBank';
import attachementPreview from './attachement-preview.vue';
import { ImagePreview } from 'vant'

export default {
  name: 'DetermineType',
  mixins: [checkAnswer],
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
  },
  data() {
    return {
      radio: this.answer[0],
      currentItem: null,
      isShowDownIcon: null,
      isShowUpIcon: false,
      myAnswer: 'T',
      question: []
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
    filterOrders: function(answer = [], mode = 'do') {
      // standard表示标砖答案过滤
      if (this.subject == 'fill') {
        if (mode == 'standard') {
          return answer.length > 0 ? answer.toString() : '无';
        } else {
          return answer.length > 0 ? answer.toString() : this.$t('courseLearning.unanswered');
        }
      } else {
        let arr = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        if (this.subject == 'determine') {
          arr = ['错', '对'];
        }
        let formateAnswer = null;
        formateAnswer = answer.map(element => {
          return arr[element];
        });
        if (mode == 'standard') {
          return formateAnswer.length > 0 ? formateAnswer.join(' ') : '无';
        }
        return formateAnswer.length > 0 ? formateAnswer.join(' ') : this.$t('courseLearning.unanswered');
      }
    },
    isShowFooterShardow() {
      // 模式不为练习 并且不是最后一题,并且为答题模式
      const lastQuestion = this.showShadow !== this.itemdata.id
      if (this.mode === '' && lastQuestion && this.canDo) {
        return true;
      } else if (this.mode === '' && lastQuestion && !this.canDo && this.parentType !== 'material' ) {
        // 模式不为练习，不是最后一题，是解析模式，并且题型不为材料题
        return true;
      }
      
      // 只有练习才有 isExercise --- 是不是练习解析页
      if (this.isExercise) {
        // 不是最后一题，练习模式为测验。并且不是材料题
        if (this.mode === 'exercise' && lastQuestion && this.parentType !== 'material') {
          return true;
        } else if (this.mode === 'exercise' && lastQuestion && this.parentType === 'material') {
          // 是练习解析页，不是最后一题，是材料题返回false
          return false;
        }
      } 

      // 是练习模式 并且为答题模式
      if (this.mode === 'exercise' && this.canDo) {
        // 为一题一答模式，不是最后一题，一题一答做题（true为可以选择，false为不可选，表示已提交）有没有提交
        if (this.exerciseMode === '1' && lastQuestion && this.disabledData) {
          return true
        } 
        // 一题一答，不是材料题，不是最后一题
        if (this.exerciseMode === '1' && lastQuestion && this.parentType !== 'material') {
          return true
        }

        if ( this.exerciseMode === '0' && lastQuestion && this.canDo ) {
          return true
        }
      }
    },
    
    handleClickImage (imagesUrl) {
      if (imagesUrl === undefined) return;
      event.stopPropagation();//  阻止冒泡
      const images = [imagesUrl]
      ImagePreview({
        images
      })
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
        this.question = obj.filter(item => item.questionId+'' === this.itemdata.id)
        this.refreshKey = !this.refreshKey
      })
    },
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
      this.isShowUpIcon = false
      this.isShowDownIcon = true
    },
    changeDownIcon() {
      this.isShowUpIcon = true
      this.isShowDownIcon = false
    }
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
  .exercise-do .active {
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
    p {
      display: inline !important;
      font-size: vw(14);
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