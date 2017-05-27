import sortList from 'common/sortable';
import { delHtmlTag } from 'common/utils';
import SelectLinkage from 'app/js/question-manage/widget/select-linkage.js';

class TestpaperForm {
  constructor($form) {
    this.$form = $form;
    this.$description = this.$form.find('[name="description"]');
    this.validator = null;
    this.difficultySlider = null;
    this._initEvent();
    this._initValidate();
    this._initSortList();
    this.scoreSlider = null;
  }

  _initEvent() {
    this.$form.on('click', '[data-role="submit"]', event => this._submit(event));
    this.$form.on('click', '[name="mode"]', event => this.changeMode(event));
    this.$form.on('click', '[name="range"]', event => this.changeRange(event));
    this.$form.on('blur', '[data-role="count"]', event => this.changeCount(event));
  }

  initScoreSlider(passScore, score) {
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
    }
    if (this.scoreSlider) {
      this.scoreSlider.updateOptions(option);
    } else {
      this.scoreSlider = noUiSlider.create(scoreSlider, option);
      scoreSlider.noUiSlider.on('update', function (values, handle) {
        $('.noUi-tooltip').text(`${(values[handle] / score * 100).toFixed(0)}%`);
        $('.js-passScore').text(parseInt(values[handle]));
      });
    }
    $('.noUi-handle').attr('data-placement', 'top').attr('data-original-title', `达标分数：<span class="js-passScore">${passScore}</span>分`).attr('data-container', 'body');
    $('.noUi-handle').tooltip({ html: true })
    $('.noUi-tooltip').text(`${(passScore / score * 100).toFixed(0)}%`);
  }

  changeMode(event) {
    let $this = $(event.currentTarget);
    if ($this.val() == 'difficulty') {
      this.$form.find('#difficulty-form-group').removeClass('hidden');
      this.initDifficultySlider();
    } else {
      this.$form.find('#difficulty-form-group').addClass('hidden')
    }
  }

  changeRange(event) {
    let $this = $(event.currentTarget);
    ($this.val() == 'course') ? this.$form.find('#testpaper-range-selects').addClass('hidden') : this.$form.find('#testpaper-range-selects').removeClass('hidden');
  }

  initDifficultySlider() {
    if (!this.difficultySlider) {
      let sliders = document.getElementById('difficulty-percentage-slider');
      this.difficultySlider = noUiSlider.create(sliders, {
        start: [30, 70],
        margin: 30,
        range: {
          'min': 0,
          'max': 100
        },
        step: 1,
        connect: [true, true, true],
        serialization: {
          resolution: 1
        },
      });
      sliders.noUiSlider.on('update', function (values, handle) {
        let simplePercentage = parseInt(values[0]),
          normalPercentage = values[1] - values[0],
          difficultyPercentage = 100 - values[1];
        $('.js-simple-percentage-text').html(Translator.trans('简单') + simplePercentage + '%');
        $('.js-normal-percentage-text').html(Translator.trans('一般') + normalPercentage + '%');
        $('.js-difficulty-percentage-text').html(Translator.trans('困难') + difficultyPercentage + '%');
        $('input[name="percentages[simple]"]').val(simplePercentage);
        $('input[name="percentages[normal]"]').val(normalPercentage);
        $('input[name="percentages[difficulty]"]').val(difficultyPercentage);
      });
    }
  }

  _initEditor(validator) {
    let editor = CKEDITOR.replace(this.$description.attr('id'), {
      toolbar: 'Simple',
      filebrowserImageUploadUrl: this.$description.data('imageUploadUrl'),
      height: 100
    });
    editor.on('change', (a, b, c) => {
      this.$description.val(delHtmlTag(editor.getData()));
    });
    editor.on('blur', () => {
      this.$description.val(delHtmlTag(editor.getData()));//fix ie11
      validator.form();
    });
  }

  changeCount() {
    let num = 0;
    this.$form.find('[data-role="count"]').each(function (index, item) {
      num += parseInt($(item).val());
    });
    this.$form.find('[name="questioncount"]').val(num > 0 ? num : null);
  }

  _initValidate() {
    this.validator = this.$form.validate({
      rules: {
        name: {
          required: true,
          maxlength: 50,
          trim: true,
        },
        description: {
          required: true,
          maxlength: 500,
          trim: true,
        },
        limitedTime: {
          min: 0,
          max: 10000,
          digits: true
        },
        mode: {
          required: true
        },
        range: {
          required: true
        },
        questioncount: {
          required: true
        }
      },
      messages: {
        questioncount: "请选择题目",
        name: {
          required: "请输入试卷名称",
          maxlength: "最多只能输入50个字符"
        }, 
        description: {
          required: "请输入试卷描述",
          maxlength: "最多只能输入500个字符"
        },
        mode: "请选择生成方式",
        range: "请选择出题范围"
      }
    });
    this.$form.find('.testpaper-question-option-item').each(function () {
      let self = $(this);
      self.find('[data-role="count"]').rules('add', {
        min: 0,
        max: function () {
          return parseInt(self.find('[role="questionNum"]').text());
        },
        digits: true
      })

      self.find('[data-role="score"]').rules('add', {
        min: 0,
        max: 100,
        digits: true
      })

      if (self.find('[data-role="missScore"]').length > 0) {
        self.find('[data-role="missScore"]').rules('add', {
          min: 0,
          max: function () {
            return parseInt(self.find('[data-role="score"]').val());
          },
          digits: true
        })
      }
    })
    this._initEditor(this.validator);
  }

  _initSortList() {
    sortList({
      element: '#testpaper-question-options',
      itemSelector: '.testpaper-question-option-item',
      handle: '.question-type-sort-handler',
      ajax: false
    });
  }

  _submit(event) {
    let $target = $(event.currentTarget);
    let status = this.validator.form();
    
    if (status) {
      $.post($target.data('checkUrl'),this.$form.serialize(),result => {
        if (result.status == 'no') {
          $('.js-build-check').html('该范围内题目数量不足');
        } else {
          $('.js-build-check').html('');
          
          $target.button('loading').addClass('disabled');
          this.$form.submit();
        }
      })

    }
  }
}

new TestpaperForm($('#testpaper-form'));
new SelectLinkage($('[name="ranges[courseId]"]'),$('[name="ranges[lessonId]"]'));

$('[name="ranges[courseId]"]').change(function(){
  let url = $(this).data('checkNumUrl');
  checkQuestionNum(url);
})

$('[name="ranges[lessonId]"]').change(function(){
  let url = $(this).data('checkNumUrl');
  checkQuestionNum(url);
})

function checkQuestionNum(url) {
  let courseId = $('[name="ranges[courseId]"]').val();
  let lessonId = $('[name="ranges[lessonId]"]').val();

  $.post(url,{courseId:courseId, lessonId:lessonId},function(data){
    $('[role="questionNum"]').text(0);

    $.each(data,function(i,n){
      $("[type='"+i+"']").text(n.questionNum);
    });
  })
}


