<template>
  <div>
    <e-loading v-if="isLoading" />
    <div v-if="exerciseIntro" class="intro-body">
      <van-cell-group class="intro-panel">
        <van-cell
          class="intro-cell test-name"
          :border="false"
          :title="$t('courseLearning.chapterName')"
          :value="exerciseIntro.chapterName"
        />
      </van-cell-group>
      <van-panel
        class="panel intro-panel"
        :title="$t('courseLearning.numberOfTopics')"
      >
        <template #header>
          <div class="van-cell van-panel__header">
            <span class="font-medium text-16"
              style="color:rgba(0,0,0,0.85)"
              >{{ $t('courseLearning.numberOfTopics') }}</span
            >
            <span class="ml-12 font-normal text-14 leading-6"
              style="color:rgba(0,0,0,0.35)"
              >{{
                exerciseIntro.itemCounts.total +
                  ' ' +
                  $t('courseLearning.topic')
              }}</span
            >
          </div>
        </template>
        <van-cell
          v-for="item in question_type_seq"
          :border="false"
          :key="item"
          :title="$t(obj[item])"
          :value="`${counts[item]} ${$t('courseLearning.topic')}`"
          class="intro-cell"
        />
      </van-panel>
    </div>
    <div v-if="exerciseIntro" class="intro-footer">
      <van-button v-if="!answer_record_id"
        class="intro-footer__btn"
        type="primary"
        @click="chooseQuestionMode = true"
        >{{ $t('courseLearning.chooseQuestionAnsweringMode') }}</van-button
      >
      <van-button v-else
        class="intro-footer__btn"
        type="primary"
        @click="continueDo()"
        >{{ $t('questionBank.continue') }}</van-button
      >
    </div>
    <van-popup
      v-model="chooseQuestionMode"
      position="bottom"
      closeable
      round
      safe-area-inset-bottom
      :style="{ height: '44%' }"
      class="choose-mode-popup"
    >
      <div class="choose-mode-title">
        {{ $t('courseLearning.answerMode') }}
      </div>
      <div class="choose-mode-change-radio">
        <van-radio-group v-model="radio" class="choose-mode-group-radio">
          <van-radio name="0" class="choose-mode-radio">
            {{ $t('courseLearning.testMode') }}
            <template #icon="props">
              <img
                class="img-icon"
                :src="props.checked ? activeIcon : defaultIcon"
              />
              <i v-show="props.checked" class="iconfont icon-check"></i>
            </template>
          </van-radio>
          <van-radio name="1" class="choose-mode-radio">
            {{ $t('courseLearning.answerOneQuestionAtTime') }}
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
      <van-button
        class="choose-mode__btn"
        type="primary"
        @click="startExercise()"
        >{{ $t('courseLearning.startAnsweringQuestions') }}</van-button
      >
    </van-popup>
  </div>
</template>

<script>
import Api from '@/api';
import { Toast } from 'vant';
import { mapState } from 'vuex';

export default {
  data() {
    return {
      exerciseId: null,
      moduleId: null,
      categoryId: null,
      answer_record_id: this.$route.query.answer_record_id,
      chooseQuestionMode: false,
      question_type_seq: [], // 试卷已有题型
      counts: {}, // 考试题型数量对象
      exerciseIntro: null,
      activeIcon: 'static/images/exercise/active-icon.png',
      defaultIcon: 'static/images/exercise/default-icon.png',
      activeQuestions: 'static/images/exercise/active-on-questions.png',
      defaultQuestions: 'static/images/exercise/default-on-questions.png',
      radio: '0',
      obj: {
        single_choice: 'courseLearning.singleChoice',
        choice: 'courseLearning.choice',
        essay: 'courseLearning.essay',
        uncertain_choice: 'courseLearning.uncertainChoice',
        determine: 'courseLearning.determine',
        fill: 'courseLearning.fill',
        material: 'courseLearning.material',
      },
    };
  },

  created() {
    this.getExerciseIntro();
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading,
    }),
  },
  beforeRouteEnter(to, from, next) {
    document.getElementById('app').style.background = '#f6f6f6';
    next();
  },
  beforeRouteLeave(to, from, next) {
    document.getElementById('app').style.background = '';
    next();
  },
  methods: {
    // 获取信息
    getExerciseIntro() {
      this.exerciseId = this.$route.query.exerciseId;
      this.moduleId = this.$route.query.moduleId;
      this.categoryId = this.$route.query.categoryId;
      Api.getExerciseInfro({
        query: {
          exerciseId: this.exerciseId,
        },
        params: {
          moduleId: this.moduleId,
          categoryId: this.categoryId,
        },
      })
        .then(res => {
          this.exerciseIntro = res;
          this.counts = res.itemCounts;

          for (const i in this.counts) {
            if (this.counts[i] > 0 && i !== 'total') {
              this.question_type_seq.push(i);
            }
          }
        })
        .catch(err => {
          Toast.fail(err.message);
        });
    },
    // 开始答题
    startExercise() {
      const query = {
        mode: 'start',
        type: 'chapter',
        exerciseMode: this.radio,
        title: this.exerciseIntro.chapterName,
        exerciseId: this.exerciseId,
        categoryId: this.categoryId,
        moduleId: this.moduleId,
      };
      this.$router.push({ path: '/brushDo', query });
    },
    // 继续答题
    continueDo() {
      const query = {
        mode: 'continue',
        type: 'chapter',
        title: this.exerciseIntro.chapterName,
        exerciseId: this.exerciseId,
        categoryId: this.categoryId,
        moduleId: this.moduleId,
        answer_record_id: this.answer_record_id,
      };
      this.$router.push({ path: '/brushDo', query });
    }
  },
};
</script>

<style lang="scss" scoped>
.test-name {
  .van-cell__title {
    max-width: 70px;
    margin-right: 12px;
  }

  .van-cell__value {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
}

.choose-mode-group-radio {
  ::v-deep .van-radio__icon {
    position: relative;
    margin-bottom: vw(8);
    height: vw(64);
  }

  ::v-deep .van-radio__label {
    margin-left: 0;
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
</style>
