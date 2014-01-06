define(function(require, exports, module) {

	var Widget     = require('widget');
	var Handlebars = require('handlebars');
	var Notify     = require('common/bootstrap-notify');
	var Test       = require('./util/util');
    require('jquery.sortable');

	var ItemBase = Widget.extend({

		attrs:{
			Handlebars: Handlebars,
			Notify: Notify,
			questionType: [],
		},

		events: {
            'show.bs.tab a[data-toggle="tab"]' : 'tabShow',
            'shown.bs.tab a[data-toggle="tab"]': 'tabShown',
            'click [data-role=item-modal-btn]' : 'itemModal',
            'click .btn-submit-index'          : 'onSubmit',
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
			
			Test.utli();
		},

		onSubmit: function(e){
			$.post($(e.currentTarget).data('url'), '', function(data){
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

		sortable: function(id){
			$('#'+id).sortable({
	            itemSelector: '[data-role=item]',
	            exclude: '.notMoveHandle',
	            onDrop: function (item, container, _super) {
	                _super(item, container);

	                // $.each(this.get('questionType'), function(key, value){

	                // };

                    // $list.find('.item-lesson').each(function(index){
                    //     $(this).find('.seq').text(index+1);
                    // });
	            },
	            serialize: function(parent, children, isContainer) {
	                return isContainer ? children : parent.attr('id');
	            }
	        });
		},

		_initTab: function (){
			var questionType = $('[data-role=questionType-data]').html();
			if(typeof questionType != 'undefined'){
                this.set('questionType', $.parseJSON(questionType));
            }
		},

		_initItemList: function(){
            require('./util/batch-delete')($(this.element));
            require('./util/item-delete')($(this.element));
            require('./util/batch-select')($(this.element));
        },

		_onChangeQuestionType: function	(questionType){
			var self = this;
			$.each(questionType, function(key, value){
				var id = 'questionType-'+key;
				var html = "<tbody id="+id+" class='tab-pane test-item-tbody'></tbody>";
        		self.$('[data-role=item-body]').after(html);
                
                if (self.$('[data-type=' + key + ']').length == 0) {
                	$('#'+id).append("<tr><td colspan='20'><div class='empty'>暂无题目,请添加</div></td></tr>");
                } else {
                	$('#'+id).append(self.$('[data-type=' + key + ']'));
                }

                if('material' == key){
                	self.$('[data-type=' + key + ']').each(function(index){
                		$(this).after(self.$('[data-type=' + $(this).attr('id') + ']'));
                	});
                }

                self.sortable(id);

            });

            self.$('#myTab li:first a').trigger('click');
		},

	});

	module.exports = ItemBase;



});