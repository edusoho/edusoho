define(function(require, exports, module) {

	var Widget = require('widget');
	var Handlebars = require('handlebars');
    var Notify = require('common/bootstrap-notify');

	var ItemBase = Widget.extend({
		attrs:{
			Handlebars: Handlebars,
			Notify: Notify,
			questionType: [],
		},

		events: {
            'show.bs.tab a[data-toggle="tab"]' : 'tabShow',
            'shown.bs.tab a[data-toggle="tab"]': 'tabShown',
            'click [data-role=batch-select]'   : 'batchSelect',
            'click [data-role=item-modal-btn]' : 'itemModal',
            'click .btn-submit-index' : 'onSubmit',
		},

		setup:function(){
			this._initTab();
			this._initItemList();
		},

		tabShow: function(){
			this.$('.test-item-tbody').addClass('tab-pane');
		},

		tabShown: function(){
			this.$('.test-item-tbody.active').removeClass('active tab-pane');
			this.$('[data-role=batch-select], [data-role=batch-item]').prop('checked', false);

			var total = 0;
			var questionTotal = 0;
			var questionType = $('#myTab .active a').text();
			var questionConut = $('[name^=scores]:visible').length;

			$('[name^=scores][type=text]').each(function(){
			    total = Number($(this).val()) + Number(total);
			});

			$('[name^=scores]:visible').each(function(){
			    questionTotal = Number($(this).val()) + Number(questionTotal);
			});

			if(isNaN(total) || isNaN(questionTotal)){
				total = 0;
				questionTotal = 0;
			}
			
			var html = "试卷总分" + total + "分 " + questionType + questionConut + "题/ "+ questionTotal + "分";

			this.$('.score-text-alert').html(html);
		},

		onSubmit: function(e){
			$.get($(e.currentTarget).data('url'), '', function(data){
                console.log(data);
            })
		},

		itemModal: function(e){
			var ids = new Array();

			this.$('[data-role=batch-item]:visible').each(function(){
				ids.push(this.value);
			});

			var href = $('#myTab .active a').attr('href').split("#");

			var url  = $(e.currentTarget).data('url')+'&type='+href[1]+"&ids="+ids;

            $.get(url, '', function(data){
                $($(e.currentTarget).data('target')).html(data).modal({
                    backdrop:true,
                    keyboard:true,
                    show:true,
                });
            })
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
		},

		_initItemList: function(){
            require('./batch-delete')($(this.element));
            require('./item-delete')($(this.element));
        },

		_onChangeQuestionType: function	(questionType){
			var self = this;
			$.each(questionType, function(key, value){
				var id = 'questionType-'+key;
				var html = "<tbody id="+id+" class='tab-pane test-item-tbody'></tbody>";
        		self.$('[data-role=item-body]').after(html);
                
                if (self.$('[data-type=' + key + ']').length == 0) {
                	var empty = "<tr><td colspan='20'><div class='empty'>暂无题目,请添加</div></td></tr>";
                	$('#'+id).append(empty);
                } else {
                	$('#'+id).append(self.$('[data-type=' + key + ']'));
                }

                if('material' == key){
                	self.$('[data-type=' + key + ']').each(function(index){
                		$(this).after(self.$('[data-type=' + $(this).attr('id') + ']'));
                	});
                }
            });

            self.$('#myTab li:first a').trigger('click');
		},



	});

	module.exports = ItemBase;



});