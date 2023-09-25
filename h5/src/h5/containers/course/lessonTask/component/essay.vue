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
      <i @click="changeDownIcon" :class="['iconfont', 'icon-arrow-down', {'show-down-icon': isShowUpIcon }]"></i>
      <attachement-preview 
        v-for="item in getAttachementMaterialType('material')"
        :canLoadPlayer="isCurrent"
        :attachment="item"
        :key="item.id" />
    </div>
    <div class="essay">
      <span v-if="!itemdata.parentTitle" class="tags">
        {{ subject }}
      </span>
      <div v-if="!itemdata.parentTitle" class="subject-stem">
        <div class="serial-number">{{ itemdata.seq }}、</div>
        <div class="rich-text" v-html="stem" @click="handleClickImage($event.target.src)" />
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
          v-model="answerText"
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
            <div v-if="mode === 'exam' && !canDo">
              <img v-if="(exerciseMode == '' &&  question.length > 0 && question[0].status === 'right') || (itemdata.testResult.status === 'right' && itemdata.testResult.status !== 'none')" :src="rigth" alt="" class="fill-status">
              <img 
                v-if="(question.length > 0 && question[0].status === 'wrong') || (itemdata.testResult.status === 'wrong') || (itemdata.testResult.status === 'noAnswer') || (itemdata.testResult.status === 'partRight')" 
                :src="wrong" 
                alt="" 
                class="fill-status">
              <span class="is-right-answer" v-if="(exerciseMode == '' &&  question.length > 0 && question[0].status === 'right') || (itemdata.testResult.status === 'right' && itemdata.testResult.status !== 'none')" v-html="answer[0]" @click="handleClickImage($event.target.src)"></span>
              <span class="is-wrong-answer" v-else-if="(question.length > 0 && question[0].status === 'wrong') || (itemdata.testResult.status === 'wrong') || (itemdata.testResult.status === 'noAnswer') || (itemdata.testResult.status === 'partRight')" v-html="answer[0]" @click="handleClickImage($event.target.src)"></span>
              <span v-if="itemdata.testResult.status === 'none'" class="your-answer" style="color: #37393D;" v-html="answer[0]" @click="handleClickImage($event.target.src)"></span>
              <span v-if="answer[0] === '' || itemdata.testResult.answer && itemdata.testResult.answer.length === 0" class="your-answer is-wrong-answer"> {{ $t('courseLearning.unanswered') }}</span>
            </div>
            <div v-else>
              <span v-if="answer[0] === '' || itemdata.testResult.answer && itemdata.testResult.answer.length === 0" class="your-answer"> {{ $t('courseLearning.unanswered') }}</span>
              <span class="essay-answer" style="color: #37393D;" v-html="answer[0]" @click="handleClickImage($event.target.src)"></span>
            </div>
          </div>
          <div class="your-answer mt-16">
            {{ $t('courseLearning.correctAnswer') }}：
          </div>
          <div class="mb-16">
            <span class="is-right-answer" v-html="itemdata.answer[0]" @click="handleClickImage($event.target.src)" /> 
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
    <div v-if="  totalCount === reviewedCount" class="submit-footer">
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
import Api from '@/api';
import attachementPreview from './attachement-preview.vue';
import { Dialog, Toast } from 'vant'
import isShowFooterShardow from '@/mixins/lessonTask/footerShardow';
import refreshChoice from '@/mixins/lessonTask/swipeRefResh.js';
import handleClickImage from '@/mixins/lessonTask/handleClickImage.js';

const WINDOWWIDTH = document.documentElement.clientWidth

export default {
  name: 'EssayType',
  mixins: [isShowFooterShardow, refreshChoice, handleClickImage],
  components: {
    attachementPreview
  },
  watch: {
    answerText: {
      handler(data) {
        this.answer[0] = data + this.fileStr
      },
    },
    imgs: {
      handler(data) {
        this.fileStr = ''
        data.forEach(item => {
          this.fileStr += `<img src="${item}"></img>`
        })
        this.answer[0] = this.answerText + this.fileStr
      },
      deep: true,
    },
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
      fileList: [],
      fileStr: '',
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
      answerText: ''
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
          return this.$t('courseLearning.yourAreAnswer') + '...';
        } else {
          return this.$t('wrongQuestion.unanswered');
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
    getAttachementByType(type) {
      return this.itemdata.attachments.filter(item => item.module === type) || []
    },
    getAttachementMaterialType(type) {
      return this.itemdata.parentTitle.attachments.filter(item => item.module === type) || []
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
      this.isShowUpIcon = true
      this.isShowDownIcon = false
    },
    changeDownIcon() {
      this.isShowUpIcon = false
      this.isShowDownIcon = true
    },
    submitTopic() {
      if ( this.answer[0] === '' && this.exerciseMode === '1') {
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
  /deep/.essay-answer {
    font-size: vw(14);
    line-height: vw(22);
    img {
      display: block;
      margin-bottom: vw(8);
      border-radius: vw(8);
      height: vw(175);
    }
  }
</style>
