define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var Widget = require('widget');
    var Uploader = require('webuploader');
    exports.run = function() {
        var editForm = Widget.extend({
            events: {
                'click .js-add-btn': 'onClickAddBtn',
                'click .js-remove-btn': 'onClickRemoveBtn',
            },

            setup: function() {
                this._bindUploader(this.element);                
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
                    $model = $($panels[0]).clone();
                    $model.find('input').attr('value', '');
                    $model.find('textarea').attr('html', '');
                    $model.find('.title-label').html('');
                    $headingId = new Date().getTime() + '-heading';
                    $model.find('.panel-heading').attr('id', $headingId);
                    $collapseId = new Date().getTime()+ '-collapse';
                    $model.find('.panel-collapse').attr('aria-labelledby', $headingId).attr('id', $collapseId);
                    $model.find('a[data-toggle=collapse]').attr('aria-expanded', false).attr('href', "#"+$collapseId).attr('aria-controls', $collapseId);
                    $model.find('input[data-role=radio-yes]').attr('checked', false);
                    $model.find('input[data-role=radio-no]').attr('checked', true);
                    this._bindUploader($model); 
                    $panelGroup.append($model);
                    this.refreshIndex($panelGroup);
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
                        this.refreshIndex($panelGroup);
                    }
                }
                
            },
            refreshIndex: function($panelGroup) {
                $prefixCode = $panelGroup.data('code');
                $panels = $panelGroup.children('.panel.panel-default');
                $panels.each(function(index, object){
                    var $replace = $(this)[0].outerHTML.replace(/\bdata\[.*?\]\[.*?\]/g, $prefixCode + "[" + index + "]");
                    $(this).replaceWith($replace);
                });
            },
            _bindUploader: function($element) {
               $element.find('.img-upload').each(function(){
                   var self = $(this);
                   var uploader = WebUploader.create({
                       swf: require.resolve("webuploader").match(/[^?#]*\//)[0] + "Uploader.swf",
                       server: $(this).data('uploadUrl'),
                       pick: '#'+$(this).attr('id'),
                       formData: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
                       accept: {
                           title: 'Images',
                           extensions: 'gif,jpg,jpeg,png',
                           mimeTypes: 'image/*'
                       }
                    });

                   uploader.on( 'uploadSuccess', function( file, response ) {
                       self.closest('.form-group').find('input').val(response.url);
                       Notify.success('上传成功！', 1);
                   });

                   uploader.on( 'uploadError', function( file, response ) {
                       Notify.danger('上传失败，请重试！');
                   });
                   
               });
            }
        });

        new editForm({
            'element': '#block-edit-form'
        });
    };

});