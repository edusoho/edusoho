define(function(require, exports, module) {

    require('z_tree');
    require('z_tree_css');
    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var $form = $('#role-form');

        $('#role-submit').on('click', function(event) {
            var $checkedNodes = getCheckedNodes();
            var checkedNodesArray = [];
            for (var i = 0; i < $checkedNodes.length; i++) {
                var obj = {};
                obj.code = $checkedNodes[i].code;
                checkedNodesArray.push(obj);
            };
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
                    Notify.success('权限添加成功!');
                    window.location.reload();
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

        var zNodes = $('.zTreeDemoBackground').data('menus')

        function getCheckedNodes() {
            var zTree = $.fn.zTree.getZTreeObj("treeDemo");
            var nodes = zTree.getCheckedNodes(true);
            return nodes;
        }

        $.fn.zTree.init($("#treeDemo"), setting, zNodes);

        /*function arrayToJson(o) {
            var r = [];
            if (typeof o == "string") return "\"" + o.replace(/([\'\"\\])/g, "\\$1").replace(/(\n)/g, "\\n").replace(/(\r)/g, "\\r").replace(/(\t)/g, "\\t") + "\"";
            if (typeof o == "object") {
                if (!o.sort) {
                    for (var i in o)
                        r.push(i + ":" + arrayToJson(o[i]));
                    if (!!document.all && !/^\n?function\s*toString\(\)\s*\{\n?\s*\[native code\]\n?\s*\}\n?\s*$/.test(o.toString)) {
                        r.push("toString:" + o.toString.toString());
                    }
                    r = "{" + r.join() + "}";
                } else {
                    for (var i = 0; i < o.length; i++) {
                        r.push(arrayToJson(o[i]));
                    }
                    r = "[" + r.join() + "]";
                }
                return r;
            }
            return o.toString();
        }*/

    };

});