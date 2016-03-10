define(function(require, exports, module) {
    require("jquery.bootstrap-datetimepicker");
    var Notify = require('common/bootstrap-notify');
    var Widget = require('widget');
    require('jquery.select2-css');
    require('jquery.select2');
    require('jquery.colorbox');

    exports.run = function() {
        var MaterialWidget = Widget.extend({
            attrs: {
                renderUrl: ''
            },
            events: {
                'submit': 'submitForm',
                'click .nav.nav-tabs li': 'onClickNav',
                'click .pagination li': 'onClickPagination',
                'click .js-detail-btn': 'onClickDetailBtn',
                'click .js-delete-btn': 'onClickDeleteBtn',
                'click .js-reconvert-btn': 'onClickReconvertBtn'
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
            onClickDetailBtn: function(event)
            {
                var self = this;
                var $target = $(event.currentTarget);
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
                            var $form = $('#materials-form');
                            $form.show();
                            $form.prev().show();
                            window.materialWidget.renderTable();
                        }
                    });
                }).fail(function(){
                    Notify.danger('Opps,出错了!');
                });
            },
            onClickDeleteBtn: function(event)
            {
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
            element: '#message-search-form'
        });
        // $('[name="search-items"]').on('click',function(){
        //     console.log($(this).data('url'));
        //     $.post($(this).data('url'), function(date){
        //         $("[name='item-material-views']").html(date);
        //     });
        // });
        // var $panel = $('#material-lib-items-panel');
        // require('../../../../topxiaweb/js/util/batch-select')($panel);

        // $(".tip").tooltip();

        //var $list = $("#material-item-list");

        // $list.on('mouseover', '.item-material', function(e) {
        //     $(".file-name-container", this).hide();
        //     $(".action-buttons-container", this).show();
        // });

        // $list.on('mouseout', '.item-material', function(e) {
        //     $(".file-name-container", this).show();
        //     $(".action-buttons-container", this).hide();
        // });

        // $list.on('click', '.delete-material-btn', function(e) {
        //     var warning = '您真的要删除该文件吗？';

        //     if ($(this).data('link-count') > 0) {
        //         warning = "该文件目前正被 " + $(this).data('link-count') + " 个地方使用。 删除文件将导致这些地方不可用。\n\n" + warning;
        //     }

        //     if (!confirm(warning)) {
        //         return;
        //     }

        //     var $btn = $(e.currentTarget);

        //     $.post($(this).data('url'), function(response) {
        //         $btn.parents('.item-material').remove();
        //         Notify.success('文件已删除！');
        //         window.location.reload();
        //     }, 'json');
        // });

        // Batch delete (there is a special logic for this page, so we're not using the common batch-delete.js)
        // $panel.on('click', '[data-role=batch-delete]', function() {
        //     var $btn = $(this);
        //     var name = $btn.data('name');

        //     var ids = [];
        //     var warning = "";

        //     $panel.find('[data-role=batch-item]:checked').each(function() {
        //         ids.push(this.value);

        //         if ($(this).data('link-count') > 0) {
        //             warning = "\t\"" + $(this).data('file-name') + "\"目前被 " + $(this).data('link-count') + " 个地方使用。 \n" + warning;
        //         }
        //     });

        //     if (warning.length > 0) {
        //         warning = "下列文件目前正被其它地方使用： \n\n" + warning + "\n删除文件将导致这些地方不可用。\n\n";
        //     }

        //     if (ids.length == 0) {
        //         Notify.danger('未选中任何' + name);
        //         return;
        //     }

        //     if (!confirm(warning + '确定要删除选中的' + ids.length + '条' + name + '吗？')) {
        //         return;
        //     }

        //     $panel.find('.btn').addClass('disabled');

        //     Notify.info('正在删除' + name + '，请稍等。', 60);

        //     $.post($btn.data('url'), {
        //         ids: ids
        //     }, function(response) {
        //         window.location.reload();
        //     });

        // });


        // $list.on('click', '.convert-file-btn', function() {
        //     $.post($(this).data('url'), function(response) {
        //         if (response.status == 'error') {
        //             alert(response.message);
        //         } else {
        //             window.location.reload();
        //         }
        //     }, 'json').fail(function() {
        //         alert('文件转换提交失败，请重试！');
        //     });
        // });

        // $('.tip').tooltip();

        // $("#modal").modal({
        //     backdrop: 'static',
        //     keyboard: false,
        //     show: false
        // });

        // $('.image-preview').colorbox({innerWidth:'70%',innerHeight:'70%',rel:'group1', photo:true, current:'{current} / {total}', title:function() {
        //     return $(this).data('fileName');
        // }});

        // asyncLoadFiles();
    }

    // function asyncLoadFiles()
    // {
    //   var fileIds = new Array();
    //     $('#material-item-list [type=checkbox]').each(function(){
    //         if(!isNaN($(this).val())){
    //             fileIds.push($(this).val());
    //         }
    //     });

    //     if(fileIds.length==0){
    //       return ;
    //     }

    //     $.get("/course/manage/file/status?ids="+fileIds.join(","),'',function(data){
    //         if(!data||data.length==0){
    //             return ;
    //         }
            
    //         for(var i=0;i<data.length;i++){
    //           var file=data[i];
    //           if(file.convertStatus=='waiting'||file.convertStatus=='doing'){
    //             $(".convertInfo"+file.id).append("<br><span class='text-warning text-sm'>正在文件格式转换</span>");
    //           }else if(file.convertStatus=='error'){
    //             $(".convertInfo"+file.id).append("<br><span class='text-danger text-sm'>文件格式转换失败</span>");
    //           }
    //         }
    //     });
    // }

});