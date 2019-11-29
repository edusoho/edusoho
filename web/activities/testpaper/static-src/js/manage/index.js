import { dateFormat } from 'app/common/unit.js';

class Testpaper {
  constructor($element) {
    this.$element = $element;
    this.$form = this.$element.find('#step2-form');
    this.$testpaperSelect = this.$element.find('#testpaper-media');
    this.$questionItemShow = this.$element.find('#questionItemShowDiv');
    this._init();
  }

  _init() {
    dateFormat();
    this.setValidateRule();
    this.initTestPaperSelector();
    // this.initSelectTestpaper(this.$testpaperSelect.select2('data')[0]);
    this.initEvent();
    this.initStepForm2();
    window.ltc.on('getActivity', (msg) => {
      window.ltc.emit('returnActivity', { valid:this.validator.form(), data: window.ltc.getFormSerializeObject($('#step2-form'))});
    });

    window.ltc.on('getValidate', (msg) => {
      // window.ltc.emit('returnValidate', { valid: this.validator.form(), context: {
      //   score: $('#testpaper-media').find('option:selected').data('score')
      // }});
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
    this.$element.find('#testpaper-media').on('change', event => this.changeTestpaper(event));
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

  initSelectTestpaper($option) {
    console.log($option);
    let mediaId = $option.val();
    if (mediaId != '') {
      this.getItemsTable($option.closest('select').data('getTestpaperItems'), mediaId);
      if (!$('input[name="title"]').val()) {
        $('input[name="title"]').val($option.text());
      }
    } else {
      $('#questionItemShowDiv').hide();
    }
  }

  initTestPaperSelector() {
    let options = {
      data: [
        {
          id: 0,
          text: '请选择试卷',
          selected: true,
        }
      ],
    };

    let url = this.$testpaperSelect.data('url');
    if (url) {
      options = {
        // placeholder: $('#testpaper').val(),
        ajax: {
          url: url,
          dataType: 'json',
          data: function(params) {
            return {
              keyword: params.term,
              page: params.page,
            }
          },
          postprocessResults: function(data, params) {
            params.page = params.page || 1;

            return {
              results : data.testpapers,
              pagination : {
                more : params.page < data.openCount
              }
            };
          },
          results: function(data) {
            let results = [];

            $.each(data.testpapers, function(index, testpaper) {
              results.push({
                id: testpaper.id,
                text: testpaper.name,
              });
            });

            return {
              results: results
            };
          },
          initSelection: function(element, callback) {
            let testpaper = $('#testpaper').val();
            // testpaper = JSON.parse(testpaper);
            console.log(testpaper);
            let data = [];
            data.push({
              id: element.val(),
              text: testpaper,
              // selected: true,
            });
            callback(data);
          },
        }
      }
    }
    this.$testpaperSelect.select2(options);
  }

  changeQuestionBank(event) {
    let $helpBlock = $('.js-help-block');
    $helpBlock.addClass('hidden');
    this.$questionItemShow.hide();
    let $target = $(event.currentTarget);
    let bankId = $target.val();
    let option = `<option value="">${Translator.trans('请选择试卷')}</option>`;
    if (!bankId) {
      this.$testpaperSelect.html(option);
      return;
    }
    let url = $('#question-bank').data('url');
    url = url.replace(/[0-9]/, bankId);
    let self = this;
    $.post(url, function(resp) {
      if (resp.totalCount === 0) {
        $helpBlock.addClass('color-danger').removeClass('hidden').text(Translator.trans('queston_bank.testpaper.empty_tips'));
        self.$testpaperSelect.html(option);
        return;
      }
      if (resp.openCount === 0) {
        $helpBlock.removeClass('color-danger').removeClass('hidden').text(Translator.trans('queston_bank.testpaper.no_open_tips'));
        self.$testpaperSelect.html(option);
        return;
      }
      self.$testpaperSelect.data('url', url);
      self.initTestPaperSelector();
      // $.each(resp.testpapers, function(index, testpaper) {
      //   option += `<option value="${testpaper.id}">${testpaper.name}</option>`;
      // });
      // self.$testpaperSelect.html(option);
    }).error(function(e) {
      cd.message({type: 'danger', message: e.responseJson.error.message});
    });
  }

  changeTestpaper(event) {
    let $target = $(event.currentTarget);
    let $option = $target.find(':selected');
    console.log($option);
    this.initSelectTestpaper($option);
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
    var $this = $(event.currentTarget);

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