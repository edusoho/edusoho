define(function(require, exports, module) {
    require("jquery.bootstrap-datetimepicker");
    var Notify = require('common/bootstrap-notify');
    var Widget = require('widget');
    require('jquery.select2-css');
    require('jquery.select2');
    require('jquery.colorbox');
    var DetailWidget = require('materiallibbundle/controller/web/detail');

    exports.run = function() {
        var MaterialWidget = Widget.extend({
            attrs: {
                renderUrl: ''
            },
            events: {
                'submit': 'submitForm',
                'click .nav.nav-tabs li': 'onClickNav',
                'click .pagination li': 'onClickPagination',
                'click .tags-container .label': 'onClickTag',
                'click .js-detail-btn': 'onClickDetailBtn',
                'click .js-delete-btn': 'onClickDeleteBtn',
                'click .js-download-btn': 'onClickDownloadBtn',
                'click .js-reconvert-btn': 'onClickReconvertBtn',
                'click .js-source-btn': 'onClickSourseBtn',
                'click .op-li div.op-btn': 'onClickOperationBtn'
            },
            setup: function() {
                this.set('renderUrl', $('#material-item-list').data('url'));
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
                var $container = $target.closest('.tags-container');
                $container.find('.label-info').removeClass('label-info').addClass('label-default');
                $target.addClass('label-info').removeClass('label-default');
                $container.find('[name=tags]').val($target.html());
                this.renderTable();
            },
            onClickDetailBtn: function(event)
            {
                if (!this.DetailBtnActive) {
                    var self = this;
                    var $target = $(event.currentTarget);
                    this.DetailBtnActive = true;
                    $.ajax({
                        type:'GET',
                        url:$target.data('url'),
                    }).done(function(resp){
                        self.element.hide();
                        self.element.prev().hide();
                        self.element.parent().append(resp);
                        new DetailWidget({
                            element:'#material-detail',
                            callback: function() {
                                var $form = $('#material-search-form');
                                $form.show();
                                $form.prev().show();
                                window.materialWidget.renderTable();
                            }
                        });
                    }).fail(function(){
                        Notify.danger('Opps,出错了!');
                    }).always(function() {
                        self.DetailBtnActive = false;
                    });
                } 
                
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
                        self.renderTable();
                    }).fail(function(){
                        Notify.danger('删除失败!');
                    });
                }
            },
            onClickDownloadBtn: function(event) {
                var $target = $(event.currentTarget);
                window.open($target.data('url'));
            },
            onClickReconvertBtn: function(event)
            {
                var self = this;
                var $target = $(event.currentTarget);
                $target.button('loading');
                $.ajax({
                    type:'POST',
                    url:$target.data('url'),
                }).done(function(){
                    Notify.success('重新转码成功!');
                    var html = '<span class="label label-info">等待转码</span>';
                    $target.closest('td').html(html);
                }).fail(function(){
                    Notify.danger('重新转码失败!');
                }).always(function(){
                    $target.button('reset');
                });
            },

            onClickSourseBtn: function(event)
            {
                var $target = $(event.currentTarget);
                $target.parent().find('button.active').removeClass('active');
                $target.addClass('active');
                $target.parent().find("[name=sourceFrom]").val($target.data('value'));
                this.renderTable();
            },
            onClickOperationBtn: function(event)
            {
                var self = this;
                var $target = $(event.currentTarget);
                $.get($target.data('url'),function(data){
                    if(data){
                        $target.find('i').addClass("material-collection");
                    } else {
                        $target.find('i').removeClass("material-collection");
                    }
                });
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
                var $table = $('#material-item-list');
                this._loading();
                $.ajax({
                    type:'GET',
                    url:this.get('renderUrl'),
                    data:this.element.serialize()
                }).done(function(resp){
                    $table.html(resp);
                    var $temp = $table.find('.js-paginator');
                    self.element.find('[data-role=paginator]').html($temp.html());
                }).fail(function(){
                    self._loaded_error();
                });
            },
            _loading: function()
            {
                var loading = '<div class="empty" colspan="10" style="color:#999;padding:80px;">正在搜索，请等待......</div>';
                var $table = $('#material-item-list');
                $table.html(loading);
            },
            _loaded_error: function()
            {
                var loading = '<div class="empty" colspan="10" style="color:#999;padding:80px;">Opps,出错了......</div>';
                var $table = $('#material-item-list');
                $table.html(loading);
            },
            _resetPage: function()
            {
                this.element.find('.js-page').val(1);
            },
            _initSelect2: function()
            {
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
                    width: 600,
                    multiple: true,
                    placeholder: "请输入标签",
                    multiple: true,
                    createSearchChoice: function() {
                        return null;
                    },
                    maximumSelectionSize: 20
                });

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
                $("#startDate").datetimepicker({
                    autoclose: true,
                }).on('changeDate',function(){
                    $("#endDate").datetimepicker('setStartDate',$("#startDate").val().substring(0,16));
                });

                $("#startDate").datetimepicker('setEndDate',$("#endDate").val().substring(0,16));

                $("#endDate").datetimepicker({
                    autoclose: true,
                }).on('changeDate',function(){

                    $("#startDate").datetimepicker('setEndDate',$("#endDate").val().substring(0,16));
                });

                $("#endDate").datetimepicker('setStartDate',$("#startDate").val().substring(0,16));
            }
        });

        window.materialWidget = new MaterialWidget({
            element: '#material-search-form'
        });
    }

});