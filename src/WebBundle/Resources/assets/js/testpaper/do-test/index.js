import QuestionTypeBuilder from '../../../common/testpaper/question-type-builder';


class DoTest
{
	constructor($container) {

		this.$container = $container;
		this.answers = {};
		this.usedTime = 0;
		this.$form = $container.find('form');
		this._initEvent();
		this._init();
	}

	_initEvent() {
		this.$container.on('focusin','textarea',event=>this._showEssayInputEditor(event));
		this.$container.on('click','[data-role="paper-submit"]',event=>this._submit(event));
		this.$container.on('click','ul.testpaper-question-choices li',event=>this._choice2Lable(event));
		this.$container.on('click','*[data-anchor]',event=>this._quick2Question(event));
	}

	_init() {

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

	_choice2Lable(event) {
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
		let $target = $(event.currentTarget);
		let values = {};

		$('*[data-type]').each(function(index){
			let questionId = $(this).attr('name');
			let type = $(this).data('type');
			const questionTypeBuilder = QuestionTypeBuilder.getTypeBuilder(type);
			let answer = questionTypeBuilder.getAnswer(questionId);
			values[questionId] = answer;
		})

		$.post($target.data('url'),{data:values,usedTime:0},function(){
			console.log('111');
		})
	}
}

new DoTest($('.container'));