import QuestionTypeBuilder from '../../../common/testpaper/question-type-builder';
import ActivityEmitter from "../../activity/activity-emitter";

class DoTest
{
	constructor($container) {
		this.$container = $container;
		this.answers = {};
		this.usedTime = 0;
		this.$form = $container.find('form');
    this.$timer = $container.find('.js-testpaper-timer');
		this._initEvent();
    this._initTimer();
	}

	_initEvent() {
    $(".testpaper-activity-show").perfectScrollbar();
		this.$container.on('focusin','textarea',event=>this._showEssayInputEditor(event));
		this.$container.on('click','[data-role="paper-submit"]',event=>this._submit(event));
		this.$container.on('click','.js-testpaper-question-list li',event=>this._choiceList(event));
		this.$container.on('click','*[data-anchor]',event=>this._quick2Question(event));
    this.$container.find('.js-testpaper-question-label').on('click','input',event=>this._choiceLable(event));
    this.$container.on('click','.js-btn-index',event=>this._clickBtnIndex(event));
    this.$container.on('click','.js-btn-pause',event=>this._clickBtnPause(event));
    this.$container.on('click','.js-marking-toggle',event=>this._markingToggle(event));
    this.$container.on('click','.js-favorite-toggle',event=>this._favoriteToggle(event));
    this.$container.on('click','.js-analysis-toggle',event=>this._analysisToggle(event));
	}

  _markingToggle(event) {
    let $current = this._viewToggle(event);
    let id = $current.closest('.testpaper-question').attr('id');
    $(`a[data-anchor="#${id}"]`).toggleClass("have-pro");
  }

  _favoriteToggle(event) {
    let $current = this._viewToggle(event);
    console.log($current.data('url'));
  }

  _analysisToggle(event) {
    let $current = this._viewToggle(event);
    console.log($current.data('url'));
    $current.closest('.js-testpaper-question').find('.js-testpaper-question-analysis').slideToggle();
  }

  _viewToggle(event) {
    let  $this = $(event.currentTarget).toggleClass('active');
    let  $current  =  $this.children(':hidden');
    return $current;
  }

  _initTimer() {
    this.$timer.timer({
      countdown:true,
      duration: this.$timer.data('time'),
      format: '%H:%M:%S',
      callback: function() {
        console.log('结束'); 
      },
      repeat: true
    });
  }

  _clickBtnPause(event) {
    let $btn = $(event.currentTarget).toggleClass('active');
    if($btn.hasClass('active')) {
      this.$timer.timer('pause');
    }else {
      this.$timer.timer('resume');
    }
  }

  _clickBtnIndex(event) {
    let $current = $($(event.currentTarget).data('anchor'));
    $(".testpaper-activity-show").scrollTop($current.offset().top);
  }

  _choiceLable(event) {
    let $inputParents = $(event.delegateTarget);
    $inputParents.find('label').each(function(){
      $(this).find('input').prop("checked") ? $(this).addClass('active') : $(this).removeClass('active');
    });
    let $choices = $inputParents.find('label.active');
    this._renderBtnIndex($choices.find('input').attr('name'),$choices.length);
  }

  _renderBtnIndex(id,num) {
    num > 0 ? $('a[data-anchor="#question' + id + '"]').addClass('active') : $('a[data-anchor="#question' + id + '"]').removeClass('active');
  }

	_showEssayInputEditor(event) {
		let $shortTextarea = $(event.currentTarget);

		if ($shortTextarea.hasClass('essay-input-short')) {
			
			event.preventDefault();
      event.stopPropagation();
      $(this).blur();
      let $longTextarea = $shortTextarea.siblings('.essay-input-long');
      let $textareaBtn = $longTextarea.siblings('.essay-input-btn');

      $shortTextarea.hide();
      $longTextarea.show();
      $textareaBtn.show();

      let editor = CKEDITOR.replace($longTextarea.attr('id'), {
	    	toolbar: 'Minimal',
	    	filebrowserImageUploadUrl: $longTextarea.data('imageUploadUrl')
			});

			editor.on('blur', function(e) {
        editor.updateElement();
        setTimeout(function() {
          $longTextarea.val(editor.getData());
          $longTextarea.change();
        }, 1);
      });

      editor.on('instanceReady', function(e) {
        this.focus();

        $textareaBtn.one('click', function() {
          $shortTextarea.val($(editor.getData()).text());
          editor.destroy();
          $longTextarea.hide();
          $textareaBtn.hide();
          $shortTextarea.show();
        });
      });

      editor.on('key', function(){
        editor.updateElement();
        setTimeout(function() {
          $longTextarea.val(editor.getData());
          $longTextarea.change();
        }, 1);
      });

      editor.on('insertHtml', function(e) {
        editor.updateElement();
        setTimeout(function() {
          $longTextarea.val(editor.getData());
          $longTextarea.change();
        }, 1);
      });
		}
		
	}

	_choiceList(event) {
		let $target = $(event.currentTarget);
		let index = $target.index();
		let $input = $target.closest('.testpaper-question-body').siblings('.testpaper-question-footer').find('label').eq(index).find('input');

		let isChecked = $input.prop('checked');
    $input.prop('checked', !isChecked).change();

    isChecked = $input.prop('checked');
    let questionId = $input.attr('name');
    
    if(isChecked) {
			$('a[data-anchor="#question' + questionId + '"]').addClass('active');
		} else {
			$('a[data-anchor="#question' + questionId + '"]').removeClass('active');
		}
	}

	_quick2Question(event) {
		let $target = $(event.currentTarget); 
		let position = $($target.data('anchor')).offset();
    $(document).scrollTop(position.top - 55);
	}

	_submit(event) {
    console.log(event);
		let $target = $(event.currentTarget);
		let values = {};
		let emitter = new ActivityEmitter();

		$('*[data-type]').each(function(index){
			let questionId = $(this).attr('name');
			let type = $(this).data('type');
			const questionTypeBuilder = QuestionTypeBuilder.getTypeBuilder(type);
			let answer = questionTypeBuilder.getAnswer(questionId);
			values[questionId] = answer;
		})

		$.post($target.data('url'),{data:values,usedTime:0})
		.done((response) => {
			if (response.result) {
				emitter.emit('finish');
			}
		})
	}
}

new DoTest($('.testpaper-activity-show'));