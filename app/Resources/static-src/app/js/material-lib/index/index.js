import notify from 'common/notify';
import DetailWidget from './detail';

class MaterialWidget {
  constructor(element) {
    this.model = 'normal';
    this.renderUrl = $('#material-item-list').data('url');
    this.attribute = 'mine';
    this.element = $('#material-search-form');
    this.init();
  }
  init() {
    this.initEvent();
    this._initHeader();
    this._initSelect2();
    this.initTagForm();
    this.renderTable();
  }
  initEvent() {
    this.element.on('click', '.js-search-btn', (event) => {
      this.submitForm(event);
    });

    this.element.on('click', '.js-source-btn', (event) => {
      this.onClickSourseBtn(event);
    });

    this.element.on('click', '.js-type-btn', (event) => {
      this.onClickTabs(event);
    });

    this.element.on('click', '.js-material-tag .label', (event) => {
      this.onClickTag(event);
    });

    this.element.on('click', '.js-delete-btn', (event) => {
      this.onClickDeleteBtn(event);
    });

    this.element.on('click', '.js-download-btn', (event) => {
      this.onClickDownloadBtn(event);
    });

    this.element.on('click', '.js-collect-btn', (event) => {
      this.onClickCollectBtn(event);
    });

    this.element.on('click', '.js-manage-batch-btn', (event) => {
      this.onClickManageBtn(event);
    });

    this.element.on('click', '.js-batch-delete-btn', (event) => {
      this.onClickDeleteBatchBtn(event);
    });
   
    this.element.on('click', '.js-batch-share-btn', (event) => {
      this.onClickShareBatchBtn(event);
    });

    this.element.on('click', '.js-batch-tag-btn', (event) => {
      this.onClickTagBatchBtn(event);
    });
    
    this.element.on('click', '.js-detail-btn', (event) => {
      this.onClickDetailBtn(event);
    });

    this.element.on('click', '.js-reconvert-btn', (event) => {
      this.onClickReconvertBtn(event);
    });

    this.element.on('change', '.js-process-status-select', (event) => {
      this.onClickProcessStatusBtn(event);
    });

    this.element.on('change', '.js-use-status-select', (event) => {
      this.onClickUseStatusBtn(event);
    });

    this.element.on('click', '.js-share-btn', (event) => {
      this.onClickShareBtn(event);
    });

    this.element.on('click', '.js-unshare-btn', (event) => {
      this.onClickUnshareBtn(event);
    });

    this.element.on('click', '.pagination li', (event) => {
      this.onClickPagination(event);
    });
  
  }
  submitForm(event) {
    this.renderTable();
    event.preventDefault();
  }
  onClickTabs(event) {
    let $target = $(event.currentTarget);
    $target.closest('.js-material-tabs').find('.active').removeClass('active');
    $target.addClass('active');
    $target.closest('.js-material-tabs').find('[name=type]').val($target.data('value'));
    this.renderTable();
    event.preventDefault();
  }
  //标签选择
  onClickTag(event) {
    let $target = $(event.currentTarget);
    let $container = $target.closest('.js-material-tag');
    let $prev = $container.find('.label-primary');
    if ($target.html() == $prev.html()) {
      $target.removeClass('label-primary').addClass('label-default');
      $container.find('[name=tagId]').val('');
    } else {
      $prev.removeClass('label-primary').addClass('label-default');
      $target.addClass('label-primary').removeClass('label-default');
      $container.find('[name=tagId]').val($target.data('id'));
    }
    this.renderTable();
  }
  // 下拉菜单编辑(有问题，tags)
  onClickDetailBtn(event) {
    if (!this.DetailBtnActive) {
      let self = this;
      let $target = $(event.currentTarget);
      this.DetailBtnActive = true;
      $.ajax({
        type:'GET',
        url:$target.data('url'),
      }).done(function(resp){
        self.element.hide();
        self.element.prev().hide();
        self.element.parent().prev().html(Translator.trans('资源详情'));
        self.element.parent().append(resp);

        if($(".nav.nav-tabs").length > 0 && !navigator.userAgent.match(/(iPhone|iPod|Android|ios|iPad)/i)) {
          $(".nav.nav-tabs").lavaLamp();
        }

        new DetailWidget({
         element:$('#material-detail'),
         callback: function() {
          let $form = $('#material-search-form');
          $form.show();
          $form.prev().show();
          self.renderTable();
          }
        });
      }).fail(function(){
        notify('danger', Translator.trans('抱歉，您无权操作此文件'));
      }).always(function() {
        self.DetailBtnActive = false;
      });
    }
  }
  // 下拉菜单删除
  onClickDeleteBtn(event) {
    let self = this;
    let $target = $(event.currentTarget);
    let ids = [];
    ids.push($target.data('id'));
    $('#modal').html('');
    $('#modal').load($target.data('url'),{ids:ids});
    $('#modal').modal('show');
  }
  // 下拉菜单下载
  onClickDownloadBtn(event) {
    let $target = $(event.currentTarget);
    window.open($target.data('url'));
  }
  onClickSourseBtn(event) {
    let $target = $(event.currentTarget);
    $target.parent().find('li.active').removeClass('active');
    $target.parent().addClass('active');
    $target.parent().parent().siblings('input[name="sourceFrom"]').val($target.parent().data('value'));

    if ($target.parent().parent().siblings('input[name="sourceFrom"]').val() == 'my') {
      this.attribute = 'mine';
      $('#myShare').removeClass('hide');
      $('#shareMaterials').removeClass('hide');
      $('.js-manage-batch-btn').removeClass('hide');
      $('.js-upload-file-btn').removeClass('hide');
      let mode = this.model;
      if (mode == "edit") {
        $('#material-lib-batch-btn-bar').show();
      }
    } else {
      this.attribute = 'others';
      $('#myShare').addClass('hide');
      $('#shareMaterials').addClass('hide');
      $('.js-manage-batch-btn').addClass('hide');
      $('.js-upload-file-btn').addClass('hide');
      $('#material-lib-batch-btn-bar').hide();

    }
    this.renderTable();
  }
  onClickCollectBtn(event) {
    let self = this;
    let $target = $(event.currentTarget);
    $.get($target.data('url'), function(data) {
      if (data) {
        $target.addClass("material-collection");
        notify('success', Translator.trans('收藏成功'))
      } else {
        $target.removeClass("material-collection");
        notify('success', Translator.trans('取消收藏成功'))
      }
    });
  }
  onClickManageBtn(event) {
    let self = this;
    let mode = self.model;

    if(mode == "normal") {
      this.model = 'edit';
      let $target = $(event.currentTarget);
      $('#material-lib-batch-btn-bar').show();
      $('#material-lib-items-panel').find('[data-role=batch-item]').show();
      $('.materials-ul').addClass('batch-hidden');
      $target.html(Translator.trans('完成管理'));
    } else {
      this.model = 'normal';
      let self = this;
      let $target = $(event.currentTarget);
      $('#material-lib-batch-btn-bar').hide();
      $('#material-lib-items-panel').find('[data-role=batch-item]').hide();
      $('.materials-ul').removeClass('batch-hidden');
      $target.html(Translator.trans('批量管理'));
    }
  }
  onClickDeleteBatchBtn(event){
    let self = this;
    let $target = $(event.currentTarget);
    let ids = [];
    $('#material-lib-items-panel').find('[data-role=batch-item]:checked').each(function() {
      ids.push(this.value);
    });
    if (ids == "") {
      notify('danger', Translator.trans('请先选择你要删除的资源!'));
      return;
    }
    $('#modal').html('');
    $('#modal').load($target.data('url'), { ids: ids });
    $('#modal').modal('show');
  }
  onClickShareBatchBtn(event) {
    if (confirm(Translator.trans('确定要分享这些资源吗？'))) {
      let $target = $(event.currentTarget);
      let ids = [];
      $('#material-lib-items-panel').find('[data-role=batch-item]:checked').each(function() {
        ids.push(this.value);
      });

      this._fileShare(ids, $target.data('url'));
      $('#material-lib-items-panel').find('[data-role=batch-item]').show();
    }
  }
  onClickTagBatchBtn(event) {
    let self = this;
    let $target = $(event.currentTarget);
    let ids = [];
    this.element.find('[data-role=batch-item]:checked').each(function() {
      ids.push(this.value);
    });
    if (ids == '') {
      notify('danger', Translator.trans('请先选择你要操作的资源!'));
      return;
    }
    $('#select-tag-items').val(ids);
    $('#tag-modal').modal('show');
  }
  onClickProcessStatusBtn(event) {
    this.renderTable();
  }
  onClickUseStatusBtn(event) {
    this.renderTable();
  }
  onClickShareBtn(event) {
    if (confirm(Translator.trans('确定要分享这个资源吗？'))) {
      let $target = $(event.currentTarget);

      let ids = [];
      ids.push($target.data('fileId'));

      this._fileShare(ids, $target.data('url'));
    }
  }
  onClickUnshareBtn(event) {
    if (confirm(Translator.trans('确定要取消分享这个资源吗？'))) {
      let self = this;
      let $target = $(event.currentTarget);

      $.post($target.data('url'), function(response) {
        if (response) {
          notify('success', Translator.trans('取消分享资源成功'));
          self.renderTable();
        }
      })
    }
  }
  onClickPagination(event) {
    let $target = $(event.currentTarget);
    this.element.find('.js-page').val($target.data('page'));
    this.renderTable(true);
    event.preventDefault();
  }
  onClickReconvertBtn(event) {
    let self = this;
    let $target = $(event.currentTarget);
    $target.button('loading');
    $.ajax({
      type: 'POST',
      url: $target.data('url'),
    }).done(function(response) {
      notify('success', Translator.trans('重新转码成功!'));
      $target.parents(".materials-list").replaceWith($(response));
    }).fail(function() {
      notify('danger', Translator.trans('重新转码失败!'));
    }).always(function() {
      $target.button('reset');
    });
  }
  renderTable(isPaginator) {
    isPaginator || this._resetPage();
    let self = this;
    let $table = $('#material-item-list');
    this._loading();
    $.ajax({
      type: 'GET',
      url: this.renderUrl,
      data: this.element.serialize()
    }).done(function(resp){
      $table.html(resp);
      $('[data-toggle="tooltip"]').tooltip();
      let mode = self.model;
      let attribute = self.attribute;
      if (mode == 'edit' && attribute == 'mine') {
        $('#material-lib-batch-bar').show();
        $('#material-lib-items-panel').find('[data-role=batch-item]').show();
        $("[data-role=batch-select]").attr("checked",false);
      } else if (mode == 'normal') {
        $('#material-lib-batch-bar').hide();
        $('#material-lib-items-panel').find('[data-role=batch-item]').hide();
      }
      let $temp = $table.find('.js-paginator');
      self.element.find('[data-role=paginator]').html($temp.html());
    }).fail(function(){
      self._loaded_error();
    });
  }
  _loading() {
    let loading = '<div class="empty" colspan="10" style="color:#999;padding:80px;">'+Translator.trans('正在加载，请等待......')+'</div>';
    let $table = $('#material-item-list');
    $table.html(loading);
  }
  _loaded_error() {
    let loading = '<div class="empty" colspan="10" style="color:#999;padding:80px;">'+Translator.trans('Opps,出错了......')+'</div>';
    let $table = $('#material-item-list');
    $table.html(loading);
  }
  _resetPage() {
    this.element.find('.js-page').val(1);
  }
  _fileShare(ids, url) {
    let self = this;
    if (ids == "") {
      notify('danger', Translator.trans('请先选择你要分享的资源!'));
      return;
    }
    $.post(url, { "ids": ids }, function(data) {
      if (data) {
        notify('success', Translator.trans('分享资源成功'));
        self.renderTable();
      } else {
        notify('danger', Translator.trans('分享资源失败'));
        self.renderTable();
      }
    });
  }
  _initHeader() {
    //init timepicker
    let self = this;
    $('#startDate').datetimepicker({
      autoclose: true,
    }).on('changeDate', function() {
      $('#endDate').datetimepicker('setStartDate', $('#startDate').val().substring(0, 16));
      self.renderTable();
    });

    $('#startDate').datetimepicker('setEndDate', $('#endDate').val().substring(0, 16));

    $('#endDate').datetimepicker({
      autoclose: true,
    }).on('changeDate', function() {

      $('#startDate').datetimepicker('setEndDate', $('#endDate').val().substring(0, 16));
      self.renderTable();
    });

    $('#endDate').datetimepicker('setStartDate', $('#startDate').val().substring(0, 16));
  }
  _initSelect2() {
    $('#tags').select2({
     ajax: {
       url: $('#tags').data('url') + '#',
       dataType: 'json',
       quietMillis: 100,
       data: function(term, page) {
         return {
           q: term,
           page_limit: 10
         };
       },
       results: function(data) {
         let results = [];
         $.each(data, function(index, item) {
           results.push({
             id: item.name,
             name: item.name
           });
         });
         return {
           results: results
         };
       }
     },
     initSelection: function(element, callback) {
       let data = [];
       $(element.val().split(",")).each(function() {
         data.push({
           id: this,
           name: this
         });
       });
       callback(data);
     },
     formatSelection: function(item) {
       return item.name;
     },
     formatResult: function(item) {
       return item.name;
     },
     width: 400,
     multiple: true,
     placeholder: Translator.trans("请输入标签"),
     multiple: true,
     createSearchChoice: function() {
       return null;
     },
     maximumSelectionSize: 20
    });
  }
  initTagForm(event) {
    let $form = $('#tag-form');
    let validator = $form.validate({
      rules: {
        tags: {
          required: true,
        },
      }
    });
  }
}

let materialWidget = new MaterialWidget();

$('#modal').on('click','.file-delete-form-btn', function(event) {
  let $form = $('#file-delete-form');

  $(this).button('loading').addClass('disabled');
  $.post($form.attr('action'), $form.serialize(), function(data) {
    if (data) {
      $('#modal').modal('hide');
      notify('success', Translator.trans('删除资源成功'));
      materialWidget.renderTable(true);
    }
    $('#material-lib-items-panel').find('[data-role=batch-item]').show();
  });
});
