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
    this.scoreSlider = null;
  }

  _initEvent() {
    this.$form.on('click','[data-role="submit"]',event=>this._submit(event));
    this.$form.on('click','[name="mode"]',event=>this.changeMode(event));
    this.$form.on('click','[name="range"]',event=>this.changeRange(event));
    this.$form.on('blur','[data-role="count"]',event=>this.changeCount(event));
  }

  initScoreSlider(passScore,score) {
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
    if(this.scoreSlider) {
      this.scoreSlider.updateOptions(option);
    }else {
      this.scoreSlider = noUiSlider.create(scoreSlider,option);
      scoreSlider.noUiSlider.on('update', function( values, handle ){
        $('.noUi-tooltip').text(`${(values[handle]/score*100).toFixed(0)}%`);
        $('.js-passScore').text(parseInt(values[handle]));
      });
    }
    $('.noUi-handle').attr('data-placement','top').attr('data-original-title',`达标分数：<span class="js-passScore">${passScore}</span>分`).attr('data-container','body');
    $('.noUi-handle').tooltip({html: true})
    $('.noUi-tooltip').text(`${(passScore/score*100).toFixed(0)}%`);
  }

  changeMode(event) {
    let $this = $(event.currentTarget);
    if($this.val() == 'difficulty') {
      this.$form.find('#difficulty-form-group').removeClass('hidden');
      this.initDifficultySlider();
    }else {
      this.$form.find('#difficulty-form-group').addClass('hidden')
    }
  }

  changeRange(event) {
    let $this = $(event.currentTarget);
    ($this.val() == 'course') ? this.$form.find('#testpaper-range-selects').addClass('hidden') : this.$form.find('#testpaper-range-selects').removeClass('hidden');
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
        step: 1,
        connect: [true, true,true],
        serialization: {
          resolution: 1
        },
      });
      sliders.noUiSlider.on('update', function( values, handle ){
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

  changeCount() {
    let num = 0;
    this.$form.find('[data-role="count"]').each(function(index,item){
      num += parseInt($(item).val());
    });
    this.$form.find('[name="questioncount"]').val(num > 0 ? num : null);
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
        },
        questioncount: {
          required:true
        }
      },
      messages:{
        questioncount:"请选择题目",
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
      ajax:false
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


