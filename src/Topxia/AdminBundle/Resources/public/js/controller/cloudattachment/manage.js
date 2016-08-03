define(function(require, exports, module) {
    require("jquery.bootstrap-datetimepicker");
    var Widget = require('widget');
    require('jquery.select2-css');
    require('jquery.select2');
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
                'click .nav-tabs .js-useType-btn': 'onClickNav',
                'click .targetType-tabs .js-targetType-btn': 'onClickTargetType',
                'click .pagination li': 'onClickPagination',
                'click .js-reconvert-btn': 'onClickReconvertBtn',
                'click .js-search-type option': 'onClickSearchTypeBtn',
                'click .js-refresh-btn': 'onClickRefreshBtn',
                'change .js-process-status-select': 'onClickProcessStatusBtn',
                'change .js-use-status-select': 'onClickUseStatusBtn',
                'click .js-manage-batch-btn': 'onClickManageBtn',
                'click .js-batch-delete-btn': 'onClickDeleteBatchBtn',
                'click .js-delete-btn': 'onClickDeleteBtn',
            },
            setup: function() {
                this.set('model', 'normal');
                this.set('renderUrl', this.element.find('#attachment-table').data('url'));
                this.renderTable();
                this._initHeader();
            },
            onClickNav: function(event) {
                var $target = $(event.currentTarget);
                $target.closest('.nav').find('.active').removeClass('active');
                $target.addClass('active');
                $target.closest('.nav').find('[name=useType]').val($target.data('value'));
                this.renderTable();
                event.preventDefault();
            },
            onClickTargetType: function(event) {
                var $target = $(event.currentTarget);
                $target.closest('.nav').find('.active').removeClass('active');
                $target.addClass('active');
                $target.closest('.nav').find('[name=targetType]').val($target.data('value'));
                this.renderTable();
                event.preventDefault();
            },
            onClickPagination: function(event) {
                var $target = $(event.currentTarget);
                this.element.find('.js-page').val($target.data('page'));
                this.renderTable(true);
                event.preventDefault();
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
                    Notify.success('重新转码成功!');
                    $target.parents('tr').replaceWith(response);
                }).fail(function() {
                    Notify.danger('重新转码失败!');
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
                    Notify.success('重新转码成功!');
                    self.renderTable();
                }).fail(function() {
                    Notify.danger('重新转码失败!');
                }).always(function() {
                    $target.button('reset');
                });
            },
            onClickSearchTypeBtn: function(event) {
                var self = this;
                var $target = $(event.currentTarget);
                $("#search-type").val($target.data("value"));
            },
            onClickProcessStatusBtn: function(event) {
                this.renderTable();
            },
            onClickUseStatusBtn: function(event) {
                this.renderTable();
            },
            submitForm: function(event) {
                this.renderTable();
                event.preventDefault();
            },
            renderTable: function(isPaginator) {
                isPaginator || this._resetPage();
                var self = this;
                var $table = this.element.find('#attachment-table');
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
                        $(self.element).find('[data-role=batch-item]').show();
                        $("[data-role=batch-select]").attr("checked", false);
                    }
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
                    $('#attachment-table').find('.batch-item').show();
                    $target.html('完成管理');
                } else {
                    this.set('model', 'normal');
                    var self = this;

                    $target.siblings('.btn').hide();
                    $target.siblings('[data-role=batch-manage]').hide();
                    $('#attachment-table').find('.batch-item').hide();
                    $target.html('批量管理');
                }
            },
            onClickDeleteBatchBtn: function(event) {
                var self = this;
                var $target = $(event.currentTarget);
                var ids = [];
                $('#attachment-form').find('[data-role=batch-item]:checked').each(function() {
                    ids.push(this.value);
                });
                if (ids == "") {
                    Notify.danger('请先选择你要删除的资源!');
                    return;
                }

                $('#modal').html('');
                $('#modal').load($target.data('url'), {
                    ids: ids
                });
                $('#modal').modal('show');

            },
            _loading: function() {
                var loading = '<tr><td class="empty" colspan="10" style="color:#999;padding:80px;">正在加载，请等待......</td></tr>';
                var $table = this.element.find('#attachment-table');
                $table.find('tbody').html(loading);
            },
            _loaded_error: function() {
                var loading = '<tr><td class="empty" colspan="10" style="color:#999;padding:80px;">Opps,出错了......</td></tr>';
                var $table = this.element.find('#attachment-table');
                $table.find('tbody').html(loading);
            },
            _resetPage: function() {
                this.element.find('.js-page').val(1);
            },
            _initHeader: function() {
                //init timepicker
                var self = this;
                $("#startDate").datetimepicker({
                    autoclose: true,
                    language: 'zh-CN',
                }).on('changeDate', function() {
                    $("#endDate").datetimepicker('setStartDate', $("#startDate").val().substring(0, 16));
                    self.renderTable();
                });

                $("#startDate").datetimepicker('setEndDate', $("#endDate").val().substring(0, 16));

                $("#endDate").datetimepicker({
                    autoclose: true,
                    language: 'zh-CN',
                }).on('changeDate', function() {
                    $("#startDate").datetimepicker('setEndDate', $("#endDate").val().substring(0, 16));
                    self.renderTable();
                });

                $("#endDate").datetimepicker('setStartDate', $("#startDate").val().substring(0, 16));
            }
        });

        window.materialWidget = new MaterialWidget({
            element: '#attachment-form'
        });

        $('#modal').on('click', '.file-delete-form-btn', function(event) {

            var $form = $('#file-delete-form');

            $(this).button('loading').addClass('disabled');
            $.post($form.attr('action'), $form.serialize(), function(data) {
                if (data) {
                    $('#modal').modal('hide');
                    Notify.success('删除资源成功');
                    materialWidget.renderTable(true);
                    $("input[name = 'batch-select']").attr("checked", false);
                }
                $('#attachment-form').find('[data-role=batch-item]').show();
                $('#attachment-form').find('[data-role=batch-select]').attr("checked", false);
            });
        });

        var $panel = $('#attachment-form');
        require('../../../../topxiaweb/js/util/batch-select')($panel);

    }

});