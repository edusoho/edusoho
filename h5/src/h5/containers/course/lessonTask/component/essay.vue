<template>
  <div>
    <div v-if="itemdata.parentTitle" class="subject-material">
      <div :class="['material-stem-nowrap', isShowUpIcon ? 'material-stem' : '', {'exist-material': getAttachementMaterialType('material').length > 0 && isShowUpIcon }]">
        <span v-if="itemdata.parentTitle" :class="['material-tags']">
          {{ subject }}
        </span>
        <span class="material-text material-icon" v-html="stem" @click="handleClickImage($event.target.src)">
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
    <div class="essay">
      <span v-if="!itemdata.parentTitle" class="tags">
        {{ subject }}
      </span>
      <div v-if="!itemdata.parentTitle" class="subject-stem">
        <div class="serial-number">{{ itemdata.seq }}、</div>
        <div class="rich-text" v-html="stem" @click="handleClickImage($event.target.src)" />
        <attachement-preview 
          v-for="item in getAttachementByType('material')"
          :canLoadPlayer="isCurrent"
          :attachment="item"
          :key="item.id" />
      </div>
  
      <div v-if="itemdata.parentTitle" :class="['material-title',{'material-title-weight': itemdata.parentTitle}]">
        <span class="serial-number"><span class="material-type">[{{ $t('courseLearning.essay') }}] </span> {{ itemdata.materialIndex }}、</span>
        <div class="rich-text" v-html="itemdata.stem" @click="handleClickImage($event.target.src)" />
      </div>
  
      <attachement-preview 
        v-for="item in getAttachementByType('stem')"
        :canLoadPlayer="isCurrent"
        :attachment="item"
        :key="item.id" />
  
      <div v-if="disabledData" class="answer-paper">
        <van-field
          v-model="answer[0]"
          :placeholder="placeholder"
          :autosize="{ maxHeight: 132, minHeight: 132 }"
          :disabled="!disabledData"
          class="essay-input"
          label-width="0px"
          type="textarea"
          @input="change()"
        />
        <div v-if="canDo" class="discussion-create__upload">
          <van-uploader
            v-model="fileList"
            :after-read="afterRead"
            :max-count="5"
            @delete="deleteImgItem"
          />
        </div>
        
      </div>
      <div v-if="!disabledData" class="answer-paper">
        <div v-if="!disabledData" class="answer-paper">
          <div class="your-answer">{{ $t('courseLearning.yourAreAnswer') }}：</div>
          <div>
            <!-- <img v-if="(question.length > 0 && question[0].status === 'right' && question[0].status !== 'reviewing') || (itemdata.testResult.status === 'right' && itemdata.testResult.status !== 'none')" :src="rigth" alt="" class="fill-status"> -->
            <!-- <img 
              v-if="(question.length > 0 && question[0].status === 'wrong' && question[0].status !== 'reviewing') || (itemdata.testResult.status === 'wrong' && itemdata.testResult.status !== 'noAnswer' && itemdata.testResult.status !== 'none')" 
              :src="wrong" 
              alt="" 
              class="fill-status"> -->
            <!-- <span class="is-right-answer" v-if="question.length > 0 && question[0].status === 'right' && question[0].status !== 'reviewing'">{{ answer[0] }}</span>
            <span class="is-wrong-answer" v-else-if="itemdata.testResult.status !== 'none'">{{ answer[0] }}</span> -->
            <!-- <span :class="[question.length > 0 && question[0].status === 'right' && question[0].status !== 'reviewing' && itemdata.testResult.status !== 'none' ? 'is-right-answer' : 'is-wrong-answer']"> {{ answer[0] }}</span> -->
            <img 
              v-if="(answer[0] === '' || itemdata.testResult.answer && itemdata.testResult.answer.length === 0)" 
              :src="wrong" 
              alt="" 
              class="fill-status">
            <span v-if="answer[0] === '' || itemdata.testResult.answer && itemdata.testResult.answer.length === 0" class="your-answer is-wrong-answer"> {{ $t('courseLearning.unanswered') }}</span>
            <span class="text-14" style="color: #37393D;" v-html="answer[0]" ></span>
          </div>
          <div class="your-answer mt-16">
            正确答案：
          </div>
          <div class="mb-16">
            <span class="is-right-answer" v-html="itemdata.answer[0]" @click="handleClickImage($event.target.src)" /> 
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
    <div v-if="canDo && exerciseMode === '1' && disabledData" class="submit-footer" :style="{width:width+ 'px'}">
      <van-button
        class="submit-footer-btn"
        :style="{width:width - 20 + 'px'}"
        type="primary"
        @click="submitTopic"
        >{{ $t('courseLearning.submitATopic') }}</van-button
      >
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import attachementPreview from './attachement-preview.vue';
import { ImagePreview, Dialog, Toast } from 'vant'

const WINDOWWIDTH = document.documentElement.clientWidth

export default {
  name: 'EssayType',
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
  },
  data() {
    return {
      fileList: [],
      imgs: [],
      currentItem: null,
      isShowDownIcon: null,
      isShowUpIcon: false,
      question: [],
      refreshKey: true,
      currentAnswer: [],
      width: WINDOWWIDTH,
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
          return '你的回答...';
        } else {
          return '未作答';
        }
      },
    },
  },
  mounted() {
    this.isShowDownIcon = document.getElementsByClassName('material-icon')[this.number]?.childNodes[0].offsetWidth > 234
  },
  methods: {
    change() {
      // console.log(this.answer[0])
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
        this.refreshKey = !this.refreshKey
      })
    },

    handleClickImage (imagesUrl) {
      if (imagesUrl === undefined) return;
      const images = [imagesUrl]
      ImagePreview({
        images
      })
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
    changeUpIcon() {
      this.isShowUpIcon = false
      this.isShowDownIcon = true
    },
    changeDownIcon() {
      this.isShowUpIcon = true
      this.isShowDownIcon = false
    },
    submitTopic() {
      if ( this.answer[0] === '' && this.exerciseMode === '1') {
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
        this.$emit('submitSingleAnswer', this.answer, this.itemdata);
      }


      // let img = ''
      // this.currentAnswer[0] = ''
      // this.imgs.forEach(item => {
      //   img += `<img src="${item}" alt="">`
      // });
       
      // this.currentAnswer[0] = this.answer[0] + img

    }
  },
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
    /deep/.van-uploader__preview-delete {
      border-radius: 50%;
    }

    /deep/.van-uploader__preview-delete-icon {
      position: absolute;
      top: vw(-1);
      right: vw(-1);
      color: #fff;
      font-size: vw(16);
      -webkit-transform: scale(0.5);
      transform: scale(0.5);
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
  .fill-status {
    width: 18px;
    height: 18px;
  }
</style>
