define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');
	var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

	exports.run = function(options) {
		var $modal = $('#course-recommend-form').parents('.modal');
		
		var validator = new Validator({
            element: '#course-recommend-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $('#course-recommend-btn').button('submiting').addClass('disabled');
                $.post($form.attr('action'), $form.serialize(), function(html) {
                    $modal.modal('hide');
                    Notify.success(Translator.trans('设置推荐课程操作成功!'));
                    var $tr = $(html);
					$('#' + $tr.attr('id')).replaceWith($tr);

                    if ($tr.data('sort') != null) {
                        var $tbody = $('#course-recommend-table').find('tbody'),
                            trs = $tbody.find('tr').sort(function(a,b){
                            return $(a).data('sort') - $(b).data('sort');
                        });

                        $tbody.find('tr').remove();
                        for (tr in trs) {
                            if(!isNaN(parseInt(tr,10))){
                                $(trs[tr]).appendTo($tbody);
                            }
                        }
                    }



                }).error(function(){
                    Notify.danger(Translator.trans('设置推荐课程操作失败!'));
                });
            }

        });

        validator.addItem({
            element: '[name="number"]',
            required: true,
            rule: 'integer min{min: 0} max{max: 10000}'
        });

		

	};

});

















