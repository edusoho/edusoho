define(function(require, exports, module) {

    require('z_tree');
    require('z_tree_check');
    require('z_tree_css');
    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var $form = $('#role-form');

        $('#role-submit').click(function(event) {
            $('#menus').val(getCheckedNodes());
        });
        var validator = new Validator({
            element: $form,
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return ;
                }
                $.post($form.attr('action'), $form.serialize(), function(html){
                    Notify.success('标签添加成功!');
                });

            }
        });

        validator.addItem({
            element: '#name',
            required: true,
            rule: 'remote'
        });

        validator.addItem({
            element: '#code',
            required: true,
            rule: 'remote alphanumeric'
        });

        var setting = {
            check: {
                enable: true
            },
            data: {
                simpleData: {
                    enable: true
                }
            }
        };

        var zNodes =$('.zTreeDemoBackground').data('menus')
        
        function getCheckedNodes(){
            var zTree = $.fn.zTree.getZTreeObj("treeDemo");
            var nodes = zTree.getCheckedNodes(true);
            return nodes;
        }

        $(document).ready(function(){
            $.fn.zTree.init($("#treeDemo"), setting, zNodes);
        });

    };

});