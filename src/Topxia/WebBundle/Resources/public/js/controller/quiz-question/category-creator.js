define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    // var Handlebars = require('handlebars');
    var Validator = require('bootstrap.validator');

    exports.run = function() {
    	// var targets = $.parseJSON($('[data-role=targets-data]').html());
    	// var options = '';
     //    if(typeof (targets.default)  != 'undefined'){
     //        var selected = targets.default;
     //        delete targets.default;
     //    }
        // $.each(targets, function(index, target){
        //     var value = target.type+'-'+target.id;
        //     if(value == selected){
        //         options += '<option selected=selected value=' + value + '>' + target.name + '</option>';
        //     }else{
        //         options += '<option value=' + value + '>' + target.name + '</option>';
        //     }
        // });
        // $('[data-role=target]').html(options);

        var validator = new Validator({
            element: '#category-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                $.post($form.attr('action'), $form.serialize(), function(html) {
                  var id = '#' + $(html).attr('id'),
                      $item = $(id);
                  if ($item.length) {
                      $item.replaceWith(html);
                      Notify.success('保存成功');
                  } else {
                      $(".tbady-category").prepend(html);
                      Notify.success('添加成功');
                  }
                  $form.parents('.modal').modal('hide');
              });

            }
        });

        validator.addItem({
            element: '#category-name-field',
            required: true,
            rule: 'maxlength{max:100}'
        });

        
    };

});