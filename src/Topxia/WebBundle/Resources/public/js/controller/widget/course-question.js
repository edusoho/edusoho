define(function(require, exports, module) {

	var Widget = require('widget'),
		Templatable = require('templatable'),
		Handlebars = require('handlebars');	
		

	var CourseQuestionWidget = Widget.extend({
		template: '<h3>xxx</h3>',
		events: {
			'submit .course-question-widget-form': 'createQuestion',
		},

        parseElement: function() {
            CourseQuestionWidget.superclass.parseElement.call(this);
        },

		setup: function() {

			var element = this.element;
			var source   = $("#course-question-widget-template").html();
			var template = Handlebars.compile(source);
			var modal = {
				createUrl: element.data('createUrl'),
			};
			this.element.html(template(modal));
		},

		createQuestion: function(e) {
			e.preventDefault();
			var $form = $('.course-question-widget-form');

			$.post($form.data('url'), $form.serialize(), function(response){
			});

		}
	});


	module.exports = CourseQuestionWidget;
	});