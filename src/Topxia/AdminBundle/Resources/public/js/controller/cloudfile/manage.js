define(function(require, exports, module) {
  require("jquery.bootstrap-datetimepicker");
  var Widget = require('widget');
  require('jquery.select2-css');
  require('jquery.select2');
  var DetailWidget = require('./detail');
  var Notify = require('common/bootstrap-notify');
  var Validator = require('bootstrap.validator');

  exports.run = function() {

    var MaterialWidget = Widget.extend({
      attrs: {
        model: '',
        renderUrl: ''
      },
      events: {
        'submit': 'submitForm',
        'click .nav-tabs .js-type-btn': 'onClickNav',
        'click .nav-tabs .js-useType-btn': 'onClickUseTypeNav',
        'click .pagination li': 'onClickPagination',
        'click .js-tags-container .js-tag-btn': 'onClickTag',
        'click .js-detail-btn': 'onClickDetailBtn',
        'click .js-delete-btn': 'onClickDeleteBtn',
        'click .js-reconvert-btn': 'onClickReconvertBtn',
        'click .js-search-type option': 'onClickSearchTypeBtn',
        'click .js-refresh-btn': 'onClickRefreshBtn',
        'click .js-manage-batch-btn': 'onClickManageBtn',
        'click .js-batch-delete-btn': 'onClickDeleteBatchBtn',
        'click .js-batch-share-btn': 'onClickShareBatchBtn',
        'click .js-batch-tag-btn': 'onClickTagBatchBtn',
        'click .js-cd-modal': 'codeErrorTip',
        'click .js-batch-download': 'batchDownload',

      },
      setup: function() {
        this.set('model', 'normal');
        this.set('renderUrl', this.element.find('#materials-table').data('url'));
        this.renderTable();
        this._initHeader();
        this._initSelect2();
        this.initTagForm();
        
      },
      initTagForm: function(event) {
        var $form = $("#tag-form");
        if ($form.length == 0) {
          return false;
        }
        var validator = new Validator({
          element: $form,
        });

        validator.addItem({
          element: '#tags',
          required: true,
          display: Translator.trans('admin.cloud_file.tag_required_hint')
        });
      },

      downloadFile(url) {
        var iframe = document.createElement("iframe");
        iframe.style.display = "none";
        iframe.style.height = 0;
        iframe.src = url;
        document.body.appendChild(iframe);
        setTimeout(function(){
          iframe.remove();
        }, 5 * 60 * 1000);
      },

      batchDownload() {
        var self = this;
        var urls = [];
        $('#materials-table').find('[data-role=batch-item]:checked').each(function() {
          var downloadUrl = $(this).closest('.js-tr-item').find('.js-download-btn').data('url');
          urls.push(downloadUrl);
        });
        for (var i = 0;i < urls.length;i++) {
          var url = urls[i];
          self.downloadFile(url);   
        }
      },

      codeErrorTip: function() {
        $('#error-modal').on('show.bs.modal', function(event) {
          var $btn = $(event.relatedTarget);
          var title = $btn.data('title');
          var reason = $btn.data('reason');
          var solution = $btn.data('solution');
          var status = $btn.data('status');
          $('.js-error-tip').html(
           '<div class="mbl clearfix"><span class="pull-left error-label">'+ Translator.trans('material.common_table.file_name') +'：</span><span class="pull-left error-content">' + title + 
           '</span></div><div class="mbl clearfix"><span class="pull-left error-label">'+ Translator.trans('material.common_table.transcoding') +'：</span><span class="pull-left error-content">' + status + '</span></div><div class="mbl clearfix"><span class="pull-left error-label">' + Translator.trans('material.common_table.error_reason') + '：</span><span class="color-danger pull-left error-content">' + reason +
           '</span></div><div class="clearfix"><span class="pull-left error-label">'+ Translator.trans('material.common_table.solution_way') +'：</span><span class="color-info pull-left error-content">' +  solution + 
           '</span></div>')
        })
      },
      onClickUseTypeNav: function(event) {
        var $target = $(event.currentTarget);
        $target.closest('.nav').find('.active').removeClass('active');
        $target.addClass('active');
        $target.closest('.nav').find('[name=useType]').val($target.data('value'));
        this.renderTable();
        event.preventDefault();
      },
      onClickNav: function(event) {
        var $target = $(event.currentTarget);
        $target.closest('.nav').find('.active').removeClass('active');
        $target.addClass('active');
        $target.closest('.nav').find('[name=type]').val($target.data('value'));
        this.renderTable();
        event.preventDefault();
      },
      onClickPagination: function(event) {
        var $target = $(event.currentTarget);
        this.element.find('.js-page').val($target.data('page'));
        this.renderTable(true);
        event.preventDefault();
      },
      onClickTag: function(event) {
        var $target = $(event.currentTarget);
        var $container = $target.closest('.js-tags-container');
        var $prev = $container.find('.active');
        if ($target.html() == $prev.html()) {
          $target.removeClass('active');
          $container.find('[name=tags]').val('');
        } else {
          $prev.removeClass('active');
          $target.addClass('active');
          $container.find('[name=tags]').val($target.find('a').data('value'));
        }
        this.renderTable();
      },
      onClickDetailBtn: function(event) {
        var self = this;
        var $target = $(event.currentTarget);
        $target.button('loading');
        $.ajax({
          type: 'GET',
          url: $target.data('url'),
        }).done(function(resp) {
          self.element.hide();
          self.element.prev().hide();
          self.element.parent().prev().html(Translator.trans('material_lib.detail.content_title'));
          self.element.parent().append(resp);
          new DetailWidget({
            element: '#material-detail',
            callback: function() {
              var $form = $('#materials-form');
              $form.show();
              $form.prev().show();
              window.materialWidget.renderTable();
            }
          });
        }).fail(function() {
          $target.button('reset');
          Notify.danger(Translator.trans('admin.cloud_file.detail_show_error_hint') + '!');
        });
      },
      onClickDeleteBtn: function(event) {
        var self = this;
        var $target = $(event.currentTarget);
        var ids = [];

        ids.push($target.data('id'));

        $('#modal').html('');
        $('#modal').load($target.data('url'), {
          ids: ids
        });
        $('#modal').modal('show');
      },
      onClickReconvertBtn: function(event) {
        var self = this;
        var $target = $(event.currentTarget);
        $target.button('loading');

        $.ajax({
          type: 'POST',
          url: $target.data('url'),
        }).done(function(response) {
          Notify.success(Translator.trans('admin.cloud_file.re_transcode_success_hint'));
          $target.parents('tr').replaceWith(response);
        }).fail(function() {
          Notify.danger(Translator.trans('admin.cloud_file.re_transcode_fail_hint'));
        }).always(function() {
          $target.button('reset');
        });
      },
      onClickRefreshBtn: function(event) {
        var self = this;
        var $target = $(event.currentTarget);
        $target.button('loading');

        $.ajax({
          type: 'POST',
          url: $target.data('url'),
        }).done(function() {
          Notify.success(Translator, trans('admin.cloud_file.re_transcode_success_hint'));
          self.renderTable();
        }).fail(function() {
          Notify.danger(Translator.trans('admin.cloud_file.re_transcode_fail_hint'));
        }).always(function() {
          $target.button('reset');
        });
      },
      onClickSearchTypeBtn: function(event) {
        var self = this;
        var $target = $(event.currentTarget);
        $("#search-type").val($target.data("value"));
      },
      submitForm: function(event) {
        this.renderTable();
        event.preventDefault();
      },
      renderTable: function(isPaginator) {
        isPaginator || this._resetPage();
        var self = this;
        var $table = this.element.find('#materials-table');
        this._loading();

        $.ajax({
          type: 'GET',
          url: this.get('renderUrl'),
          data: this.element.serialize()
        }).done(function(resp) {
          $table.find('tbody').html(resp);
          var $temp = $table.find('.js-paginator');
          self.element.find('[data-role=paginator]').html($temp.html());

          if (self.get('model') == 'edit') {
            $('#materials-form').find('[data-role=batch-item]').show();
            $("[data-role=batch-select]").attr("checked", false);
          }
          $('.js-table-popover').popover({
            placement: 'top',
            trigger: 'click',
            html: true,
            animation: false,
            title: '<div class="clearfix">' + Translator.trans('material.common_table.transcoding_intro') + '<a class="pull-right text-sm" href="http://www.qiqiuyu.com/faq/868/detail" target="_blank"> ' + Translator.trans('material.common_table.transcoding_more') + '</a></div>',
            content: '<div class="text-sm material-table-popover"><p class="mb0"><strong>' + Translator.trans('subtitle.status.error') + '：</strong>' + Translator.trans('material.common_table.fail_error_tip') + '</p><p class="mb0"><strong>' + Translator.trans('material.common_table.fail_not_support') + '：</strong>' + Translator.trans('material.common_table.not_support_error_tip') + '</p></div>'
            }).on('mouseenter', function () {
            var _this = this;
            $(this).popover('show');
            $('.popover').on('mouseleave', function () {
                $(_this).popover('hide');
            });
          }).on('mouseleave', function () {
            var _this = this;
            setTimeout(function () {
              if (!$('.popover:hover').length) {
                $(_this).popover("hide");
              }
            }, 300);
          });
        }).fail(function() {
          self._loaded_error();
        });
      },
      onClickManageBtn: function(event) {
        var self = this;
        var mode = self.get('model');
        var $target = $(event.currentTarget);

        if (mode == "normal") {
          this.set('model', 'edit');

          $target.siblings('.btn').show();
          $target.siblings('[data-role=batch-manage]').show();
          $('#materials-table').find('.batch-item').show();
          $target.html(Translator.trans('meterial_lib.complete_manage'));
        } else {
          this.set('model', 'normal');
          var self = this;

          $target.siblings('.btn').hide();
          $target.siblings('[data-role=batch-manage]').hide();
          $('#materials-table').find('.batch-item').hide();
          $target.html(Translator.trans('meterial_lib.batch_manage'));
        }
      },
      onClickDeleteBatchBtn: function(event) {
        var self = this;
        var $target = $(event.currentTarget);
        var ids = [];
        $('#materials-form').find('[data-role=batch-item]:checked').each(function() {
          ids.push(this.value);
        });
        if (ids == "") {
          Notify.danger(Translator.trans('meterial_lib.select_resource_delete_hint') + '!');
          return;
        }

        $('#modal').html('');
        $('#modal').load($target.data('url'), {
          ids: ids
        });
        $('#modal').modal('show');

      },
      onClickShareBatchBtn: function(event) {
        if (confirm(Translator.trans('meterial_lib.confirm_share_resource_hint'))) {
          var self = this;
          var $target = $(event.currentTarget);
          var ids = [];
          this.element.find('[data-role=batch-item]:checked').each(function() {
            ids.push($(this).data('fileId'));
          });
          if (ids == "") {
            Notify.danger(Translator.trans('meterial_lib.select_share_resource_hint') + '!');
            return;
          }

          $.post($target.data('url'), {
            "ids": ids
          }, function(data) {
            if (data) {
              Notify.success(Translator.trans('meterial_lib.share_resource_success_hint'));
              self.renderTable();
            } else {
              Notify.danger(Translator.trans('meterial_lib.share_resource_erroe_hint'));
              self.renderTable();
            }
            this.element.find('[data-role=batch-item]').show();

          });
        }
      },
      onClickTagBatchBtn: function(event) {
        var self = this;
        var $target = $(event.currentTarget);
        var ids = [];
        this.element.find('[data-role=batch-item]:checked').each(function() {
          ids.push($(this).data('fileId'));
        });
        if (ids == "") {
          Notify.danger(Translator.trans('meterial_lib.select_resource_operate_hint') + '!');
          return;
        }

        $("#select-tag-items").val(ids);
        $("#tag-modal").modal('show');
      },
      _loading: function() {
        var loading = '<tr><td class="empty" colspan="10" style="color:#999;padding:80px;">' + Translator.trans('admin.cloud_file.detail_loading_hints') + '</td></tr>';
        var $table = this.element.find('#materials-table');
        $table.find('tbody').html(loading);
      },
      _loaded_error: function() {
        var loading = '<tr><td class="empty" colspan="10" style="color:#999;padding:80px;">' + Translator.trans('admin.cloud_file.detail_show_error_hint') + '......' + '</td></tr>';
        var $table = this.element.find('#materials-table');
        $table.find('tbody').html(loading);
      },
      _resetPage: function() {
        this.element.find('.js-page').val(1);
      },
      _initSelect2: function() {
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
              var results = [];
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
            var data = [];
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
          placeholder: Translator.trans('validate.tag_required_hint'),
          multiple: true,
          createSearchChoice: function() {
            return null;
          },
          maximumSelectionSize: 20
        });

        $("#js-course-search").select2({
          placeholder: Translator.trans('admin.cloud_file.select_course_placeholder'),
          minimumInputLength: 1,
          ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
            url: $("#js-course-search").data('url'),
            dataType: 'json',
            quietMillis: 250,
            data: function(term, page) {
              return {
                q: term, // search term
              };
            },
            results: function(data, page) { // parse the results into the format expected by Select2.
              var results = [];

              $.each(data, function(index, item) {

                results.push({
                  id: item.id,
                  name: item.title
                });
              });

              return {
                results: results
              };
            },
            cache: true
          },
          initSelection: function(element, callback) {
            var id = $(element).val();
            if (id !== "") {
              $.ajax($("#js-course-search").data('url') + '?courseId=' + id, {
                dataType: "json"
              }).success(function(resp) {
                var data = ({
                  id: resp.id,
                  name: resp.title
                });

                callback(data);
              });
            }
          },
          formatSelection: function(item) {
            return item.name;
          },
          formatResult: function(item) {
            return item.name;
          },
          width: 300,
          dropdownCssClass: "bigdrop", // apply css that makes the dropdown taller
          escapeMarkup: function(m) {
              return m;
            } // we do not want to escape markup since we are displaying html in results
        });

        $("#js-user-search").select2({
          placeholder: Translator.trans('admin.cloud_file.select_user_placeholder'),
          minimumInputLength: 1,
          ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
            url: $("#js-user-search").data('url'),
            dataType: 'json',
            quietMillis: 250,
            data: function(term, page) {
              return {
                q: term, // search term
              };
            },
            results: function(data, page) { // parse the results into the format expected by Select2.
              var results = [];

              $.each(data, function(index, item) {

                results.push({
                  id: item.id,
                  name: item.nickname
                });
              });

              return {
                results: results
              };
            },
            cache: true
          },
          initSelection: function(element, callback) {
            var id = $(element).val();
            if (id !== "") {
              $.ajax($("#js-user-search").data('url') + '?userId=' + id, {
                dataType: "json"
              }).success(function(resp) {
                var data = ({
                  id: resp.id,
                  name: resp.nickname
                });
                callback(data);
              });
            }
          },
          formatSelection: function(item) {
            return item.name;
          },
          formatResult: function(item) {
            return item.name;
          },
          width: 300,
          dropdownCssClass: "bigdrop", // apply css that makes the dropdown taller
          escapeMarkup: function(m) {
              return m;
            } // we do not want to escape markup since we are displaying html in results
        });
      },
      _initHeader: function() {
        //init timepicker
        var self = this;
        $("#startDate").datetimepicker({
          autoclose: true,
        }).on('changeDate', function() {
          $("#endDate").datetimepicker('setStartDate', $("#startDate").val().substring(0, 16));
        });

        $("#startDate").datetimepicker('setEndDate', $("#endDate").val().substring(0, 16));

        $("#endDate").datetimepicker({
          autoclose: true,
        }).on('changeDate', function() {
          $("#startDate").datetimepicker('setEndDate', $("#endDate").val().substring(0, 16));
        });

        $("#endDate").datetimepicker('setStartDate', $("#startDate").val().substring(0, 16));
      }
    });

    window.materialWidget = new MaterialWidget({
      element: '#materials-form'
    });

    $('#modal').on('click', '.file-delete-form-btn', function(event) {

      var $form = $('#file-delete-form');

      $(this).button('loading').addClass('disabled');
      $.post($form.attr('action'), $form.serialize(), function(data) {
        if (data) {
          $('#modal').modal('hide');
          Notify.success(Translator.trans('meterial_lib.delete_resource_success_hint'));
          materialWidget.renderTable(true);
          $("input[name = 'batch-select']").attr("checked", false);
        }
        $('#materials-form').find('[data-role=batch-item]').show();
        $('#materials-form').find('[data-role=batch-select]').attr("checked", false);
      });
    });

    var $panel = $('#materials-form');
    require('../../util/batch-select')($panel);

  }

});