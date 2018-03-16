class DetermineQuestion
{
  constructor() {
		
  }

  getAnswer(questionId) {
    let answers = [];
		
    $('input[name='+questionId+']:checked').each(function(){
      answers.push($(this).val());
    });

    return answers;
  }
}

export default DetermineQuestion;