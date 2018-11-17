define(function(require, exports, module) {
  var Notify = require('common/bootstrap-notify');
  var Widget = require('widget');
  var EduWebUploader  = require('edusoho.webuploader');
  require('webuploader');
  require('jquery.sortable');
  require('colorpicker');
  exports.run = function() {


    if( $('.poster-btn').length>0 ){
      var selector = $('.poster-btn');
      initFirstTab(selector);
      bindSortPoster();
    }

    if($('.colorpicker-input').length>0 ){
      $('.colorpicker-input').colorpicker();
    }

    $('#btn-tabs .btn').click(function(){
      $(this).removeClass('btn-default').addClass('btn-primary')
        .siblings('.btn-primary').removeClass('btn-primary').addClass('btn-default');
    });

    var editForm = Widget.extend({
      uploaders: [],
      events: {
        'click .js-add-btn': 'onClickAddBtn',
        'click .js-remove-btn': 'onClickRemoveBtn',
        'click a.js-title-label': 'onClickTitleLabel',
        'click .js-img-preview': 'onClickPicPreview',
        'change .js-label-input': 'onChangeLabel'
      },

      setup: function() {
        //this._bindImgPreview(this.element);
        this._bindUploader(this.element);
        this._initForm();
        this._bindCollapseEvent(this.element);
        this._bindSortable(this.element);
      },
      _initForm: function() {
        $form = this.element;

        $form.data('serialize', $form.serialize());
        $(window).on('beforeunload',function(){
          if ($form.serialize() != $form.data('serialize')) {
            return Translator.trans('admin.block.not_saved_data_hint');
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
          Notify.danger(Translator.trans('admin.block.add_max_num_hint',{panelGroup:$panelGroup.data('count')}));
        } else {
          $model = $($panels[0]).clone();
          $model.find('input').attr('value', '').val('');
          $model.find('textarea').attr('html', '');
          $model.find('.title-label').html('');
          $model.find('.js-img-preview').attr('href', '');
          var headingId = new Date().getTime() + '-heading';
          $model.find('.panel-heading').attr('id', headingId);
          var collapseId = new Date().getTime() + '-collapse';
          $model.find('.panel-collapse').attr('aria-labelledby', headingId).attr('id', collapseId);
          $model.find('[data-toggle=collapse]').attr('aria-expanded', false).attr('href', '#'+collapseId).attr('aria-controls', collapseId);
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
        if (confirm(Translator.trans('admin.block.delete_tip'))) {
          var $target = $(e.currentTarget);
          var $panelGroup = $target.closest('.panel-group');
          var $parent = $target.closest('.panel.panel-default');
          var $panels = $panelGroup.children('.panel.panel-default');
          if ($panels.length == 1) {
            Notify.danger(Translator.trans('admin.block.delete_min_num_hint'));
          } else {
            $parent.remove();
            this.refreshIndex($panelGroup);
          }
        }
        e.stopPropagation();
      },
      onClickTitleLabel: function(e) {
        var $target = $(e.currentTarget);
        if (!$target.data('noLink')) {
          e.stopPropagation();
        }
      },
      onClickPicPreview: function(e) {
        var $target = $(e.currentTarget);
        e.stopPropagation();
      },
      onChangeLabel: function(e) {
        var $target = $(e.currentTarget);
        $target.closest('.panel.panel-default').find('.js-title-label').html($target.val());
      },
      refreshIndex: function($panelGroup) {
        this._destoryUploader(this.element);
        $prefixCode = $panelGroup.data('prefix');
        $panels = $panelGroup.children('.panel.panel-default');
        $panels.each(function(index, object){
          $(this).find('input[type=text]').each(function(element){
            $(this).attr('value', $(this).val());
          });
          $(this).find('input[type=radio]').each(function(element){
            if ($(this).prop('checked')) {
              $(this).attr('checked', 'checked');
            }
          });

          $(this).find('.webuploader-container').html(Translator.trans('uploader.title'));
          var replace = $(this)[0].outerHTML.replace(/\bdata\[.*?\]\[.*?\]/g, $prefixCode + '[' + index + ']');
          $(this).replaceWith(replace);
        });

        this._bindUploader($panelGroup);
        this._bindCollapseEvent($panelGroup);
      },
      _bindImgPreview: function($element) {
        $element.find('.js-img-preview').colorbox({rel:'group1', photo:true, current:'{current} / {total}', title:function() {
          return $(this).data('title');
        }});
      },
      _bindUploader: function($element) {
        var thiz = this;
        $element.find('.img-upload').each(function(){
          var self = $(this);
          var uploader = WebUploader.create({
            swf: require.resolve('webuploader').match(/[^?#]*\//)[0] + 'Uploader.swf',
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
            Notify.info(Translator.trans('admin.block.uploading_hint'), 0);
            uploader.upload();
          });

          uploader.on( 'uploadSuccess', function( file, response ) {
            self.closest('.form-group').find('input[data-role=img-url]').val(response.url);
            Notify.success(Translator.trans('admin.block.upload_success_hint'), 1);
          });

          uploader.on( 'uploadError', function( file, response ) {
            Notify.danger(Translator.trans('admin.block.upload_failed_hint'));
          });

          var id =$(this).attr('id');
          thiz.uploaders[id] = uploader;
        });

      },
      _bindCollapseEvent: function($element) {
        $element.find('[data-role=collapse]').each(function(){
          $(this).on('shown.bs.collapse', function(e){
            $(e.target).siblings('.panel-heading').find('.js-expand-icon').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
            $(e.target).find('.webuploader-container div:eq(1)').css({width:46, height:30});
          });
          $(this).on('hidden.bs.collapse', function(e){
            $(e.target).siblings('.panel-heading').find('.js-expand-icon').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
            $(e.target).find('.webuploader-container div:eq(1)').css({width:1, height:1});
          });
        });

      },
      _bindSortable: function($element)
      {
        var self = this;
        $element.find('.panel-group').each(function(){
          var $group = $(this);
          $(this).sortable({
            itemSelector: '.panel.panel-default',
            handle: '.js-move-seq',
            serialize: function(parent, children, isContainer) {
              return isContainer ? children : parent.attr('id');
            },
            onDrop: function ($item, container, _super, event) {
              $item.removeClass('dragged').removeAttr('style');
              $('body').removeClass('dragging');
              self.refreshIndex($group);
            }
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

  function initFirstTab(selector){
    var href =selector.attr('href');
    var id = href.substr(1,href.length-1);
    var imgSelf = $('#'+id).find('.img-mode-upload');
    var htmlSelf = $('#'+id).find('.html-mode-upload');

    bindImgUpLoader(imgSelf);
    bindHtmlUpLoader(htmlSelf);
  }

  $('#block-edit-form').on('click', '.imgMode', function(){
    $(this).parent().parent().siblings('.edit-mode-html').css('display','none');
    $(this).parent().parent().siblings('.edit-mode-img').removeAttr('style');
    $(this).parent().siblings().find('.htmlMode').removeAttr('checked');
    $(this).parent().siblings('.mode-value').val('img');
    var self = $(this).parent().parent().siblings('.edit-mode-img').find('.img-mode-upload');
    bindImgUpLoader(self);
  });

  $('#block-edit-form').on('click', '.htmlMode', function () {
    $(this).parent().parent().siblings('.edit-mode-img').css('display','none');
    $(this).parent().parent().siblings('.edit-mode-html').removeAttr('style');
    $(this).parent().siblings().find('.imgMode').removeAttr('checked');
    $(this).parent().siblings('.mode-value').val('html');
    var self = $(this).parent().parent().siblings('.edit-mode-html').find('.html-mode-upload');
    bindHtmlUpLoader(self);
  });

  $('#block-edit-form').on('click', '.layout-input', function () {
    $(this).parent().siblings().find('.layout-input').removeAttr('checked');
    $(this).parent().siblings('.layout-value').val($(this).val());
  });

  $('#block-edit-form').on('click', '.status-input', function () {
    $(this).parent().siblings().find('.status-input').removeAttr('checked');
    $(this).parent().siblings('.status-value').val($(this).val());
  });

  $('#btn-tabs').on('click', '.poster-btn', function(){
    var href = $(this).attr('href');
    var id = href.substr(1,href.length-1);
    var imgSelf = $('#'+id).find('.img-mode-upload');
    var htmlSelf = $('#'+id).find('.html-mode-upload');
    bindImgUpLoader(imgSelf);
    bindHtmlUpLoader(htmlSelf);
  });

  function bindImgUpLoader(self){
    var uploader = new EduWebUploader({
      element : self,
      options: {compress: false}
    });

    uploader.on('uploadSuccess', function(file, response ) {
      self.closest('.form-group').find('.img-mrl').html(response.url);
      self.closest('.form-group').find('.img-mtl').attr('src',response.url);
      self.closest('.form-group').find('.img-value').val(response.url);
      Notify.success(Translator.trans('admin.block.upload_success_hint'), 1);
    });
  }

  function bindHtmlUpLoader(self){
    var uploader = new EduWebUploader({
      element : self,
	          options: {compress: false}
    });

    uploader.on('uploadSuccess', function(file, response ) {
      var html = self.closest('.edit-mode-html').find('.html-mrl').append('<p>' + response.url + '</p>');
      Notify.success(Translator.trans('admin.block.upload_success_hint'), 1);
    });
  }

  function bindSortPoster(){
    var $group = $('#btn-tabs');
    var adjustment;
    $('#btn-tabs').sortable({
      handle: '.js-move-icon',
      itemSelector : '.poster-table',
      placeholder: '<li class="poster-table poster-placehoder"></li>',
      onDrop: function ($item, container, _super, event) {
        $item.removeClass('dragged').removeAttr('style');
        $('body').removeClass('dragging');
        $group.children('.poster-table').each(function(index, object){
          var href = $(this).find('.poster-btn').attr('href');
          var id = href.substr(1,href.length-1);
          $('#' + id).children('div').each(function(){
            $(this).find('input[type=text]').each(function(element){
              $(this).attr('value', $(this).val());
            });
  
            $(this).find('input[type=radio]').each(function(){
              if ($(this).prop('checked')) {
                $(this).attr('checked', 'checked');
              }
            });
  
            var replace = $(this)[0].outerHTML.replace(/\bdata\[.*?\]\[.*?\]/g,   'data[posters][' + index + ']');
            $(this).replaceWith(replace);
          });
          $(this).find('.poster-btn').html('<span class="js-move-icon mrm"><i class="es-icon es-icon-yidong"></i></span><span class="mlm">'+Translator.trans('admin.block.poster',{index:index+1})+'</span>');
          $(this).find('input[type=hidden]').val(Translator.trans('admin.block.poster',{index:index+1}));
          var nameReplace = $(this)[0].outerHTML.replace(/\bdata\[.*?\]\[.*?\]/g,   'data[posters][' + index + ']');
          $(this).replaceWith(nameReplace);
  
        });
        selectBtn = $item.find('.poster-btn');
        initFirstTab(selectBtn);
        $('.colorpicker-input').colorpicker();
      },
      onDragStart: function(item, container, _super) {
        var offset = item.offset(),
          pointer = container.rootGroup.pointer;
        adjustment = {
          left: pointer.left - offset.left,
          top: pointer.top - offset.top
        };
        _super(item, container);
      },
      onDrag: function(item, position) {
        item.css({
          left: position.left - adjustment.left,
          top: position.top - adjustment.top
        });
      },
    });
  
  }
});