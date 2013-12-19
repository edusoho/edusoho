define(function(require, exports, module) {

	var Widget = require('widget');
	var Handlebars = require('handlebars');

	var ItemCreator = Widget.extend({
		attrs:{
			questionType: null
		},

		events: {
            'show.bs.tab a[data-toggle="tab"]': 'tabShow',
            'shown.bs.tab a[data-toggle="tab"]': 'tabShown',
            'click [data-role=batch-select] ': 'batchSelect',
		},

		setup:function(){
			var questionType = $('[data-role=questionType-data]').html();
			if(typeof questionType != 'undefined'){
                this.set('questionType', $.parseJSON(questionType));
            }
		},

		tabShow:function(){
			this.$('.test-item-tbody').addClass('tab-pane');
		},

		tabShown:function(){
			this.$('.test-item-tbody.active').removeClass('active tab-pane');
			this.$('[data-role=batch-select], [data-role=batch-item]').prop('checked', false);
		},

		batchSelect:function(e){
			if( $(e.currentTarget).is(":checked") == true){
                this.$('[data-role=batch-select]:visible, [data-role=batch-item]:visible').prop('checked', true);
            } else {
                this.$('[data-role=batch-select]:visible, [data-role=batch-item]:visible').prop('checked', false);
            }
		},

		_onChangeQuestionType: function	(questionType){
			var self = this;
			$.each(questionType, function(index, type){
				var html = "<tbody id="+index+" class='tab-pane test-item-tbody'></tbody>";
            	self.$('[data-role=item-body]').after(html);
                $('#'+index).append(self.$('[data-type=' + index + ']'));
            });

		}


	});

	module.exports = ItemCreator;



});