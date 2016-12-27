import sortList from 'common/sortable';

class TestpaperForm{
  constructor($form) {
    this.$form = $form;
    this.$description = this.$form.find('[name="description"]');
    this.validator = null;
    this.difficultySlider = null;
    this._initEvent();
    this._initEditor();
    this._initValidate();
    this._initSortList();
  }

  _initEvent() {
    this.$form.on('click','[data-role="submit"]',event=>this._submit(event));
    this.$form.on('click','[name="mode"]',event=>this.changeMode(event))
    this.initDifficultySlider();
    this.initScoreSlider();
  }

  initScoreSlider() {
    let scoreSlider = document.getElementById('score-slider');
    console.log(scoreSlider);
    noUiSlider.create(scoreSlider, {
      start: 20,
      tooltips: [true],
      step: 1,
      range: {
        'min': 0,
        'max': 100
      }
    });
  }

  changeMode(event) {
    let $this = $(event.currentTarget);
    ($this.val() == 'difficulty') ? $('#difficulty-form-group').removeClass('hidden') : $('#difficulty-form-group').addClass('hidden');
  }

  initDifficultySlider() {
    if(!this.difficultySlider ) {
      let sliders = document.getElementById('difficulty-percentage-slider');
      this.difficultySlider = noUiSlider.create(sliders, {
        start: [ 30, 70 ],
        margin: 30,
        range: {
          'min': 0,
          'max': 100
        },
        step: 5,
        serialization: {
          resolution: 1
        },
      });
      sliders.noUiSlider.on('update', function( values, handle ){
        let simplePercentage = values[0],
        normalPercentage = values[1] - values[0],
        difficultyPercentage = 100 - values[1];
        $('.simple-percentage-text').html(Translator.trans('简单') + simplePercentage + '%');
        $('.normal-percentage-text').html(Translator.trans('一般') + normalPercentage + '%');
        $('.difficulty-percentage-text').html(Translator.trans('困难') + difficultyPercentage + '%');
        $('input[name="percentages[simple]"]').val(simplePercentage);
        $('input[name="percentages[normal]"]').val(normalPercentage);
        $('input[name="percentages[difficulty]"]').val(difficultyPercentage);
      });
    }
  }

  _initEditor() {
    let editor = CKEDITOR.replace(this.$description.attr('id'), {
      toolbar: 'Simple',
      filebrowserImageUploadUrl: this.$description.data('imageUploadUrl'),
      height: 100
    });
    editor.on( 'change', () => {    
      this.$description.val(editor.getData());
    });
  }

  _initValidate() {
    this.validator = this.$form.validate({
      rules:{
        name:{
          required:true
        },
        description:{
          required:true,
          maxlength:500
        },
        limitedTime:{
          min:0,
          max:10000,
          digits:true
        },
        mode:{
          required:true
        },
        range:{
          required:true
        }
      },
      messages:{
        name:"请输入试卷名称",
        description:"请输入试卷描述",
        mode:"请选择生成方式",
        range:"请选择出题范围"
      }
    });
    this.$form.find('.testpaper-question-option-item').each(function(){
      let self = $(this);
      self.find('[data-role="count"]').rules('add',{
        min:0,
        max:function(){
          return parseInt(self.find('[role="questionNum"]').text());
        },
        digits:true
      })

      self.find('[data-role="score"]').rules('add',{
        min:0,
        max:100,
        digits:true
      })

      if (self.find('[data-role="missScore"]').length > 0) {
        self.find('[data-role="missScore"]').rules('add',{
          min:0,
          max:function(){
            return parseInt(self.find('[data-role="score"]').val());
          },
          digits:true
        })
      }
    })

  }

  _initSortList() {
    sortList({
      element:'#testpaper-question-options',
      itemSelector:'.testpaper-question-option-item',
      handle: '.question-type-sort-handler',
      isAjax:false
    });
  }

  _submit(event){
    let status = this.validator.form();
    if (status) {
      this.$form.submit();
    }
  }
}

new TestpaperForm($('#testpaper-form'));