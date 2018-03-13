class FillQuestion
{
  constructor() {
		
  }

  getAnswer(questionId) {
    let answers = [];
    $('input[name='+questionId+']').each(function(){
      answers.push($(this).val());
    });

    return answers;
  }
}

export default FillQuestion;