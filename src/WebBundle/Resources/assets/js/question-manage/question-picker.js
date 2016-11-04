
console.log($('#question-picker-body'));

class QuestionPicker
{
	constructor($pickerDiv)
	{
		this.$pickBody = $pickerDiv;
		this.$modal = this.$pickBody.closest('.modal');

		this._initEvent();
	}

	_initEvent()
	{	console.log(this.$pickBody);
		console.log($('.question-picker-body'));
		$('.search-question-btn').on('click', event=>this._searchQuestion(event))
	}

	_searchQuestion(event)
	{console.log('111');
		/*let $this = $(event.currentTarget);
		let $form = $this.closest('form');
		event.preventDefault();

		$.get($form.attr('action'), $form.serialize(), function(html) {
			console.log(html);
			console.log($this.closest('.modal'));
            //$this.closest('.modal').html(html);
        });*/
	}
}

new QuestionPicker($('#question-picker-body'));