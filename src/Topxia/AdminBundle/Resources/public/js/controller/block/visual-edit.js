define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var Widget = require('widget');
    var Uploader = require('upload');
    exports.run = function() {
        var editForm = Widget.extend({
            events: {
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
                            self.parents('.form-group').find('input').val(response.url);
                            Notify.success('上传图片成功！');
                        }
                    });
                });

                $('[name=picture]').length > 0 && $('[name=picture]').css("height", 30);
                
                
                this._initForm();
            },
            _initForm: function() {
                $form = this.element;
                $form.data('serialize', $form.serialize()); 
                $(window).on('beforeunload',function(){
                    if ($form.serialize() != $form.data('serialize')) {
                        return "还有没有保存的数据,是否要离开此页面?";
                    }
                });
                
                this.$('#block-save-btn').on('click', function(){
                    $form.data('serialize', $form.serialize()); 
                });
            },
            onChangeUpdateBtn: function() {

            }
        });

        new editForm({
            'element': '#block-edit-form'
        });
    };

});