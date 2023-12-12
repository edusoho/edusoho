<template>
  <van-radio-group v-model="questions.answer[0]">
    <van-radio
      v-for="(answer, index) in questions.response_points"
      :key="index"
      :name="answer.radio.val"
      :class="['question-option',
				{'van-checkbox__right': RadioRight(answer.radio.val)},
      	{'van-checkbox__wrong': RadioWrong(answer.radio.val)},
			]"
    >
			<i class="iconfont icon-zhengque1"></i>
			<i class="iconfont icon-cuowu2"></i>
      <div class="question-option__content">
        {{ answer.radio.val === 'T' ? $t('wrongQuestion.right2') : $t('wrongQuestion.wrong2') }}
      </div>
      <template slot="icon">
        <span
          :class="['question-option__order']"
        >
        </span>
      </template>
    </van-radio>
  </van-radio-group>
</template>

<script>
import _ from 'lodash';

export default {
  props: {
    question: {
      type: Object,
      required: true,
    },
  },

  computed: {
    questions() {
      return this.question.questions[0];
    },
  },

  methods: {
    RadioWrong(radioItem) {
      const answer = this.question.questions[0].answer
			const response = this.question.questions[0].report.response

      // 选择项等于当前项，并且选择项不等于正确答案
      if(response[0] === radioItem && response[0] !== answer[0]){
        return true;
      }
    },
    RadioRight(radioItem) {
      const answer = this.question.questions[0].answer
			const response = this.question.questions[0].report.response
      
      // 未填写答案显示正确答案 || 选中错误，正确答案显示
      if(response.length === 0 && answer[0] === radioItem || radioItem === answer[0]) {
        return true;
      }

      // 选中项与正确答案一致
      if (radioItem === response[0] && response[0] === answer[0]) {
        return true;
      }
    },
  },
};
</script>
