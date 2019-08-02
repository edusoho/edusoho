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
    "errors":{
        "answer":{
            "element":"answer",
            "index":-1,
            "code":100003,
            "message":"缺少正确答案"
        }
    }
}
*/
export default class QuestionOperate {
  constructor() {
    this.questions = {};
    this.eventManager = {};
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
      let question = cachedData[i];
      this.questions[token] = question;
      this.tokenList.push(token);
      let index = i;
      $(`[data-anchor="#${index}"]`).attr('data-anchor', '#' + token);
      let $item = $('#' + index).attr('id', token);
      if (question['type'] == 'material') {
        $.each(question['subQuestions'], function (key, subQuestion) {
          $item = $item.next(`[data-key="${key}"]`).attr('data-material-token', token);
        })
      }
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
        self.totalScore = (parseFloat(self.totalScore) + parseFloat(question['score'])).toFixed(1);
      } else {
        $.each(question['subQuestions'], function(token, subQuestion) {
          self.totalScore = (parseFloat(self.totalScore) + parseFloat(subQuestion['score'])).toFixed(1);
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

  getTokenList() {
    return this.tokenList;
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

  modifyScore(selectQuestion, scoreObj, isTestpaper) {
    let self = this;
    $.each(selectQuestion, function(index, token) {
      self.$itemList.find(`#${token}`).find('.js-score').html(`${scoreObj['score']}${Translator.trans('subject.question_score_unit')}`);
      let question = self.getQuestion(token);
      self.updateQuestionItem(token, 'score', scoreObj['score'], false);
      if (isTestpaper) {
        if (question['type'] == 'choice' || question['type'] == 'uncertain_choice') {
          self.updateQuestionItem(token, 'missScore', scoreObj['missScore']);
        }
      }
    });
    self.trigger('updateQuestionScore');
  }

  addQuestion(preToken, question) {
    if (!this.isUpdating()) {
      return;
    }
    this.flag = true;
    if (question['type'] == 'material') {
      question['subQuestions'] = [];
      question['score'] = 0;
    }
    let token = this._getToken();
    this.questions[token] = question;
    let position = this.tokenList.indexOf(preToken) + 1;
    this.tokenList.splice(position, 0, token);
    this.questionCounts['total']++;
    this.questionCounts[question['type']]++;
    this.totalScore = (parseFloat(this.totalScore) + parseFloat(question['score'])).toFixed(1);
    let index = position + 1;
    this.trigger('addQuestion', [index, token, question['type']]);
    this.flag = false;

    return token;
  }

  deleteQuestion(deleteToken) {
    if (!this.isUpdating()) {
      return;
    }
    let self = this;
    this.flag = true;
    const question = this.questions[deleteToken];
    delete this.questions[deleteToken];
    let position = this.tokenList.indexOf(deleteToken);
    this.tokenList.splice(position, 1);
    this.questionCounts['total']--;
    this.questionCounts[question['type']]--;
    if (question['type'] != 'material') {
      this.totalScore = (parseFloat(this.totalScore) - parseFloat(question['score'])).toFixed(1);
    } else {
      $.each(question['subQuestions'], function(token, subQuestion) {
        self.totalScore = (parseFloat(self.totalScore) - parseFloat(subQuestion['score'])).toFixed(1);
      })
    }
    this.trigger('deleteQuestion', [question['type']]);
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
      this.totalScore = (this.totalScore - parseFloat(oldValue) + parseFloat(itemValue)).toFixed(1);
      this.trigger('updateQuestionScore', [isTrigger]);
    }
    if (itemKey == 'type' && oldValue != itemValue) {
      this.questionCounts[oldValue]--;
      this.questionCounts[itemValue]++;
      this.trigger('updateQuestionType', [itemKey, itemValue, oldValue, token])
    }
    this.flag = false;
  }

  getQuestion(token) {
    if (!this.isUpdating()) {
      return;
    }
    return this.questions[token];
  }

  getQuestions() {
    return this.questions;
  }

  getSubQuestion(token, key) {
    if (!this.isUpdating()) {
      return;
    }
    return this.questions[token]['subQuestions'][key];
  }

  addSubQuestion(token, question) {
    if (!this.isUpdating()) {
      return;
    }
    this.flag = true;
    this.questions[token]['subQuestions'].push(question);
    this.totalScore = (parseFloat(this.totalScore) + parseFloat(question['score'])).toFixed(1);
    this.trigger('updateQuestionScore');
    this.flag = false;

    return token;
  }

  updateSubQuestionItem(token, key, itemKey, itemValue, isTrigger = true) {
    if (!this.isUpdating()) {
      return;
    }
    this.flag = true;
    let oldValue = this.questions[token]['subQuestions'][key][itemKey];
    this.questions[token]['subQuestions'][key][itemKey] = itemValue;
    if (itemKey == 'score') {
      this.totalScore = (this.totalScore - parseFloat(oldValue) + parseFloat(itemValue)).toFixed(1);
      this.trigger('updateQuestionScore', [isTrigger]);
    }
    this.flag = false;
  }

  deleteSubQuestion(deleteToken, key) {
    if (!this.isUpdating()) {
      return;
    }
    this.flag = true;
    const question = this.questions[deleteToken]['subQuestions'][key];
    this.questions[deleteToken]['subQuestions'].splice(key, 1);
    this.totalScore = (parseFloat(this.totalScore) - parseFloat(question['score'])).toFixed(1);
    this.correctMaterialQuestion(deleteToken);
    this.trigger('updateQuestionScore');
    this.flag = false;
  }

  correctQuestion(token) {
    let question = this.questions[token];
    if (typeof question['errors'] == 'undefined') {
      return;
    }

    if (question['type'] == 'material' && typeof question['errors']['hasSubError'] != 'undefined') {
      question['errors'] = {"hasSubError" : true};
    } else {
      delete question['errors'];
    }

    this.questions[token] = question;
    if (typeof question['errors'] == 'undefined') {
      this.trigger('correctQuestion', [token]);
    }
  }

  correctSubQuestion(token, key) {
    let material = this.questions[token];
    let subQuestion = material['subQuestions'][key];

    if (typeof subQuestion['errors'] == 'undefined') {
      return;
    }

    delete subQuestion['errors'];
    material['subQuestions'][key] = subQuestion;
    this.questions[token] = material;
    this.correctMaterialQuestion(token);
  }

  correctMaterialQuestion(token) {
    let material = this.questions[token];
    let hasSubError = false;
    $.each(material['subQuestions'], function(index, sub) {
      if (typeof sub['errors'] != 'undefined') {
        hasSubError = true;
      }
    });

    if (!hasSubError && typeof material['errors'] != 'undefined') {
      delete material['errors']['hasSubError'];
    }

    if ($.isEmptyObject(material['errors'])) {
      delete material['errors'];
      this.trigger('correctQuestion', [token]);
    }
    this.questions[token] = material;
  }

  isUpdating() {
    if (this.flag == true) {
      cd.message({ type: 'danger', message: Translator.trans('course.question.is_updating_hint') });
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

  on(event, fn) {
    if (!this.eventManager[event]) {
      this.eventManager[event] = [fn.bind(this)];
    } else {
      this.eventManager[event].push(fn.bind(this));
    }
  }

  trigger(event, data) {
    if (this.eventManager[event]) {
      this.eventManager[event].map(function(fn) {
        fn.apply(null, data);
      });
    }
  }

  _random() {
    return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
  }

  _getToken() {
    return (this._random()+this._random()+"-"+this._random()+"-"+this._random()+"-"+this._random()+"-"+this._random()+this._random()+this._random());
  }
}