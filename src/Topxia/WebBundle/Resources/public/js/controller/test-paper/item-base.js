define(function(require, exports, module) {

	var Widget = require('widget');
	var Handlebars = require('handlebars');
    var Notify = require('common/bootstrap-notify');

	var ItemBase = Widget.extend({
		attrs:{
			Handlebars: Handlebars,
			Notify: Notify,
			questionType: [],
			material: [],
		},

		events: {
            'show.bs.tab a[data-toggle="tab"]' : 'tabShow',
            'shown.bs.tab a[data-toggle="tab"]': 'tabShown',
            'click [data-role=batch-select]'   : 'batchSelect',
            'click [data-role=item-modal-btn]' : 'itemModal',
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
		},

		itemModal: function(e){
			var href = $('#myTab .active a').attr('href').split("#");
			var url  = $(e.currentTarget).data('url')+'&type='+href[1];
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
            var material = $('[data-role=material-data]').html();
			if(typeof material != 'undefined'){
                this.set('material', $.parseJSON(material));
            }

			var questionType = $('[data-role=questionType-data]').html();
			if(typeof questionType != 'undefined'){
                this.set('questionType', $.parseJSON(questionType));
            }
		},

		_initItemList: function(){
            require('../../util/batch-delete')($(this.element));
            require('../../util/item-delete')($(this.element));
        },

		_onChangeMaterial: function	(material){
			var self = this;
			$.each(material, function(key, value){
				var id = 'parentId-'+key;
				var html = "<tbody id="+id+" class='tab-pane test-item-tbody'></tbody>";
            	self.$('[data-role=item-body]').after(html);
                $('#'+id).append(self.$('[data-type=' + key + ']'));
            });

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

                if(key == 'material'){
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