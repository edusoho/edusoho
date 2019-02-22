define(function(require, exports, module) {

  var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);

  var EdusohoTree = require('edusoho.tree');

  exports.run = function() {
    var $form = $('#role-form');
    var tree = new EdusohoTree({
      element: $('#tree')
    });

    $('#role-submit').on('click', function(event) {
      var checkedNodes = tree.getCheckedNodes();
      var checkedNodesArray = [];
      for (var i = 0; i < checkedNodes.length; i++) {
        checkedNodesArray.push(checkedNodes[i].code);
      }
      $('#menus').val(JSON.stringify(checkedNodesArray));
    });

    var validator = new Validator({
      element: $form,
      autoSubmit: false,
      onFormValidated: function(error, results, $form) {
        if (error) {
          return;
        }
        $.post($form.attr('action'), $form.serialize(), function(html) {
          var string = $form.attr('action');
                    
          if (string.indexOf('edit') >= 0) {
            Notify.success(Translator.trans('admin.role.update_success_hint'));
          } else{
            Notify.success(Translator.trans('admin.role.add_success_hint'));
          }
          window.location.reload();
        });

      }
    });

    validator.addItem({
      element: '#name',
      required: true,
      rule: 'byte_minlength{min:2} byte_maxlength{max:20} chinese_alphanumeric remote '
    });

    validator.addItem({
      element: '#code',
      required: true,
      rule: 'minlength{min:2} maxlength{max:20} alphanumeric not_all_digital remote'
    });
        
  };

});