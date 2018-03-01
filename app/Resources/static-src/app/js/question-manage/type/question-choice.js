import QuestionFormBase from './form-base';
import ReactDOM from 'react-dom';
import React from 'react';
import QuestionOptions from 'app/common/component/question-options';
import postal from 'postal';

class Choice extends QuestionFormBase {
	constructor($form) {
		super($form);
		this.isSubmit =  false;
		this.$submit = null;
		this.$questionOptions = $('#question-options');
		this.dataSource = this.$questionOptions.data('choices');
		this.dataAnswer = this.$questionOptions.data('answer');
		if(this.dataSource) {
			this.dataSource = JSON.parse(this.dataSource);
			this.dataAnswer = JSON.parse(this.dataAnswer);
		}else {
			this.dataSource = [];
			this.dataAnswer =[];
		}
		this.imageUploadUrl = this.$questionOptions.data('imageUploadUrl');
		this.imageDownloadUrl = this.$questionOptions.data('imageDownloadUrl');
		this.initTitleEditor(this.validator);
		this.initAnalysisEditor();
		this.initOptions();
		this.subscriptionMessage();
	}

	_initEvent() {
		this.$form.on('click','[data-role=submit]',event=>this.submitForm(event));
	}

	submitForm(event) {
		this.$submit = $(event.currentTarget);
		let submitType = this.$submit.data('submission');
		this.$form.find('[name=submission]').val(submitType);
    
		if(this.validator.form() && this.isSubmit ) {
			this.submit();
		}
		if(!this.isSubmit ) {
			this.publishMessage();
		}
	}

	submit() {
		this.$submit.button('loading');
		this.$form.submit();
	}

	initOptions() {
		ReactDOM.render( <QuestionOptions imageUploadUrl={this.imageUploadUrl} imageDownloadUrl={this.imageDownloadUrl} dataSource={this.dataSource} dataAnswer={this.dataAnswer}  minCheckedNum={ 2 } />,
			document.getElementById('question-options')
		);
	}

	publishMessage() {
		console.log('publishMessage');
		postal.publish({
			channel : 'manage-question',
			topic : 'question-create-form-validator-start',
			data : {
				isValidator: true,
			}
		});
	}

	subscriptionMessage() {
		console.log('subscriptionMessage');
		postal.subscribe({
			channel  : 'manage-question',
			topic    : 'question-create-form-validator-end',
			callback : (data, envelope) =>{
				this.isSubmit = data.isValidator;
				console.log({
					'subscriptionMessage':this.isSubmit
				});
				if(this.isSubmit &&  this.validator.form()) {
					console.log('submit by subscriptionMessage');
					this.submit();
				}
			}
		});
	}
}

export default Choice;