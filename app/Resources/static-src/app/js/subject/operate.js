/*
题目对象：
{
    "stem" : "", //required
    "type" : "", //required
    "options" : [], //required: choice、single_choice、uncertain_choice、determine
    "answer" : "", //required:  essay 
    "answers" : [], //required: choice、single_choice、ncertain_choice、determine
    "score" : 2.0,
    "missScore" : 1.0,
    "analysis" : "",
    "attachments" : {},
    "subQuestions" : [],
    "difficulty" : "simple|normal|difficulty",
    "errors" : [
        {
            "element" : "stem",
            "index" : 0,
            "code" : 40487,
            "message" : "题干有特殊字符"
        },
        {
           "element" : "options",
            "index" : 2,
            "code" : 40402,
            "message" : "缺少C选项" 
        }
    ]
}
*/
export default class QuestionOperate {
  constructor() {
    this.questions = {};
    this.tokenList = [];
    this.$statList = $('.js-subject-data');
    this.$itemList = $('.js-item-list');
    this.flag = true;
    this.init();
  }

  init() {
    let cachedData = this._toJson($('.js-cached-data').html());
    for (var i = 0; i < cachedData.length; i++) {
      let token = this._getToken();
      this.questions[token] = cachedData[i];
      this.tokenList.push(token);
      let index = ++i;
      $(`[data-anchor="#${index}"]`).data('anchor', '#' + token);
      $('#' + index).attr('id', token);
    }
    this.flag = false;
  }

  modifyDifficulty(selectQuestion, difficulty, text) {
    let self = this;
    $.each(selectQuestion, function(index, token){
      // if (typeof self.questions[token] != 'undefined') {
      //   this.updateQuestionItem(tokne, 'difficulty', difficulty);
        self.$itemList.find('#' + token).find('.js-difficulty').html(text);
      // }
    });
  }

  modifyScore(selectQuestion, score) {
    let self = this;
    $.each(selectQuestion, function(index, token) {
      self.$itemList.find(`#${token}`).find('.js-score').html(`${score}分`);
    });
  }

  addQuestion(preToken, token, question) {
    if (!this.isUpdating()) {
      return;
    }
    this.flag = true;
    this.questions[token] = question;
    position = this.tokenList.indexOf(preToken);
    this.tokenList.splice(position, 0, token);
    this.flag = false;
  }

  deleteQuestion(deleteToken) {
    if (!this.isUpdating()) {
      return;
    }
    this.flag = true;
    this.questions[deleteToken] = undefined;
    position = this.tokenList.indexOf(preToken);
    this.tokenList.splice(position, 1);
    this.flag = false;
  }

  updateQuestion(token, question) {
    if (!this.isUpdating()) {
      return;
    }
    this.flag = true;
    this.questions[token] = question;
    this.flag = false;
  }

  updateQuestionItem(token, itemKey, itemValue) {
    if (!this.isUpdating()) {
      return;
    }
    this.flag = true;
    this.questions[token][itemKey] = itemValue;
    this.flag = false;
  }

  getQuestion(token) {
    if (!this.isUpdating()) {
      return;
    }
    return this.questions[token];
  }

  isUpdating() {
    if (this.flag == true) {
      cd.message({ type: 'danger', message: Translator.trans('题目正在修改中,请稍侯') });
      return false;
    }

    return true;
  }

  _toJson(str) {
    let json = {};
    if (str) {
      json = $.parseJSON(str.replace(/[\r\n\t]/g, ''));
    }
    return json;
  }

  _random() {
    return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
  }

  _getToken() {
    return (this._random()+this._random()+"-"+this._random()+"-"+this._random()+"-"+this._random()+"-"+this._random()+this._random()+this._random());
  }
}