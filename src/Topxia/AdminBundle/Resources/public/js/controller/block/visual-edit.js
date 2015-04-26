define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var Widget = require('widget');
    var Uploader = require('upload');
    exports.run = function() {
        var editForm = Widget.extend({
            events: {
                    'click .js-add-collapse' : 'onAddBtn',
                    'change .lesson-content input' : 'onChangeUpdateBtn'
            },

            setup: function() {
                this.$('.img-upload').each(function(){
                    var self = $(this);
                    new Uploader({
                        trigger: $(this),
                        name: 'picture',
                        action: $(this).data('url'),
                        data: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
                        accept: 'image/*',
                        error: function(file) {
                            Notify.danger('上传网站LOGO失败，请重试！')
                        },
                        success: function(response) {
                            self.prev('input').val(response.url);
                            Notify.success('上传图片成功！');
                        }
                    });
                });

                $('[name=picture]').length > 0 && $('[name=picture]').css("height", 30);
            },
            onAddBtn: function() {
                
            },
            onChangeUpdateBtn: function() {

            }
        });

        new editForm({
            'element': '#block-edit-form'
        });
    };

});