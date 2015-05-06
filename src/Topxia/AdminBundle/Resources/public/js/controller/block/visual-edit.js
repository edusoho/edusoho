define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var Widget = require('widget');
    var Uploader = require('upload');
    exports.run = function() {
        var editForm = Widget.extend({
            events: {
                'click .js-add-btn': 'onClickAddBtn',
                'click .js-remove-btn': 'onClickRemoveBtn',
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
            onClickAddBtn: function(e) {
                var $target = $(e.currentTarget);
                var $panelGroup = $target.prev('.panel-group');
                var $panels = $panelGroup.children('.panel.panel-default');
                if ($panels.length >= $panelGroup.data('count')) {
                    alert('最多只能添加' + $panelGroup.data('count') + '个!');
                } else {
                    $model = $panels[0].clone();
                    $panelGroup.append($panels[0].outerHTML);

                }
                

            },
            onClickRemoveBtn: function(e) {
                if (confirm("你确定要删除吗?")) {
                    var $target = $(e.currentTarget);
                    var $panelGroup = $target.closest('.panel-group');
                    var $parent = $target.closest('.panel.panel-default');
                    var $panels = $panelGroup.children('.panel.panel-default');
                    if ($panels.length == 1) {
                        alert("必须要有一个!");
                    } else {
                        $parent.remove();
                    }
                }
                
            }
        });

        new editForm({
            'element': '#block-edit-form'
        });
    };

});