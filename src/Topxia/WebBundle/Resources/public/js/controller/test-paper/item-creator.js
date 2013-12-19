define(function(require, exports, module) {

	var Widget = require('widget');
	var Handlebars = require('handlebars');

	var ItemCreator = Widget.extend({
		attrs:{
			questionType: null,
			material: null
		},

		events: {
            'show.bs.tab a[data-toggle="tab"]': 'tabShow',
            'shown.bs.tab a[data-toggle="tab"]': 'tabShown',
            'click [data-role=batch-select] ': 'batchSelect',
		},

		setup:function(){
			this._initTab();
		},

		tabShow:function(){
			this.$('.test-item-tbody').addClass('tab-pane');
		},

		tabShown:function(){
			this.$('.test-item-tbody.active').removeClass('active tab-pane');
			this.$('[data-role=batch-select], [data-role=batch-item]').prop('checked', false);
		},

		batchSelect:function(e){
			if ($(e.currentTarget).is(":checked") == true){
                this.$('[data-role=batch-select]:visible, [data-role=batch-item]:visible').prop('checked', true);
            } else {
                this.$('[data-role=batch-select]:visible, [data-role=batch-item]:visible').prop('checked', false);
            }
		},

		_initTab: function (){
			var questionType = $('[data-role=questionType-data]').html();
			if(typeof questionType != 'undefined'){
                this.set('questionType', $.parseJSON(questionType));
            }

            var material = $('[data-role=material-data]').html();
			if(typeof material != 'undefined'){
                this.set('material', $.parseJSON(material));
            }
		},

		_onChangeQuestionType: function	(questionType){
			var self = this;
			$.each(questionType, function(type, name){
				var html = "<tbody id="+type+" class='tab-pane test-item-tbody'></tbody>";
            	self.$('[data-role=item-body]').after(html);
                $('#'+type).append(self.$('[data-type=' + type + ']'));
            });
            self.$('#myTab li:first a').trigger('click');
		}

		,

		_onChangeMaterial: function	(material){
			var self = this;
			$.each(material, function(type, name){
				var html = "<tbody id="+type+" class='tab-pane test-item-tbody'></tbody>";
            	self.$('[data-role=item-body]').after(html);
                $('#'+type).append(self.$('[data-type=' + type + ']'));
            });
		}


	});

	module.exports = ItemCreator;



});