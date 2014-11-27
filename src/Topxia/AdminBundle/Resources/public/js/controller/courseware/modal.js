define(function(require, exports, module){
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');
    var TagChooser = require('../tag2/tag-chooser');

    var chooser = new TagChooser({
        element: '#tag-chooser',
        sourceUrl: '/admin/knowledge/match',
        // sourceUrl: '/admin/tagset/match',
        // multi: true,
        multi: true,
        type: 'knowledge',
        items: []
    });

    exports.run = function(){
        var $form = $("#courseware-form");
        $modal = $form.parents('.modal');
        var validator = _initValidator($form, $modal);

        function _initValidator($form, $modal) {
                var validator = new Validator({
                element: '#courseware-form',
                failSilently: true,
                triggerType: 'change',
                onFormValidated: function(error, results, $form) {
                    if (error) {
                        return false;
                    }
                    $('#courseware-operate-btn').button('loading').addClass('disabled');
                    Notify.success('操作成功！');
                }
            });

            validator.addItem({
                element: '[name=url]',
                required: true,
                rule: 'url'
            });

            validator.addItem({
                element: '[name=mainKnowledgeId]',
                required: true
            });

            validator.addItem({
                element: '[name=relatedKnowledgeIds]',
                required: true
            });

            validator.addItem({
                element: '[name=tagIds]',
                required: true
            });

            validator.addItem({
                element: '[name=source]',
                required: true
            });

            return validator;
        }

        $('#import-courseware-url').click(function(){
            $url = $('#courseware-url-field').val();

            $re = /^(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?$/;
            if ($re.test($url)) {
                $(this).button('loading');
                $.post($(this).data('url'),{url:$url},function(result){
                    $('[data-role=courseware-title]').html(result.title);
                    $('#import-courseware-url').button('reset')
                });
            }
        });

    }
});