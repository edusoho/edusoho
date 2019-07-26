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
    this.questionCounts = {};
    this.totalScore = 0;
    this.flag = true;
    this.init();
  }

  init() {
    this.initQuestions();
    this.initQuestionCountsAndTotalScore();
    this.flag = false;
  }

  //data-anchor和ID节点从0开始
  initQuestions() {
    let cachedData = this._toJson($('.js-cached-data').text());
    for (var i = 0; i < cachedData.length; i++) {
      let token = this._getToken();
      this.questions[token] = cachedData[i];
      this.tokenList.push(token);
      let index = i;
      $(`[data-anchor="#${index}"]`).attr('data-anchor', '#' + token);
      $('#' + index).attr('id', token);
    }
  }

  initQuestionCountsAndTotalScore() {
    this.questionCounts = {
      'total': 0,
      'single_choice': 0,
      'choice': 0,
      'uncertain_choice': 0,
      'determine': 0,
      'fill': 0,
      'essay': 0,
      'material': 0,
    };

    let self = this;
    Object.keys(this.questions).forEach(function(token) {
      let question = self.questions[token];
      self.questionCounts['total']++;
      self.questionCounts[question['type']]++;
      if (question['type'] != 'material') {
        self.totalScore += question['score'];
      } else {
        $.each(question['subQuestions'], function(token, subQuestion) {
          self.totalScore += subQuestion['score'];
        });
      }
    });
  }

  getQuestionCount(type) {
    return this.questionCounts[type];
  }

  getTotalScore() {
    return this.totalScore;
  }

  getQuestionOrder(token) {
    return this.tokenList.indexOf(token) + 1;
  }

  modifyDifficulty(selectQuestion, difficulty, text) {
    let self = this;
    $.each(selectQuestion, function(index, token){
      if (typeof self.questions[token] != 'undefined') {
        self.updateQuestionItem(token, 'difficulty', difficulty);
        self.$itemList.find('#' + token).find('.js-difficulty').html(text);
      }
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
    let position = this.tokenList.indexOf(preToken);
    this.tokenList.splice(position, 0, token);
    this.questionCounts['total']++;
    this.questionCounts[question['type']]++;
    this.totalScore += question['score'];
    this.triggerTotalScoreChange();
    this.triggerTypeCountChange(question['type']);
    this.flag = false;
  }

  deleteQuestion(deleteToken) {
    if (!this.isUpdating()) {
      return;
    }
    this.flag = true;
    const question = this.questions[deleteToken];
    delete this.questions[deleteToken];
    let position = this.tokenList.indexOf(deleteToken);
    this.tokenList.splice(position, 1);
    this.questionCounts['total']--;
    this.questionCounts[question['type']]--;
    if (question['type'] != 'material') {
      this.totalScore -= question['score'];
    } else {
      $.each(question['subQuestions'], function(token, subQuestion) {
        this.totalScore -= subQuestion['score'];
      })
    }
    this.triggerTotalScoreChange();
    this.triggerTypeCountChange(question['type']);
    this.flag = false;
  }

  updateQuestion(token, question) {
    if (!this.isUpdating()) {
      return;
    }
    this.flag = true;
    let oldQuestion = this.questions[token];
    this.questions[token] = question;
    this.totalScore = this.totalScore - oldQuestion['score'] + question['score'];
    this.triggerTotalScoreChange();
    this.flag = false;
  }

  updateQuestionItem(token, itemKey, itemValue, isTrigger = true) {
    if (!this.isUpdating()) {
      return;
    }
    this.flag = true;
    let oldValue = this.questions[token][itemKey];
    this.questions[token][itemKey] = itemValue;
    if (itemKey == 'score') {
      this.totalScore = this.totalScore - oldValue + itemValue;
      this.triggerTotalScoreChange(isTrigger);
    }
    if (itemKey == 'type' && oldValue != itemValue) {
      this.questionCounts[oldValue]--;
      this.questionCounts[itemValue]++;
      this.triggerTypeCountChange(oldValue);
      this.triggerTypeCountChange(itemValue);
    }
    this.flag = false;
  }

  getQuestion(token) {
    if (!this.isUpdating()) {
      return;
    }
    return this.questions[token];
  }

  triggerTotalScoreChange(isTrigger = true) {
    if ($('.js-total-score').length > 0 && isTrigger) {
      $('.js-total-score').trigger('change');
    }
  }

  triggerTypeCountChange(type) {
    $('*[data-type]').trigger('change', [type]);
  }

  updateQuestionList(seq) {

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