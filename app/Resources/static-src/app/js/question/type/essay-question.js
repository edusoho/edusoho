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

  getAttachment(questionId) {
    let attachment = [];
    let fileId = $('[name='+questionId+']').parent().find('[data-role="fileId"]').val();

    if (fileId != '') {
      attachment.push(fileId);
    }

    return attachment;
  }
}

export default EssayQuestion;