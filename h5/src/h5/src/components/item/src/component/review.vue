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
      <div class="ibs-review-title">答题自判</div>
      <div class="ibs-review-item ibs-mt10">
        <van-radio-group v-model="status" @change="changeStatus">
          <van-radio
            name="right"
            :class="[status === 'right' ? 'ibs-review-check' : '']"
          >
            已掌握
            <template #icon="props">
              <i
                :class="[
                  'wap-icon',
                  props.checked ? 'wap-icon-zhangwo1' : 'wap-icon-zhangwo'
                ]"
              />
            </template>
          </van-radio>
          <van-radio
            name="wrong"
            :class="[status === 'wrong' ? 'ibs-review-check' : '']"
          >
            不太懂
            <template #icon="props">
              <i
                :class="[
                  'wap-icon',
                  props.checked ? 'wap-icon-budong1' : 'wap-icon-budong'
                ]"
              />
            </template>
          </van-radio>
        </van-radio-group>
      </div>
    </template>
  </div>
</template>

<script>
// import Emitter from "../../../../mixins/emitter";
import Emitter from "@/src/mixins/emitter.js";

import { Toast } from "vant";
export default {
  mixins: [Emitter],
  components: {},
  data() {
    return {
      score: "",
      status: "",
      activeIcon: "https://img.yzcdn.cn/vant/user-active.png",
      inactiveIcon: "https://img.yzcdn.cn/vant/user-inactive.png"
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
    questionScore: {
      type: Number,
      default: 0
    }
  },
  computed: {},
  watch: {},
  created() {},
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
      this.dispatch("item-review", "changeStatus", data);
    }
  }
};
</script>
