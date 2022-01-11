class CommentResult {
  constructor() {
    this.$commentBox = $('.js-comment-box');
    this.$modifyCommentBox = $('.js-modify-comment-box');
    this.answerRecordId = $('.js-answer-record-id').val();
    this.initEvent();
  }

  initEvent() {
    $('.js-modify-btn').click(() => {
      this.handleClickModify();
    });
    $('.js-close-btn').click(() => {
      this.handleClickClose();
    });
    $('.js-save-btn').click(() => {
      this.handleClickSave();
    });
    $('.js-comment-select').change(() => {
      this.handleChangeSelect();
    });
  }

  handleClickClose() {
    this.showElement(this.$commentBox);
    this.hiddenElement(this.$modifyCommentBox);
    $('.js-comment-textarea').val($('.js-comment-content').text());
    $('.js-comment-select option:first').prop('selected', 'selected');
  }

  handleClickSave() {
    this.showElement(this.$commentBox);
    this.hiddenElement(this.$modifyCommentBox);
    const textareaVal = $('.js-comment-textarea').val();
    $('.js-comment-content').text(textareaVal);

    $.ajax({
      type: 'POST',
      beforeSend: function(request) {
        request.setRequestHeader('Accept', 'application/vnd.edusoho.v2+json');
        request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
      },
      data: { comment: textareaVal },
      url: '/api/answerRecord/' + this.answerRecordId + '/comment',
      success: function(res) {
        console.log(res);
      }
    });
  }

  handleClickModify() {
    this.hiddenElement(this.$commentBox);
    this.showElement(this.$modifyCommentBox);
  }

  handleChangeSelect() {
    const selectedVal = $('.js-comment-select').find('option:selected').text();
    $('.js-comment-textarea').val(selectedVal);
  }

  hiddenElement($el) {
    if ($el.hasClass('show')) {
      $el.removeClass('show');
    }
    $el.addClass('hidden');
  }

  showElement($el) {
    if ($el.hasClass('hidden')) {
      $el.removeClass('hidden');
    }
    $el.addClass('show');
  }
}

new CommentResult();