define(function(require, exports, module) {

	var Widget     = require('widget');
    var Handlebars = require('handlebars');
	var Notify     = require('common/bootstrap-notify');
	var Test       = require('./util/util');
    require('jquery.sortable');

	var ItemBase = Widget.extend({

		attrs:{
			Notify: Notify,
			questionType: [],
			confirmTrTemplate: null,
		},

		events: {
            'show.bs.tab  a[data-toggle="tab"]' : 'menuTabShow',
            'shown.bs.tab a[data-toggle="tab"]' : 'menuTabShown',
            'click [data-role=item-modal-btn]'  : 'itemListModal',
            'show.bs.modal [id=confirm-modal]'  : 'addConfirmTr',
            'click [id=confirm-modal] .confirm-submit'  : 'submit',
            'keyup [name^=scores][type=text]'   : function(){ Test.menuTotal() },
		},

		setup:function(){
			this._initTab();
			this._initItemList();
			this._initConfirmModal();
		},

		menuTabShow: function(){
			this.$('.test-item-tbody').addClass('tab-pane');
		},

		menuTabShown: function(){

			this.$('.test-item-tbody.active').removeClass('active tab-pane');
			this.$('[data-role=batch-select], [data-role=batch-item]').prop('checked', false);
			
			Test.menuTotal();
		},

		itemListModal: function(e){

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
	                Test.sortable();
	            },
	        });
		},

		addConfirmTr: function(allTotal){
			var template = this.get('confirmTrTemplate');

			var allTotal = Test.getMenuAllTotal();
			$('.confirm-tbody').empty();
			$.each(allTotal, function(key, value){
	            var $html = $($.trim(template(value)));
	            $html.appendTo($('.confirm-tbody'));
			})
			
		},

		submit: function(e){
			$(e.currentTarget).text("努力保存中...");
			this.$('#test-create2-form').submit();
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

        _initConfirmModal: function(e){

        	var confirmTrTemplate = Handlebars.compile($('[data-role=confirm-tr-template]').html());
        	this.set('confirmTrTemplate', confirmTrTemplate);
		},

		_onChangeQuestionType: function	(questionType){

			var self = this;

			$.each(questionType, function(key, value){

				var id = 'questionType-'+key;

				var html = "<tbody id="+id+" class='tab-pane test-item-tbody'></tbody>";

        		self.$('[data-role=item-body]').after(html);
                
                if (self.$('[data-type=' + key + ']').length == 0) 
                {
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