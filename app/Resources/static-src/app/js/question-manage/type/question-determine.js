import QuestionFormBase from './form-base';

class Datermine extends QuestionFormBase {
	constructor($form) {
		super($form);

		this.initTitleEditor(this.validator);
		this.initAnalysisEditor();

		this.$answerField = $('[name="answer\[\]"]');

		this.init();
	}

	init() {
		this.$answerField.rules('add', {
			required: true,
			messages: {
				required: Translator.trans('course.question.create.choose_success_answer')
			}
		})

	}
}

export default Datermine;