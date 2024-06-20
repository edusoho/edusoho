import { dateFormat, htmlEscape } from 'app/common/unit.js';
import DateRangePicker from 'app/common/daterangepicker';
import 'moment';

const locale = {
  'format': 'YYYY/MM/DD HH:mm:ss',
  'separator': '-',
  'applyLabel': '确定',
  'cancelLabel': '取消',
  'fromLabel': '起始时间',
  'toLabel': '结束时间',
  'customRangeLabel': '自定义',
  'weekLabel': 'W',
  'daysOfWeek': [
    '日',
    '一',
    '二',
    '三',
    '四',
    '五',
    '六'
  ],
  'monthNames': [
    '一月',
    '二月',
    '三月',
    '四月',
    '五月',
    '六月',
    '七月',
    '八月',
    '九月',
    '十月',
    '十一月',
    '十二月'
  ],
  'firstDay': 1
};

if (app.lang !== 'zh_CN') {
  locale = {
    'format': 'YYYY/MM/DD HH:mm:ss',
    'separator': '-',
    'applyLabel': 'Apply',
    'cancelLabel': 'Cancel',
    'fromLabel': 'From',
    'toLabel': 'To',
    'customRangeLabel': 'Custom',
    'weekLabel': 'W',
    'daysOfWeek': [
      'Su',
      'Mo',
      'Tu',
      'We',
      'Th',
      'Fr',
      'Sa'
    ],
    'monthNames': [
      'January',
      'February',
      'March',
      'April',
      'May',
      'June',
      'July',
      'August',
      'September',
      'October',
      'November',
      'December'
    ],
    'firstDay': 1
  };
}


class Testpaper {
  constructor($element) {
    this.$element = $element;
    this.$form = this.$element.find('#step2-form');
    this.$questionBankSelector = this.$element.find('#question-bank');
    this.$testpaperSelector = this.$element.find('#testpaper-media');
    this.$questionItemShow = this.$element.find('#questionItemShowDiv');
    this.$scoreItem = this.$element.find('.js-score-form-group');
    this.$rangeStartTime = $('.js-start-range')
    this.$rangeDateInput = $('.js-realTimeRange-data');
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
    this.initDatePicker();
    this.initFormItemData();

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
      window.ltc.emit('returnValidate', { valid: this.validator.form() });
    });

    if ($('.form-switch').length) {
      $('[data-toggle="switch"]').on('click', function () {
        var $this = $(this);
        var $parent = $this.parent();
        var isEnable = $this.val();
        var reverseEnable = isEnable == 1 ? 0 : 1;

        if ($parent.hasClass('checked')) {
          $parent.removeClass('checked');
        } else {
          $parent.addClass('checked');
        }
        $this.val(reverseEnable);
        $this.next().val(reverseEnable);
      });
    }

  }

  initDatePicker() {
    const todayYear=(new Date()).getFullYear();
    const todayMonth=(new Date()).getMonth();
    const todayDay=(new Date()).getDate();
    const todayTime=(new Date(todayYear,todayMonth,todayDay,'23','59','59')).getTime();//毫秒
    const validPeriodMode = $('[name="validPeriodMode"]:checked').val()

    const activityId = $('#activityId').val()
    const startTime = $('[name=startTime]').val()
    const endTime = $('[name=endTime]').val()
    this.$rangeDateInput.daterangepicker({
      "timePicker": true,
      "timePicker24Hour": true,
      "timePickerSeconds": true,
      'autoUpdateInput':false,
      'minDate': new Date(),
      'endDate': validPeriodMode == '1' ? endTime != '0' ? endTime : todayTime : todayTime,
      'startDate': validPeriodMode == '1' ? activityId != '0' ? startTime : moment().startOf('seconds') : moment().startOf('seconds'),
      locale,
    });
    
    this.$rangeDateInput.on('apply.daterangepicker', function(ev, picker) {
      $('input[name=startTime]').val(picker.startDate.format('YYYY-MM-DD HH:mm:ss'))
      $('input[name=endTime]').val(picker.endDate.format('YYYY-MM-DD HH:mm:ss'))
      $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm:ss') +' - ' + picker.endDate.format('YYYY-MM-DD HH:mm:ss'));
    });

    this.$rangeStartTime.daterangepicker({
      "timePicker": true,
      'singleDatePicker': true,
      "timePicker24Hour": true,
      "timePickerSeconds": true,
      'autoUpdateInput':false,
      'minDate': new Date(),
      'startDate': validPeriodMode == '2' ? activityId != '0' ? startTime : moment().startOf('seconds') : moment().startOf('seconds'),
      locale,
    });
    
    this.$rangeStartTime.on('apply.daterangepicker', function(ev, picker) {
      $('input[name=startTime]').val(picker.startDate.format('YYYY-MM-DD HH:mm:ss'))
      $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm:ss'));
    });
  }

  initFormItemData() {
    const activityId = $('#activityId').val()
    const validPeriodMode = $('[name="validPeriodMode"]:checked').val()
    
    if (activityId == 0) return

    const startTime = $('[name=startTime]').val()
    const endTime = $('[name=endTime]').val()
    const defaultStartTime = startTime == '0' ? '' : startTime
    const defaultEndTime = endTime == '0' ? '' : endTime

    if(validPeriodMode == 1) {
      defaultStartTime == '' && defaultEndTime == '' ? this.$rangeDateInput.val() : this.$rangeDateInput.val(defaultStartTime + ' - ' + defaultEndTime)
    } else if(validPeriodMode == 2) {
      this.$rangeStartTime.val(defaultStartTime)
    }
  }

  setValidateRule() {
    $.validator.addMethod('arithmeticFloat', function (value, element) {
      return this.optional(element) || /^[0-9]+(\.[0-9]?)?$/.test(value);
    }, $.validator.format(Translator.trans('activity.testpaper_manage.arithmetic_float_error_hint')));

    $.validator.addMethod('examLength', function (value, element) {
      if ($('input[name="exam_mode"]').val() == 0 && value <= 0) {
        return false;
      }

      return true;
    }, $.validator.format(Translator.trans('course.plan_task.activity_manage.testpaper.mock_tips4')));
  }

  initEvent() {
    this.$element.find('#question-bank').on('change', event => this.changeQuestionBank(event));
    this.$element.find('#testpaper-media').on('change', event => this.changeTestPaper(event));
    this.$element.find('input[name=validPeriodMode]').on('change', event => this.showRedoExamination(event));
    this.$element.find('input[name=isLimitDoTimes]').on('change', event => this.showRedoInterval(event));
    this.$element.find('input[name="testMode"]').on('change', event => this.startTimeCheck(event));
    this.$element.find('.js-testpaper-mode').on('click', event => this.switchExamMode(event));
  }

  initAddComment() {
    let $customCommentTable = $('#customCommentTable');
    let ii = $customCommentTable.find('tr').length;
    $('#addComment').on('click', function () {
      let tr = '<tr>\n' +
        '              <td class="form-inline">\n' +
        '                <input type="text" class="form-control" name="start[' + ii + ']" style="width: 47px; padding: 6px;"> -\n' +
        '                <input type="text" class="form-control" name="end[' + ii + ']" style="width: 47px; padding: 6px;">\n' +
        '              </td>\n' +
        '              <td class="form-inline">\n' +
        '                <textarea name="comment[' + ii + ']" rows="1" maxlength="1500" class="form-control js-comment-content" style="width: 310px;margin-right: 15px;"></textarea>\n' +
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
      if($customCommentTable.find('tr').length == 1) {
        $customCommentTable.addClass('hidden')
      }
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
          byte_maxlength: 100,
          course_title: true,
        },
        testpaperId: {
          required: true,
          digits: true,
          min: 1,
        },
        length: {
          required: true,
          digits: true,
          examLength: true
        },
        doTimes: {
          required: () => $('[name="isLimitDoTimes"]:checked').val() == 1,
          optional_range: {
            optional: () => $('[name="isLimitDoTimes"]:checked').val() == 0,
            range: [1, 100],
          },
          digits: true,
          // section_number: () => $('[name="isLimitDoTimes"]:checked').val() == 1 ? /^([1-9][0-9]{0,1}|100)$/ : '',
        },
        rangeTime: {
          required: () => $('[name="validPeriodMode"]:checked').val() == 1,
        },
        rangeStartTime: {
          required: () => $('[name="validPeriodMode"]:checked').val() == 2,
        },
        redoInterval: {
          required: function () {
            return $('[name="isLimitDoTimes"]:checked').val() == 0;
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
          required: Translator.trans('validate.required.message', { 'display': Translator.trans('validate.valid_enter.retest.interval') }),
          max: Translator.trans('activity.testpaper_manage.max_error_hint')
        },
        length: {
          required: Translator.trans('validate.required.message', { 'display': Translator.trans('course.plan_task.activity_manage.testpaper.time_limit') }),
        },
        doTimes: {
          required: Translator.trans('validate.valid_enter_a_positive.integer')
        },
        rangeTime: {
          required: Translator.trans('validate.valid_rangetime.required')
        },
        rangeStartTime: {
          required: Translator.trans('validate.valid_starttime.required')
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
      cd.message({ type: 'danger', message: e.responseJson.error.message });
    });
  }

  changeTestPaper(event) {
    let $selected = this.$testpaperSelector.select2('data');
    this.initSelectTestPaper($selected);
  }

  showRedoExamination(event) {
    const $this = $(event.currentTarget);

    if ($this.val() == 0) {
      this.$rangeStartTime.attr('type', 'hidden');
      $('.js-realTimeRange-data').attr('type', 'hidden');
    }

    if ($this.val() == 1) {
      $('.js-realTimeRange-data').attr('type', 'test');
      this.$rangeStartTime.attr('type', 'hidden');
    }

    if ($this.val() == 2) {
      this.$rangeStartTime.attr('type', 'test');
      $('.js-realTimeRange-data').attr('type', 'hidden');
    }
  }

  showRedoInterval(event) {
    const $this = $(event.currentTarget);

    if ($this.val() == 1) {
      $('.js-examinations-num').attr('type', 'text');
    }

    if ($this.val() == 0) {
      $('.js-examinations-num').attr('type', 'hidden');
    }
  }

  changeCondition(event) {
    let $this = $(event.currentTarget);
    let value = $this.find('option:selected').val();
    value != 'score' ? $('.js-score-form-group').addClass('hidden') : $('.js-score-form-group').removeClass('hidden');
  }

  getItemsTable(url, testpaperId) {
    $.post(url, { testpaperId: testpaperId }, function (html) {
      $('#questionItemShowTable').html(html);
      $('#questionItemShowDiv').show();
    });
  }

  initScoreSlider() {
    let score = 0;
    if (this.$testpaperSelector.select2('data').score) {
      score = Number(this.$testpaperSelector.select2('data').score);
    } else {
      score = Number($('#score-condition').data('score'));
    }
    $('.js-score-total').text(score);
    let passScore = score * $('#score-condition').data('pass');


    if (passScore % 1 != 0) {
      passScore = Number(passScore.toFixed(1));
    }

    if (score % 1 != 0) {
      score = Number(score.toFixed(1));
    }

    let scoreSlider = document.getElementById('score-slider');
    let option = {
      start: passScore,
      connect: [true, false],
      tooltips: [true],
      step: 0.1,
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
      let jsPassScore = percentage / 100 * score

      if (jsPassScore % 1 != 0) {
        jsPassScore = Number(jsPassScore.toFixed(1));
      }

      $('.js-passScore').text(jsPassScore);
      $('#finishData').val(percentage / 100);
    });

    let tooltipInnerText = Translator.trans('activity.testpaper_manage.qualified_score_hint', { 'passScore': '<span class="js-passScore">' + passScore + '</span>' });
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

  switchExamMode(event) {
    const $this = $(event.currentTarget);
    const examModeValue = $this.data('value');

    this.$element.find('#examMode').val(examModeValue);
    this.$element.find('.js-testpaper-mode').removeClass('active');
    $this.addClass('active');
    $('.js-mode-helpblock').removeClass('hidden')

    if (examModeValue == '0') {
      $('.js-enable_facein').removeClass('hidden')
      $('label[for="length"]').addClass('control-label-required')
      $('.js-mode-helpblock.js-mode-helpblock-1').addClass('hidden')
    } else if (examModeValue == '1') {
      $('.js-enable_facein').addClass('hidden')
      $('label[for="length"]').removeClass('control-label-required')
      $('.js-mode-helpblock.js-mode-helpblock-0').addClass('hidden')
    }
  }
}

new Testpaper($('#iframe-content'));
