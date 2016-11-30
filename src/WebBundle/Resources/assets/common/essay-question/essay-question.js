class EssayQuestion
{
	constructor() {
		
	}

	getAnswer(questionId) {
		let answers = [];
		let value = $('[name='+questionId+']').val();
		answers.push(value);

		return answers;
	}
}

export default EssayQuestion;