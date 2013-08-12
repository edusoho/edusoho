define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var $form = $('#navigation-form');
        var $modal = $form.parents('.modal');
        var $table = $('#navigation-table');

        var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }
                
                $.post($form.attr('action'), $form.serialize(), function(response){
                    if (response.status == 'ok') {
                        var $html = $(response.html);
                        var $type = response.type;
                        var $typeInHtml = $("#navigationType").text();

                        if( $typeInHtml == "all"){
                            $table.find('tbody').prepend(response.html);
                            Notify.success('创建成功!'); 
                        } else if ( $typeInHtml == $type ) {
                            $table.find('tbody').prepend(response.html);
                            Notify.success('创建成功!'); 
                        } else{
                            Notify.success('创建成功!'); 
                        }
                        $modal.modal('hide');
                    }
                }, 'json');
            }

        });

        validator.addItem({
            element: '[name="form[name]"]',
            required: true
        });

        validator.addItem({
            element: '[name="form[url]"]',
            required: true
        });

    };

});