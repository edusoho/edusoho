import DetailWidget from './detail';
import BatchSelect from 'app/common/widget/res-batch-select';
import Select from 'app/common/input-select';

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

    new BatchSelect(this.element);
  }
  initEvent() {
    this.element.on('click', '.js-search-btn', (event) => {
      this.onClickSearchBtn(event);
    });

    this.element.on('click', '.js-cd-modal', (event) => {
      this.codeErrorTip(event);
    })

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

    this.element.on('click', '.js-share-btn', (event) => {
      this.onClickShareBtn(event);
    });

    this.element.on('click', '.js-unshare-btn', (event) => {
      this.onClickUnshareBtn(event);
    });

    this.element.on('click', '.pagination li', (event) => {
      this.onClickPagination(event);
    });
    this.element.on('click', '.js-batch-download', (event) => {
      this.batchDownload(event);
    });
  }

  downloadFile(url) {
    const iframe = document.createElement("iframe");
    iframe.style.display = "none";
    iframe.style.height = 0;
    iframe.src = url; 
    document.body.appendChild(iframe);
    setTimeout(()=>{
      iframe.remove();
    }, 5 * 60 * 1000);
  }

  batchDownload() {
    const self = this;
    let urls = [];
    $('#material-lib-items-panel').find('[data-role=batch-item]:checked').each(function() {
      const downloadUrl = $(this).closest('.js-tr-item').find('.js-download-btn').data('url');
      console.log(downloadUrl);
      urls.push(downloadUrl);
    });
    for (let i = 0;i < urls.length;i++) {
      const url = urls[i];
      self.downloadFile(url);
    }
  }
  codeErrorTip() {
    $('#cd-modal').on('show.bs.modal', function (event) {
      // do something...
      const $btn = $(event.relatedTarget);
      const title = $btn.data('title');
      const reason = $btn.data('reason');
      const solution = $btn.data('solution');
      const status = $btn.data('status');
      $('.js-error-tip').html(
        `<div class="mbl clearfix"><span class="pull-left error-label">${Translator.trans('material.common_table.file_name')}：</span><span class="pull-left error-content">${title}</span></div><div class="mbl clearfix"><span class="pull-left error-label">${Translator.trans('material.common_table.transcoding')}：</span><span class="pull-left error-content">${status}</span></div><div class="mbl clearfix"><span class="pull-left error-label">${Translator.trans('material.common_table.error_reason')}：</span><span class="cd-text-danger error-content pull-left">${reason}</span></div><div class="clearfix"><span class="pull-left error-label">${Translator.trans('material.common_table.solution_way')}：</span><span class="cd-text-info error-content pull-left">${solution}</span></div>`
      )
    })
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

  // 搜索
  onClickSearchBtn(event) {
    this.renderTable();
    event.preventDefault();
  }

  // 下拉菜单编辑
  onClickDetailBtn(event) {
    if (!this.DetailBtnActive) {
      let self = this;
      let $target = $(event.currentTarget);
      this.DetailBtnActive = true;
      $.ajax({
        type: 'GET',
        url: $target.data('url'),
      }).done(function(resp){
        self.element.hide();
        self.element.prev().hide();
        self.element.parent().prev().html(Translator.trans('material_lib.detail.content_title'));
        self.element.parent().append(resp);

        if($('.nav.nav-tabs').length > 0 && !navigator.userAgent.match(/(iPhone|iPod|Android|ios|iPad)/i)) {
          $('.nav.nav-tabs').lavaLamp();
        }

        Select('#tags', 'remote');

        new DetailWidget({
          element: $('#material-detail'),
          callback: function() {
            let $form = $('#material-search-form');
            $form.show();
            $form.prev().show();
            self.renderTable();
          }
        });
      }).fail(function() {
        cd.message({type: 'danger', message: Translator.trans('material_lib.have_no_permission_hint')});
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
    $('#modal').load($target.data('url'), { ids: ids });
    $('#modal').modal('show');
  }
  // 下拉菜单下载
  onClickDownloadBtn(event) {
    let $target = $(event.currentTarget);
    window.open($target.data('url'));
  }
  onClickSourseBtn(event) {
    let $target = $(event.currentTarget);
    $target.closest('ul').find('li.active').removeClass('active');
    $target.parent().addClass('active');
    $target.closest('ul').siblings('input[name="sourceFrom"]').val($target.parent().data('value'));

    if ($target.closest('ul').siblings('input[name="sourceFrom"]').val() == 'my') {
      this.attribute = 'mine';
      $('#myShare').removeClass('hide');
      $('.js-material-btn-group').removeClass('hide');
      $('#shareMaterials').removeClass('hide');
      $('.js-manage-batch-btn').removeClass('hide');
      $('.js-upload-file-btn').removeClass('hide');
      let mode = this.model;
      if (mode == 'edit') {
        $('#material-lib-batch-btn-bar').show();
      }
    } else {
      this.attribute = 'others';
      $('#myShare').addClass('hide');
      $('.js-material-btn-group').addClass('hide');
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
        $target.addClass('material-collection');
        cd.message({type:'success', message: Translator.trans('site.collect_cuccess_hint')});
      } else {
        $target.removeClass('material-collection');
        cd.message({type:'success', message: Translator.trans('site.uncollect_cuccess_hint')});
      }
    });
  }
  onClickManageBtn(event) {
    let self = this;
    let mode = self.model;

    if(mode == 'normal') {
      this.model = 'edit';
      let $target = $(event.currentTarget);
      $('#material-lib-batch-btn-bar').show();
      $('#material-lib-items-panel').find('[data-role=batch-item]').show();
      $('.materials-ul').addClass('batch-hidden');
      $target.html(Translator.trans('meterial_lib.complete_manage'));
    } else {
      this.model = 'normal';
      let self = this;
      let $target = $(event.currentTarget);
      $('#material-lib-batch-btn-bar').hide();
      $('#material-lib-items-panel').find('[data-role=batch-item]').hide();
      $('.materials-ul').removeClass('batch-hidden');
      $target.html(Translator.trans('meterial_lib.batch_manage'));
    }
  }
  onClickDeleteBatchBtn(event){
    let self = this;
    let $target = $(event.currentTarget);
    let ids = [];
    $('#material-lib-items-panel').find('[data-role=batch-item]:checked').each(function() {
      ids.push(this.value);
    });
    if (ids == '') {
      cd.message({type: 'danger', message: Translator.trans('meterial_lib.select_resource_delete_hint')});
      return;
    }
    $('#modal').html('');
    $('#modal').load($target.data('url'), { ids: ids });
    $('#modal').modal('show');
  }
  onClickShareBatchBtn(event) {
    cd.confirm({
      title: '共享',
      content: Translator.trans('meterial_lib.confirm_share_resource_hint'),
      okText: '确定',
      cancelText: '取消',
      className: '',
    }).on('ok', () => {
      let $target = $(event.currentTarget);
      let ids = [];
      $('#material-lib-items-panel').find('[data-role=batch-item]:checked').each(function() {
        ids.push(this.value);
      });

      this._fileShare(ids, $target.data('url'));
      $('#material-lib-items-panel').find('[data-role=batch-item]').show();
      console.log('点击确定按钮后的回调函数');
    }).on('cancel', () => {
      console.log('点击取消按钮后的回调函数');
    })
  }
  onClickTagBatchBtn(event) {
    let self = this;
    let $target = $(event.currentTarget);
    let ids = [];
    this.element.find('[data-role=batch-item]:checked').each(function() {
      ids.push(this.value);
    });
    if (ids == '') {
      cd.message({type: 'danger', message: Translator.trans('meterial_lib.select_resource_operate_hint')});
      return;
    }
    $('#select-tag-items').val(ids);
    $('#tag-modal').modal('show');
  }

  onClickShareBtn(event) {
    cd.confirm({
      title: '共享',
      content: Translator.trans('meterial_lib.confirm_share_resource_hint'),
      okText: '确定',
      cancelText: '取消',
      className: '',
    }).on('ok', () => {
      let $target = $(event.currentTarget);

      let ids = [];
      ids.push($target.data('fileId'));

      this._fileShare(ids, $target.data('url'));
      console.log('点击确定按钮后的回调函数');
    }).on('cancel', () => {
      console.log('点击取消按钮后的回调函数');
    })
  }
  onClickUnshareBtn(event) {
    cd.confirm({
      title: '取消共享',
      content: Translator.trans('meterial_lib.confirm_unshare_resource_hint'),
      okText: '确定',
      cancelText: '取消',
      className: '',
    }).on('ok', () => {
      let self = this;
      let $target = $(event.currentTarget);

      $.post($target.data('url'), function(response) {
        if (response) {
          cd.message({type: 'success', message: Translator.trans('meterial_lib.unshare_resource_success_hint')});
          self.renderTable();
        }
      });
      console.log('点击确定按钮后的回调函数');
    }).on('cancel', () => {
      console.log('点击取消按钮后的回调函数');
    })
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
      cd.message({type: 'success', message: Translator.trans('subtitle.status.success')});
      $target.parents('.materials-list').replaceWith($(response));
    }).fail(function() {
      cd.message({type: 'danger', message: Translator.trans('subtitle.status.error')});
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
      data: this.element.find(':visible,input[type="hidden"]').serialize()
    }).done(function(resp){
      $table.html(resp);
      $('.js-batch-tag-btn, .js-batch-delete-btn, .js-batch-share-btn, .js-batch-download').attr('disabled', true);
      $('[data-toggle="tooltip"]').tooltip();
      let mode = self.model;
      let attribute = self.attribute;
      if (mode == 'edit' && attribute == 'mine') {
        $('#material-lib-batch-bar').show();
        $('#material-lib-items-panel').find('[data-role=batch-item]').show();
        $('[data-role=batch-select]').attr('checked',false);
      } else if (mode == 'normal') {
        $('#material-lib-batch-bar').hide();
      }
      let $temp = $table.find('.js-paginator');
      self.element.find('[data-role=paginator]').html($temp.html());
    }).fail(function(){
      self._loaded_error();
    });
  }
  _loading() {
    let loading = '<div class="empty" colspan="10" style="color:#999;padding:80px;">' + Translator.trans('site.loading') + '</div>';
    let $table = $('#material-item-list');
    $table.html(loading);
  }
  _loaded_error() {
    let loading = '<div class="empty" colspan="10" style="color:#999;padding:80px;">' + Translator.trans('site.loading_error') + '</div>';
    let $table = $('#material-item-list');
    $table.html(loading);
  }
  _resetPage() {
    this.element.find('.js-page').val(1);
  }
  _fileShare(ids, url) {
    let self = this;
    if (ids == '') {
      cd.message({type: 'danger', message: Translator.trans('meterial_lib.select_share_resource_hint')});
      return;
    }
    $.post(url, { 'ids': ids }, function(data) {
      if (data) {
        cd.message({type: 'success', message: Translator.trans('meterial_lib.share_resource_success_hint')});
        self.renderTable();
      } else {
        cd.message({type: 'danger', message: Translator.trans('meterial_lib.share_resource_erroe_hint')});
        self.renderTable();
      }
    });
  }
  _initHeader() {
    //init timepicker
    let self = this;
    $('#startDate').datetimepicker({
      autoclose: true,
      language: document.documentElement.lang,
    }).on('changeDate', function() {
      $('#endDate').datetimepicker('setStartDate', $('#startDate').val().substring(0, 16));
      //self.renderTable();
    });

    $('#startDate').datetimepicker('setEndDate', $('#endDate').val().substring(0, 16));

    $('#endDate').datetimepicker({
      autoclose: true,
      language: document.documentElement.lang,
    }).on('changeDate', function() {

      $('#startDate').datetimepicker('setEndDate', $('#endDate').val().substring(0, 16));
      //self.renderTable();
    });

    $('#endDate').datetimepicker('setStartDate', $('#startDate').val().substring(0, 16));
  }
  _initSelect2() {
    Select('#modal-tags', 'remote');
  }
  initTagForm(event) {
    let $form = $('#tag-form');
    let validator = $form.validate({
      rules: {
        tags: {
          required: true,
        }
      },
      messages: {
        tags: {
          required: Translator.trans('course_set.manage.tag_required_hint'),
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
      cd.message({type: 'success', message: Translator.trans('meterial_lib.delete_resource_success_hint')});
      materialWidget.renderTable(true);
    }
    $('#material-lib-items-panel').find('[data-role=batch-item]').show();
    $('#material-lib-items-panel').find('[data-role=batch-select]').attr('checked',false);
  });
});
