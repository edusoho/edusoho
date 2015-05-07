define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var Widget = require('widget');
    require('webuploader');
    exports.run = function() {
        var editForm = Widget.extend({
            uploaders: [],
            events: {
                'click .js-add-btn': 'onClickAddBtn',
                'click .js-remove-btn': 'onClickRemoveBtn',
                'click .js-title-label': 'onClickPreviewPic',
                'change .js-label-input': 'onChangeLabel'
            },

            setup: function() {
                this._bindUploader(this.element);                
                this._initForm();
                this._bindCollapseEvent(this.element);
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
                    Notify.danger('最多只能添加' + $panelGroup.data('count') + '个!');
                } else {
                    $model = $($panels[0]).clone();
                    $model.find('input').attr('value', '');
                    $model.find('textarea').attr('html', '');
                    $model.find('.title-label').html('');
                    var headingId = new Date().getTime() + '-heading';
                    $model.find('.panel-heading').attr('id', headingId);
                    var collapseId = new Date().getTime() + '-collapse';
                    $model.find('.panel-collapse').attr('aria-labelledby', headingId).attr('id', collapseId);
                    $model.find('a[data-toggle=collapse]').attr('aria-expanded', false).attr('href', "#"+collapseId).attr('aria-controls', collapseId);
                    $model.find('input[data-role=radio-yes]').attr('checked', false);
                    $model.find('input[data-role=radio-no]').attr('checked', true);
                    var code = $panelGroup.data('code');
                    var uploadId = new Date().getTime();
                    $model.find('.webuploader-container').attr('id',  'item-' + code + 'uploadId-' + (uploadId));
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
                        Notify.danger("必须要有一个!");
                    } else {
                        $parent.remove();
                        this.refreshIndex($panelGroup);
                    }
                }
                e.stopPropagation();
            },
            onClickPreviewPic: function(e) {
                var $target = $(e.currentTarget);

                if ($target.data('url')) {
                    $.get($target.data('url'), function(html){
                        if (html) {
                            $('#modal').html(html);
                            $('#modal').modal('show');
                        }
                    });
                    e.stopPropagation();
                } 
                
            },
            onChangeLabel: function(e) {
                var $target = $(e.currentTarget);
                console.log($target.closest('.panel.panel-default').find('.js-title-label').html($target.val()));
            },
            refreshIndex: function($panelGroup) {
                this._destoryUploader(this.element);
                $prefixCode = $panelGroup.data('prefix');
                $panels = $panelGroup.children('.panel.panel-default');
                $panels.each(function(index, object){
                    $(this).find('.webuploader-container').html('上传');
                    var replace = $(this)[0].outerHTML.replace(/\bdata\[.*?\]\[.*?\]/g, $prefixCode + "[" + index + "]");
                    $(this).replaceWith(replace);
                });

                this._bindUploader(this.element);
                this._bindCollapseEvent(this.element); 
            },
            _bindUploader: function($element) {
                var thiz = this;
               $element.find('.img-upload').each(function(){
                   var self = $(this);
                   var uploader = WebUploader.create({
                       swf: require.resolve("webuploader").match(/[^?#]*\//)[0] + "Uploader.swf",
                       server: $(this).data('url'),
                       pick: '#'+$(this).attr('id'),
                       formData: {'_csrf_token': $('meta[name=csrf-token]').attr('content') },
                       accept: {
                           title: 'Images',
                           extensions: 'gif,jpg,jpeg,png',
                           mimeTypes: 'image/*'
                       }
                    });

                    uploader.on( 'fileQueued', function( file ) {
                       Notify.info('正在上传，请稍等！', 0);
                       uploader.upload();
                    });

                    uploader.on( 'uploadSuccess', function( file, response ) {
                       self.closest('.form-group').find('input[data-role=img-url]').val(response.url);
                       Notify.success('上传成功！', 1);
                   });

                    uploader.on( 'uploadError', function( file, response ) {
                       Notify.danger('上传失败，请重试！');
                    });
                    
                    var id =$(this).attr('id');
                    thiz.uploaders[id] = uploader;
               });
            },
            _bindCollapseEvent: function($element) {
                $element.find('[data-role=collapse]').each(function(){
                    $(this).on('shown.bs.collapse', function(e){
                        $(e.target).find('.webuploader-container div:eq(1)').css({width:46, height:30});
                    });
                    $(this).on('hidden.bs.collapse', function(e){
                        $(e.target).find('.webuploader-container div:eq(1)').css({width:1, height:1});
                    });
                });
               
            },
            _destoryUploader: function($element) {
            
            }
        });

        new editForm({
            'element': '#block-edit-form'
        });
    };

});