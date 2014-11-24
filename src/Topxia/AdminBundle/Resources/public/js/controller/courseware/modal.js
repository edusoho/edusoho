define(function(require, exports, module){
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');
    var TagChooser = require('../../../../topxiaweb/js/controller/widget/tag-chooser/tag-chooser');

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

        var tagModalChooser = new TagChooser({
            element: '#tag-modal-chooser',
            sourceUrl: 'xxxx',
            multi: true,
            items: []
        });

        tagModalChooser.on('choosed', function(items) {
            var tagIds = [];
            for (var i = items.length - 1; i >= 0; i--) {
                tagIds[i] = items[i]['id'];
                console.log(items[i]['id']);
            };
            $('#courseware-tag-field').val(tagIds);
        });
    }
});
