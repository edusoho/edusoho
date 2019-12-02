import { dateFormat } from 'app/common/unit.js';

class Testpaper {
  constructor($element) {
    this.$element = $element;
    this.$form = this.$element.find('#step2-form');
    this.$testpaperSelector = this.$element.find('#testpaper-media');
    this.$questionItemShow = this.$element.find('#questionItemShowDiv');
    this._init();
  }

  _init() {
    dateFormat();
    this.setValidateRule();
    this.initTestPaperSelector();
    this.initSelectTestPaper(this.$testpaperSelector.select2('data'));
    this.initEvent();
    this.initStepForm2();
    window.ltc.on('getActivity', (msg) => {
      window.ltc.emit('returnActivity', { valid: this.validator.form(), data: window.ltc.getFormSerializeObject($('#step2-form'))});
    });

    window.ltc.on('getValidate', (msg) => {
      window.ltc.emit('returnValidate', { valid: this.validator.form(), context: {
        score: this.$testpaperSelector.select2('data').score,
      }});
      window.ltc.emit('returnValidate', { valid: this.validator.form() });
    });
  }

  setValidateRule() {
    $.validator.addMethod('arithmeticFloat',function(value,element){  
      return this.optional( element ) || /^[0-9]+(\.[0-9]?)?$/.test(value);
    }, $.validator.format(Translator.trans('activity.testpaper_manage.arithmetic_float_error_hint')));
  }

  initEvent() {
    this.$element.find('#question-bank').on('change', event => this.changeQuestionBank(event));
    this.$element.find('#testpaper-media').on('change', event => this.changeTestPaper(event));
    this.$element.find('input[name=doTimes]').on('change', event => this.showRedoInterval(event));
    this.$element.find('input[name="testMode"]').on('change', event => this.startTimeCheck(event));
  }

  initStepForm2() {
    this.validator = this.$form.validate({
      onkeyup: false,
      rules: {
        title: {
          required:true,
          trim: true,
          maxlength: 50,
          course_title: true,
        },
        testpaperId: {
          required: true,
          digits:true
        },
        length:{
          required:true,
          digits:true
        },
        startTime:{
          required:function(){
            return ($('[name="doTimes"]:checked').val() == 1) && ($('[name="testMode"]:checked').val() == 'realTime');
          },
          DateAndTime:function(){
            return ($('[name="doTimes"]:checked').val() == 1) && ($('[name="testMode"]:checked').val() == 'realTime');
          }
        },
        redoInterval:{
          required:function(){
            return $('[name="doTimes"]:checked').val() == 0;
          },
          arithmeticFloat:true,
          max:1000000000
        }
      },
      messages: {
        testpaperId: {
          required:Translator.trans('activity.testpaper_manage.media_error_hint'),
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
    } else {
      $('#questionItemShowDiv').hide();
    }
  }

  initEmptyTestPaperSelector() {
    this.$testpaperSelector.select2({
      data: [
        {
          id: '0',
          text: '请选择试卷',
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
        data: function(term, page) {
          return {
            keyword: term,
            page: page,
          };
        },
        results: function(data, page) {
          let results = [];

          $.each(data.testPapers, function(index, testPaper) {
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
      initSelection: function(element, callback) {
        let testPaperName = $('#testPaperName').val();
        let testPaperId = element.val();
        if (!parseInt(testPaperId)) {
          testPaperName = '';
        }
        let data = {
          id: testPaperId,
          text: testPaperName ? testPaperName : '请选择试卷',
        };

        callback(data);
      },
      formatSelection: function(data) {
        return data.text;
      }
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
    this.$questionItemShow.hide();
    this.$testpaperSelector.val('0');

    let $target = $(event.currentTarget);
    let bankId = $target.val();
    if (!parseInt(bankId)) {
      this.initEmptyTestPaperSelector();
      return;
    }
    let url = $('#question-bank').data('url');
    url = url.replace(/[0-9]/, bankId);
    let self = this;
    $.post(url, function(resp) {
      if (resp.totalCount === 0) {
        $helpBlock.addClass('color-danger').removeClass('hidden').text(Translator.trans('queston_bank.testpaper.empty_tips'));
        self.initEmptyTestPaperSelector();
        return;
      }
      if (resp.openCount === 0) {
        $helpBlock.removeClass('color-danger').removeClass('hidden').text(Translator.trans('queston_bank.testpaper.no_open_tips'));
        self.initEmptyTestPaperSelector();
        return;
      }
      self.$testpaperSelector.data('url', url);
      self.initAjaxTestPaperSelector();
      // $.each(resp.testpapers, function(index, testpaper) {
      //   option += `<option value="${testpaper.id}">${testpaper.name}</option>`;
      // });
      // self.$testpaperSelect.html(option);
    }).error(function(e) {
      cd.message({type: 'danger', message: e.responseJson.error.message});
    });
  }

  changeTestPaper(event) {
    let $selected = this.$testpaperSelector.select2('data');
    this.initSelectTestPaper($selected);
  }

  showRedoInterval(event) {
    let $this = $(event.currentTarget);
    if ($this.val() == 1) {
      $('#lesson-redo-interval-field').closest('.form-group').hide();
      $('.starttime-check-div').show();
    } else {
      $('#lesson-redo-interval-field').closest('.form-group').show();
      $('.starttime-check-div').hide();
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
    value!='score' ? $('.js-score-form-group').addClass('hidden') : $('.js-score-form-group').removeClass('hidden');
  }

  getItemsTable(url, testpaperId) {
    $.post(url, {testpaperId:testpaperId}, function(html){
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
      endDate: new Date(Date.now() + 86400 * 365 * 10 *1000)
    }).on('show', event => {
      this.$form.height(this.$form.height() + 270);
    })
      .on('hide', event => {
        this.validator.form();
        this.$form.height(this.$form.height() - 270);
      })
      .on('changeDate',event =>{
      });
    $starttime.datetimepicker('setStartDate', data);
  }
}

new Testpaper($('#iframe-content'));