define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');
	var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

	exports.run = function(options) {
		var $modal = $('#teacher-promote-form').parents('.modal');
		
		var validator = new Validator({
            element: '#teacher-promote-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $('#teacher-promote-btn').button('submiting').addClass('disabled');
                $.post($form.attr('action'), $form.serialize(), function(html) {
                    
                    $modal.modal('hide');
                    var $tr = $(html);
                    $('#' + $tr.attr('id')).replaceWith($tr);
                    
                    if ($tr.data('sort') != null) {
                        var $tbody = $('#teacher-promote-table').find('tbody'),
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
                    Notify.success(Translator.trans('admin.teacher.set_recommend_success_hint'));
                    setTimeout(function(){ 
                     window.location.reload();
                    },2000);
                }).error(function(){
                    Notify.danger(Translator.trans('admin.teacher.set_recommend_fail_hint'));
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