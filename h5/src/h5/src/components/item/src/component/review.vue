<template>
  <div class="ibs-review">
    <template v-if="needScore">
      <div class="ibs-review-title">
        题目得分<span>(最低0分，最高满分)</span>
      </div>
      <div>
        <van-field
          v-model="score"
          type="number"
          placeholder="请输入自评得分"
          label=""
          @input="changeScore"
        />
      </div>
    </template>

    <template v-else>
      <div class="ibs-self-judging">
        <div class="ibs-review-title">{{ $t('courseLearning.selfJudging') }}</div>
        <div class="ibs-self-judging-change-radio">
          <van-radio-group v-model="status" @change="changeStatus" class="ibs-self-judging-group-radio">
            <van-radio name="right" class="ibs-self-judging-radio">
              {{ $t('courseLearning.haveMastered') }}
              <template #icon="props">
                <img
                  class="img-icon"
                  :src="props.checked ? activeIcon : defaultIcon"
                />
                <i v-show="props.checked" class="iconfont icon-check"></i>
              </template>
            </van-radio>
            <van-radio name="wrong" class="ibs-self-judging-radio">
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
    </template>
  </div>
</template>

<script>
import Emitter from "@/src/mixins/emitter.js";

import { Toast } from "vant";
export default {
  mixins: [Emitter],
  components: {},
  data() {
    return {
      score: "",
      status: "",
      // activeIcon: "https://img.yzcdn.cn/vant/user-active.png",
      // inactiveIcon: "https://img.yzcdn.cn/vant/user-inactive.png",
      activeIcon: 'static/images/itemBankExercise/grasp-active.png',
      defaultIcon: 'static/images/itemBankExercise/grasp.png',
      activeQuestions: 'static/images/itemBankExercise/not-master-active.png',
      defaultQuestions: 'static/images/itemBankExercise/not-master.png',
    };
  },
  props: {
    needScore: {
      type: Boolean,
      default: false
    },
    questionId: {
      type: String,
      default: ""
    },
    reviewStatus: {
      type: Object,
      default() {
        return {};
      }
    },
    questionScore: {
      type: Number,
      default: 0
    }
  },
  computed: {},
  created() {
    this.status = this.reviewStatus.status
  },
  methods: {
    changeScore(e) {
      if (e < 0) {
        Toast.fail("不得低于0分");
        this.score = 0;
      }
      if (e > this.questionScore) {
        Toast.fail(`不得高于${this.questionScore}分`);
        this.score = this.questionScore;
      }
      const data = {
        score: this.score,
        questionId: this.questionId
      };
      this.dispatch("item-review", "changeScore", data);
    },
    changeStatus(e) {
      const data = {
        status: e,
        questionId: this.questionId
      };
      this.$emit("changeReviewList", data);
    }
  }
};
</script>
<style scoped lang="scss">
  .ibs-self-judging-group-radio {
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
</style>