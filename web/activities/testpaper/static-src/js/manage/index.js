import {dateFormat, htmlEscape} from 'app/common/unit.js';

class Testpaper {
  constructor($element) {
    this.$element = $element;
    this.$form = this.$element.find('#step2-form');
    this.$questionBankSelector = this.$element.find('#question-bank');
    this.$testpaperSelector = this.$element.find('#testpaper-media');
    this.$questionItemShow = this.$element.find('#questionItemShowDiv');
    this.$scoreItem = this.$element.find('.js-score-form-group');
    this._init();
  }

  _init() {
    dateFormat();
    this.setValidateRule();
    this.initQuestionBankSelector();
    this.initTestPaperSelector();
    this.initSelectTestPaper(this.$testpaperSelector.select2('data'));
    this.initEvent();
    this.initStepForm2();
    this.initAddComment();
    window.ltc.on('getActivity', (msg) => {
      window.ltc.emit('returnActivity', {
        valid: this.validator.form(),
        data: window.ltc.getFormSerializeObject($('#step2-form'))
      });
    });

    window.ltc.on('getValidate', (msg) => {
      window.ltc.emit('returnValidate', {
        valid: this.validator.form(), context: {
          score: this.$testpaperSelector.select2('data').score,
        }
      });
      window.ltc.emit('returnValidate', {valid: this.validator.form()});
    });
  }

  setValidateRule() {
    $.validator.addMethod('arithmeticFloat', function (value, element) {
      return this.optional(element) || /^[0-9]+(\.[0-9]?)?$/.test(value);
    }, $.validator.format(Translator.trans('activity.testpaper_manage.arithmetic_float_error_hint')));
  }

  initEvent() {
    this.$element.find('#question-bank').on('change', event => this.changeQuestionBank(event));
    this.$element.find('#testpaper-media').on('change', event => this.changeTestPaper(event));
    this.$element.find('input[name=doTimes]').on('change', event => this.showRedoInterval(event));
    this.$element.find('input[name="testMode"]').on('change', event => this.startTimeCheck(event));
  }

  initAddComment() {
    let $customCommentTable = $('#customCommentTable');
    let ii = $customCommentTable.find('tr').length;
    $('#addComment').on('click', function () {
      let tr = '<tr>\n' +
        '              <td class="form-inline">\n' +
        '                <input type="text" class="form-control" name="start['+ii+']" style="width: 47px;"> -\n' +
        '                <input type="text" class="form-control" name="end['+ii+']" style="width: 47px;">\n' +
        '              </td>\n' +
        '              <td class="form-inline">\n' +
        '                <textarea name="comment['+ii+']" rows="1" maxlength="1500" class="form-control js-comment-content" style="width: 339px;margin-right: 15px;"></textarea>\n' +
        '                <div class="default-comment">\n' +
        '                  <a href="javascript:;" class="js-default-comment">' + Translator.trans('activity.testpaper_manage.default_comment') + '</a>\n' +
        '                   <div class="default-comment-list hidden">' +
        '                   <div class="default-comment-list__item js-default-comment-item">' + Translator.trans('activity.testpaper_manage.default_comment1') + '</div>' +
        '                    <div class="default-comment-list__item js-default-comment-item">' + Translator.trans('activity.testpaper_manage.default_comment2') + '</div>' +
        '                    <div class="default-comment-list__item js-default-comment-item">' + Translator.trans('activity.testpaper_manage.default_comment3') + '</div>' +
        '                    <div class="default-comment-list__item js-default-comment-item">' + Translator.trans('activity.testpaper_manage.default_comment4') + '</div>' +
        '                    <div class="default-comment-list__item js-default-comment-item">' + Translator.trans('activity.testpaper_manage.default_comment5') + '</div>' +
        '                  </div>' +
        '                </div>\n' +
        '              </td>\n' +
        '              <td class="form-inline vertical-middle">\n' +
        '                <a href="javascript:;" class="js-comment-remove">' + Translator.trans('activity.testpaper_manage.comment_remove') + '</a>\n' +
        '              </td>\n' +
        '            </tr>';
      $customCommentTable.append(tr);
      $customCommentTable.removeClass('hidden');
      ii++;
    });
    $customCommentTable.on('input', '.js-comment-content', function (e) {
      const scrollHeight = e.target.scrollHeight - 12;
      $(this).height(scrollHeight);
    });
    $customCommentTable.on('click', '.js-comment-remove', function () {
      $(this).parent().parent().remove();
    });

    $customCommentTable.on('click', '.js-default-comment-item', function () {
      $(this).parent().parent().siblings('.js-comment-content').val($(this).text());
    });
  }

  initStepForm2() {
    this.validator = this.$form.validate({
      onkeyup: false,
      rules: {
        title: {
          required: true,
          trim: true,
          maxlength: 50,
          course_title: true,
        },
        testpaperId: {
          required: true,
          digits: true,
          min: 1,
        },
        length: {
          required: true,
          digits: true
        },
        startTime: {
          required: function () {
            return ($('[name="doTimes"]:checked').val() == 1) && ($('[name="testMode"]:checked').val() == 'realTime');
          },
          DateAndTime: function () {
            return ($('[name="doTimes"]:checked').val() == 1) && ($('[name="testMode"]:checked').val() == 'realTime');
          }
        },
        redoInterval: {
          required: function () {
            return $('[name="doTimes"]:checked').val() == 0;
          },
          arithmeticFloat: true,
          max: 1000000000
        }
      },
      messages: {
        testpaperId: {
          required: Translator.trans('activity.testpaper_manage.media_error_hint'),
          min: Translator.trans('activity.testpaper_manage.media_error_hint'),
        },
        redoInterval: {
          max: Translator.trans('activity.testpaper_manage.max_error_hint')
        },
      }
    });
  }

  initSelectTestPaper($selected) {
    let mediaId = parseInt($selected.id);
    if (mediaId) {
      this.getItemsTable(this.$testpaperSelector.data('getTestpaperItems'), mediaId);
      if (!$('input[name="title"]').val()) {
        $('input[name="title"]').val($selected.text);
      }
      this.initScoreSlider();
    } else {
      $('#questionItemShowDiv').hide();
      $('#js-test-and-comment').hide();
    }
  }

  initEmptyTestPaperSelector() {
    this.$testpaperSelector.select2({
      data: [
        {
          id: '0',
          text: Translator.trans('activity.testpaper_manage.media_required'),
          selected: true,
        }
      ],
    });
  }

  initAjaxTestPaperSelector() {
    let self = this;
    this.$testpaperSelector.select2({
      ajax: {
        url: self.$testpaperSelector.data('url'),
        dataType: 'json',
        quietMillis: 250,
        data: function (term, page) {
          return {
            keyword: term,
            page: page,
          };
        },
        results: function (data, page) {
          let results = [];

          $.each(data.testPapers, function (index, testPaper) {
            results.push({
              id: testPaper.id,
              text: testPaper.name,
              score: testPaper.score,
            });
          });

          return {
            results: results,
            more: page * 10 < data.openCount,
          };
        },
      },
      initSelection: function (element, callback) {
        let testPaperName = $('#testPaperName').val();
        let testPaperId = element.val();
        let testPaperScore = $('#score-condition').data('score');
        if (!parseInt(testPaperId)) {
          testPaperName = '';
        }
        let data = {
          id: testPaperId,
          text: testPaperName ? testPaperName : Translator.trans('activity.testpaper_manage.media_required'),
          score: testPaperScore,
        };

        callback(data);
      },
      formatSelection: function (data) {
        return data.text;
      },
      dropdownAutoWidth: true,
    });
    this.$testpaperSelector.removeClass('hidden');
  }

  initQuestionBankSelector() {
    this.$questionBankSelector.select2({
      treeview: true,
      dropdownAutoWidth: true,
      treeviewInitState: 'collapsed',
      placeholderOption: 'first',
      formatResult: function (item) {
        let text = htmlEscape(item.text);
        if (!item.id) {
          return text;
        }
        return `<div class="select2-result-text"><span class="select2-match"></span><span><i class="es-icon es-icon-tiku"></i>${text}</span></div>`;
      },
      dropdownCss: {
        width: ''
      },
    });
  }

  initTestPaperSelector() {
    if ($('#testPaperName').val()) {
      this.initAjaxTestPaperSelector();
    } else {
      this.initEmptyTestPaperSelector();
    }
  }

  changeQuestionBank(event) {
    let $helpBlock = $('.js-help-block');
    $helpBlock.addClass('hidden');
    this.$testpaperSelector.addClass('hidden');
    this.$questionItemShow.hide();
    this.$scoreItem.hide();
    this.$testpaperSelector.val('0');

    let selected = this.$questionBankSelector.select2('data');
    let bankId = selected.id;
    if (!parseInt(bankId)) {
      this.initEmptyTestPaperSelector();
      return;
    }
    let url = this.$questionBankSelector.data('url');
    url = url.replace(/[0-9]/, bankId);
    let self = this;
    $.post(url, function (resp) {
      if (resp.totalCount === 0) {
        $helpBlock.addClass('color-danger').removeClass('hidden').text(Translator.trans('queston_bank.testpaper.empty_tips')).show();
        self.initEmptyTestPaperSelector();
        return;
      }
      if (resp.openCount === 0) {
        $helpBlock.removeClass('color-danger').removeClass('hidden').text(Translator.trans('queston_bank.testpaper.no_open_tips')).show();
        self.initEmptyTestPaperSelector();
        return;
      }
      self.$testpaperSelector.data('url', url);
      self.initAjaxTestPaperSelector();
    }).error(function (e) {
      cd.message({type: 'danger', message: e.responseJson.error.message});
    });
  }

  changeTestPaper(event) {
    let $selected = this.$testpaperSelector.select2('data');
    this.initSelectTestPaper($selected);
  }

  showRedoInterval(event) {
    let $this = $(event.currentTarget);
    if ($('input[name="showAnswerMode"]:checked').length) {
      $('input[name="showAnswerMode"]:checked').prop('checked', false);
    }
    if ($this.val() == 1) {
      $('#lesson-redo-interval-field').closest('.form-group').hide();
      $('.starttime-check-div').show();
      $('.js-show-answer-mode').length > 0 && $('.js-show-answer-mode').hide();
    } else {
      $('#lesson-redo-interval-field').closest('.form-group').show();
      $('.starttime-check-div').hide();
      $('.js-show-answer-mode').length > 0 && $('.js-show-answer-mode').show();
    }
  }

  startTimeCheck(event) {
    let $this = $(event.currentTarget);

    if ($this.val() == 'realTime') {
      $('.starttime-input').removeClass('hidden');
      this.dateTimePicker();
    } else {
      $('.starttime-input').addClass('hidden');
    }
  }

  changeCondition(event) {
    let $this = $(event.currentTarget);
    let value = $this.find('option:selected').val();
    value != 'score' ? $('.js-score-form-group').addClass('hidden') : $('.js-score-form-group').removeClass('hidden');
  }

  getItemsTable(url, testpaperId) {
    $.post(url, {testpaperId: testpaperId}, function (html) {
      $('#questionItemShowTable').html(html);
      $('#questionItemShowDiv').show();
    });
  }

  dateTimePicker() {
    let data = new Date();
    let $starttime = $('#startTime');
    if ($starttime.is(':visible') && ($starttime.val() == '' || $starttime.val() == '0')) {
      $starttime.val(data.Format('yyyy-MM-dd hh:mm'));
    }
    $starttime.datetimepicker({
      autoclose: true,
      format: 'yyyy-mm-dd hh:ii',
      language: document.documentElement.lang,
      minView: 'hour',
      endDate: new Date(Date.now() + 86400 * 365 * 10 * 1000)
    }).on('show', event => {
      this.$form.height(this.$form.height() + 270);
    })
      .on('hide', event => {
        this.validator.form();
        this.$form.height(this.$form.height() - 270);
      })
      .on('changeDate', event => {
      });
    $starttime.datetimepicker('setStartDate', data);
  }

  initScoreSlider() {
    let score = 0;
    if (this.$testpaperSelector.select2('data').score) {
      score = this.$testpaperSelector.select2('data').score;
    } else {
      score = $('#score-condition').data('score');
    }
    $('.js-score-total').text(score);
    let passScore = Math.round(score * $('#score-condition').data('pass'));
    score = parseInt(score);

    let scoreSlider = document.getElementById('score-slider');
    let option = {
      start: passScore,
      connect: [true, false],
      tooltips: [true],
      step: 1,
      range: {
        'min': 0,
        'max': score
      }
    };

    if (this.scoreSlider) {
      this.scoreSlider.destroy();
    }

    this.scoreSlider = noUiSlider.create(scoreSlider, option);
    scoreSlider.noUiSlider.on('update', function (values, handle) {
      let rate = values[handle] / score;
      let percentage = (rate * 100).toFixed(0);
      $('.noUi-tooltip').text(`${percentage}%`);
      $('.js-score-tooltip').css('left', `${percentage}%`);
      $('.js-passScore').text(Math.round(percentage / 100 * score));
      $('#finishData').val(percentage / 100);
    });

    let tooltipInnerText = Translator.trans('activity.testpaper_manage.qualified_score_hint', {'passScore': '<span class="js-passScore">' + passScore + '</span>'});
    let html = `<div class="score-tooltip js-score-tooltip"><div class="tooltip top" role="tooltip" style="">
      <div class="tooltip-arrow"></div>
      <div class="tooltip-inner ">
        ${tooltipInnerText}
      </div>
      </div></div>`;
    $('.noUi-handle').append(html);
    $('.noUi-tooltip').text(`${(passScore / score * 100).toFixed(0)}%`);
    $('.js-score-tooltip').css('left', `${(passScore / score * 100).toFixed(0)}%`);
    $('#js-test-and-comment').show();
  }
}

new Testpaper($('#iframe-content'));