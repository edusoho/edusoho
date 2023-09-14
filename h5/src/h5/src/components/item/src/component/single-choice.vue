<template>
	<div class="ibs-subject-card">
		<div class="ibs-subject-stem">
			<span class="ibs-tags">
				{{ subject }}
			</span>
			<div>
				<div class="ibs-serial-number">{{ Number(commonData.current) }}、</div>
				<div class="ibs-rich-text" v-html="getStem()" />
			</div>
		</div>
		<div class="ibs-subject">
			<van-radio-group
				v-model="answer"
				class="ibs-answer-paper"
				:disabled="disabled"
				@change="changeAnswer"
			>
				<van-radio
					class="ibs-subject-option"
					v-for="(item, index) in itemData.question.response_points"
					:key="index"
					:name="item.radio.val"
				>
					<div class="ibs-subject-option__content" v-html="item.radio.text" />
					<template #icon="props">
						<span
							:class="[
								'ibs-subject-option__order',
								props,
								reportAnswer(
									itemData.mode,
									item.radio.val,
									itemData.userAnwer,
									itemData.question.answer
								)
							]"
							>{{ item.radio.val + '.' }}</span
						>
					</template>
				</van-radio>
			</van-radio-group>
		</div>
	</div>
</template>
<script>
import questionMixins from "@/src/mixins/questionMixins.js";
import reportAnswer from "@/src/mixins/reportAnswer.js";
import answerMode from "@/src/utils/filterAnswerMode";
import itemBankMixins from "@/src/mixins/itemBankMixins.js"

export default {
  name: "single-choice",
  mixins: [questionMixins, reportAnswer, itemBankMixins],
  data() {
    return {
      answer: this.itemData.userAnwer[0]
    };
  },
	created() {
		console.log(this.itemData);
		console.log(this.commonData, '1111111');
	},
	computed: {
    subject() {
      return `${answerMode(this.commonData.questionsType)}`;
    }
  },
  methods: {
    changeAnswer(e) {
      if (this.itemData.mode !== "do") {
        return;
      }
      this.$emit("changeAnswer", e, this.itemData.keys);
    }
  }
};
</script>
