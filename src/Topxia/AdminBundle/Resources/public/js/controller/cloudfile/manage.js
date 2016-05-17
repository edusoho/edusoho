define(function(require, exports, module) {
    require("jquery.bootstrap-datetimepicker");
    var Widget = require('widget');
    require('jquery.select2-css');
    require('jquery.select2');
    var DetailWidget = require('./detail');
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var MaterialWidget = Widget.extend({
            attrs: {
                model: '',
                renderUrl: ''
            },
            events: {
                'submit': 'submitForm',
                'click .nav-tabs .js-type-btn': 'onClickNav',
                'click .pagination li': 'onClickPagination',
                'click .js-tags-container .js-tag-btn': 'onClickTag',
                'click .js-detail-btn': 'onClickDetailBtn',
                'click .js-delete-btn': 'onClickDeleteBtn',
                'click .js-reconvert-btn': 'onClickReconvertBtn',
                'click .js-search-type option': 'onClickSearchTypeBtn',
                'click .js-refresh-btn': 'onClickRefreshBtn',
                'change .js-process-status-select': 'onClickProcessStatusBtn',
                'change .js-use-status-select': 'onClickUseStatusBtn',
                'click .js-manage-batch-btn': 'onClickManageBtn',
                'click .js-batch-delete-btn': 'onClickDeleteBatchBtn',
                'click .js-batch-share-btn': 'onClickShareBatchBtn',
                'click .js-batch-tag-btn': 'onClickTagBatchBtn',
            },
            setup: function() {
                this.set('model','normal');
                this.set('renderUrl', this.element.find('#materials-table').data('url'));
                this.renderTable();
                this._initHeader();
                this._initSelect2();
            },
            onClickNav: function(event)
            {
                var $target = $(event.currentTarget);
                $target.closest('.nav').find('.active').removeClass('active');
                $target.addClass('active');
                $target.closest('.nav').find('[name=type]').val($target.data('value'));
                this.renderTable();
                event.preventDefault();
            },
            onClickPagination: function(event)
            {
                var $target = $(event.currentTarget);
                this.element.find('.js-page').val($target.data('page'));
                this.renderTable(true);
                event.preventDefault();
            },
            onClickTag: function(event)
            {
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
            onClickDetailBtn: function(event)
            {
                var self = this;
                var $target = $(event.currentTarget);
                $target.button('loading');
                $.ajax({
                    type:'GET',
                    url:$target.data('url'),
                }).done(function(resp){
                    self.element.hide();
                    self.element.prev().hide();
                    self.element.parent().prev().html('资源详情');
                    self.element.parent().append(resp);
                    new DetailWidget({
                        element:'#material-detail',
                        callback: function() {
                            var $form = $('#materials-form');
                            $form.show();
                            $form.prev().show();
                            window.materialWidget.renderTable();
                        }
                    });
                }).fail(function(){
                    $target.button('reset');
                    Notify.danger('Opps,出错了!');
                });
            },
            onClickDeleteBtn: function(event)
            {
                if (confirm('真的要删除该资源吗？')) {
                    var self = this;
                    var $target = $(event.currentTarget);
                    this._loading();
                    $.ajax({
                        type:'POST',
                        url:$target.data('url'),
                    }).done(function(){
                        Notify.success('删除成功!');
                        self.renderTable(true);
                    }).fail(function(){
                        Notify.danger('删除失败!');
                    });
                }
            },
            onClickReconvertBtn: function(event)
            {
                var self = this;
                var $target = $(event.currentTarget);
                $target.button('loading');

                $.ajax({
                    type:'POST',
                    url:$target.data('url'),
                }).done(function(response){
                    Notify.success('重新转码成功!');
                    $target.parents('tr').replaceWith(response);
                }).fail(function(){
                    Notify.danger('重新转码失败!');
                }).always(function(){
                    $target.button('reset');
                });
            },
            onClickRefreshBtn: function(event)
            {
                var self = this;
                var $target = $(event.currentTarget);
                $target.button('loading');

                $.ajax({
                    type:'POST',
                    url:$target.data('url'),
                }).done(function(){
                    Notify.success('重新转码成功!');
                    self.renderTable();
                }).fail(function(){
                    Notify.danger('重新转码失败!');
                }).always(function(){
                    $target.button('reset');
                });
            },
            onClickSearchTypeBtn: function(event)
            {
                var self = this;
                var $target = $(event.currentTarget);
                $("#search-type").val($target.data("value"));
            },
            onClickProcessStatusBtn: function(event)
            {
                this.renderTable();
            },
            onClickUseStatusBtn: function(event)
            {
                this.renderTable();
            },
            submitForm: function(event)
            {
                this.renderTable();
                event.preventDefault();
            },
            renderTable: function(isPaginator)
            {
                isPaginator || this._resetPage();
                var self = this;
                var $table = this.element.find('#materials-table');
                this._loading();

                $.ajax({
                    type:'GET',
                    url:this.get('renderUrl'),
                    data:this.element.serialize()
                }).done(function(resp){
                    $table.find('tbody').html(resp);
                    var $temp = $table.find('.js-paginator');
                    self.element.find('[data-role=paginator]').html($temp.html());

                    if (self.get('model') == 'edit') {
                        $('#materials-form').find('[data-role=batch-item]').show();
                    }
                }).fail(function(){
                    self._loaded_error();
                });
            },
            onClickManageBtn: function(event)
            {
                var self = this;
                var mode = self.get('model');
                var $target = $(event.currentTarget);

                if(mode == "normal") {
                    this.set('model','edit');
                  
                    $target.siblings('.btn').show();
                    $target.siblings('[data-role=batch-manage]').show();
                    $('#materials-table').find('.batch-item').show();
                    $target.html('完成管理');
                } else {
                    this.set('model','normal');
                    var self = this;
                  
                    $target.siblings('.btn').hide();
                    $target.siblings('[data-role=batch-manage]').hide();
                    $('#materials-table').find('.batch-item').hide();
                    $target.html('批量管理');
                }
            },
            onClickDeleteBatchBtn: function(event)
            {
                if (confirm('确定要删除这些资源吗？')) {
                    var self = this;
                    var $target = $(event.currentTarget);
                    var ids = [];
                    $('#materials-form').find('[data-role=batch-item]:checked').each(function() {
                        ids.push(this.value);
                    });
                    if(ids == ""){
                        Notify.danger('请先选择你要删除的资源!');
                        return;
                    }

                    $.post($target.data('url'),{"ids":ids},function(data){
                        if(data){
                            Notify.success('删除资源成功');
                            self.renderTable(true);
                            $("input[name = 'batch-select']").attr("checked",false);
                        }
                        $('#materials-form').find('[data-role=batch-item]').show();
                        $('#materials-form').find('[data-role=batch-select]').attr("checked",false);
                    });
                }

            },
            onClickShareBatchBtn: function(event)
            {
                if (confirm('确定要分享这些资源吗？')) {
                    var self = this;
                    var $target = $(event.currentTarget);
                    var ids = [];
                    this.element.find('[data-role=batch-item]:checked').each(function() {
                        ids.push($(this).data('fileId'));
                    });
                    if(ids == ""){
                        Notify.danger('请先选择你要分享的资源!');
                        return;
                    }

                    $.post($target.data('url'),{"ids":ids},function(data){
                        if(data){
                            Notify.success('分享资源成功');
                            self.renderTable();
                        } else {
                            Notify.danger('分享资源失败');
                            self.renderTable();
                        }
                        this.element.find('[data-role=batch-item]').show();

                    });
                }
            },
            onClickTagBatchBtn: function(event)
            {
                var self = this;
                var $target = $(event.currentTarget);
                var ids = [];
                this.element.find('[data-role=batch-item]:checked').each(function() {
                    ids.push($(this).data('fileId'));
                });
                if(ids == ""){
                    Notify.danger('请先选择你要操作的资源!');
                    return;
                }

                $("#select-tag-items").val(ids);
                $("#tag-modal").modal('show');
            },
            _loading: function()
            {
                var loading = '<tr><td class="empty" colspan="10" style="color:#999;padding:80px;">正在加载，请等待......</td></tr>';
                var $table = this.element.find('#materials-table');
                $table.find('tbody').html(loading);
            },
            _loaded_error: function()
            {
                var loading = '<tr><td class="empty" colspan="10" style="color:#999;padding:80px;">Opps,出错了......</td></tr>';
                var $table = this.element.find('#materials-table');
                $table.find('tbody').html(loading);
            },
            _resetPage: function()
            {
                this.element.find('.js-page').val(1);
            },
            _initSelect2: function()
            {
                $("#js-course-search").select2({
                    placeholder: "选择课程",
                    minimumInputLength: 1,
                    ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
                        url: $("#js-course-search").data('url'),
                        dataType: 'json',
                        quietMillis: 250,
                        data: function (term, page) {
                            return {
                                q: term, // search term
                            };
                        },
                        results: function (data, page) { // parse the results into the format expected by Select2.
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
                            $.ajax($("#js-course-search").data('url')+'?courseId=' + id, {
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
                    width:300,
                    dropdownCssClass: "bigdrop", // apply css that makes the dropdown taller
                    escapeMarkup: function (m) { return m; } // we do not want to escape markup since we are displaying html in results
                });

                $("#js-user-search").select2({
                    placeholder: "选择用户",
                    minimumInputLength: 1,
                    ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
                        url: $("#js-user-search").data('url'),
                        dataType: 'json',
                        quietMillis: 250,
                        data: function (term, page) {
                            return {
                                q: term, // search term
                            };
                        },
                        results: function (data, page) { // parse the results into the format expected by Select2.
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
                            $.ajax($("#js-user-search").data('url')+'?userId=' + id, {
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
                    width:300,
                    dropdownCssClass: "bigdrop", // apply css that makes the dropdown taller
                    escapeMarkup: function (m) { return m; } // we do not want to escape markup since we are displaying html in results
                });
            },
            _initHeader: function()
            {
                //init timepicker
                var self = this;
                $("#startDate").datetimepicker({
                    autoclose: true,
                    language: 'zh-CN',
                }).on('changeDate',function(){
                    $("#endDate").datetimepicker('setStartDate',$("#startDate").val().substring(0,16));
                    self.renderTable();
                });

                $("#startDate").datetimepicker('setEndDate',$("#endDate").val().substring(0,16));

                $("#endDate").datetimepicker({
                    autoclose: true,
                    language: 'zh-CN',
                }).on('changeDate',function(){
                    $("#startDate").datetimepicker('setEndDate',$("#endDate").val().substring(0,16));
                    self.renderTable();
                });

                $("#endDate").datetimepicker('setStartDate',$("#startDate").val().substring(0,16));
            }
        });

        window.materialWidget = new MaterialWidget({
            element: '#materials-form'
        });

        var $panel = $('#materials-form');
        require('../../../../topxiaweb/js/util/batch-select')($panel);

    }

});
