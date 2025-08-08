<template>
  <div>
    <div class="answer-layout">
      <div class="answer-mode">
        <div class="title">
          {{ $t('courseLearning.answerMode') }}
        </div>
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
      <div class="amswer-num">
        <div class="questions-num">
          <span>{{ $t('courseLearning.displayQuantity') }}</span>
          <van-field
            class="input-num"
            v-model="questionsNum"
            type="digit"
            clearable
            :placeholder="$t('courseLearning.pleaseEnterNumberQuestions')"
            @blur="blurInput"
          />
        </div>
        <div class="questions-num-tips">
          {{ $t('courseLearning.questionsNumTips') }}
        </div>
      </div>
    </div>
    <div class="intro-footer">
      <van-button
        class="intro-footer__btn"
        type="primary"
        @click="startExercise"
        >{{ $t('courseLearning.startAnsweringQuestions') }}</van-button
      >
    </div>
  </div>
</template>

<script>
import { Toast } from 'vant';
import Api from '@/api';

export default {
  data() {
    return {
      activeIcon: 'static/images/exercise/active-icon.png',
      defaultIcon: 'static/images/exercise/default-icon.png',
      activeQuestions: 'static/images/exercise/active-on-questions.png',
      defaultQuestions: 'static/images/exercise/default-on-questions.png',
      radio: '0',
      questionsNum: 20,
      targetId: this.$route.query.id,
      targetType: this.$route.query.targetType,
      exerciseMediaType: this.$route.query.exerciseMediaType,
      searchParams: this.$route.query.searchParams,
      wrongNumCount: null,
    };
  },

  created() {
    this.getWrongNumCount()
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
    // 失焦校验
    blurInput(e) {
      if (Number(e.target.value) > this.wrongNumCount) {
        this.questionsNum = this.wrongNumCount
        Toast.fail('超出输入范围');
      }
    },
    // 获取信息
    getWrongNumCount() {
      Api.getWrongNumCount({
        query: {
          poolId: this.targetId,
        },
        params: {
          targetType: this.targetType,
          exerciseMediaType: this.exerciseMediaType,
        }
      }).then(res =>{
        this.wrongNumCount = res.wrongNumCount
        this.questionsNum = Math.min(this.questionsNum,this.wrongNumCount)
      }).catch(err =>{
        Toast.fail(err.message);
      })
    },
    // 开始答题
    startExercise() {
      if(this.questionsNum === '0') return Toast.fail(this.$t('courseLearning.theNumberAnswers'))
      if(this.questionsNum === '') return Toast.fail(this.$t('courseLearning.theNumberblank'))

      this.$router.replace({
        name: 'WrongExercisesDo',
        query: {
          exerciseMode: this.radio,
          itemNum: this.questionsNum,
          id: this.targetId,
          ...this.searchParams,
        },
      });
    },
  },
};
</script>

<style scoped lang="scss">
.answer-layout {
  padding: vw(16);
  .answer-mode {
    width: 100%;
    height: vw(150);
    border-radius: 8px;
    background: #fff;

    .title {
      padding: vw(12) vw(16) vw(4);
      color: #37393d;
      font-size: vw(16);
      font-weight: 500;
      line-height: vw(24);
    }

    .choose-mode-group-radio {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .choose-mode-radio {
      display: flex;
      flex-direction: column;

      &:nth-child(1) {
        margin-right: vw(64);
      }
    }
  }
  .amswer-num {
    margin-top: vw(8);
    width: 100%;
    height: vw(162);
    border-radius: 8px;
    background: #fff;

    .questions-num {
      display: flex;
      justify-content: space-between;
      padding: vw(12) vw(16);

      .input-num {
        padding: 0;
        width: vw(120);

        ::v-deep .van-field__control {
          text-align: right !important;
        }
      }
    }
    .questions-num-tips {
      margin: 0 vw(16);
      padding: vw(12);
      color: #919399;
      font-size: vw(14);
      font-weight: 400;
      line-height: vw(24);
      border-radius: 6px;
      background: #f7f8fa;
    }
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
