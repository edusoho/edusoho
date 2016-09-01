define(function (require, exports, module) {
    var Widget = require('widget');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);
    require('jquery.bootstrap-datetimepicker');

    Validator.addRule(
        'gt_current_time',
        function() {
            var testStartTime = new Date($('#lesson-testpaper-start-time-field').val());
            var now = new Date();
            return testStartTime > now;
        },
        "考试结束日期不得早于当前日期"
    );

    Validator.addRule(
        'date_and_time',
        /^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29) ([0-1]{1}[0-9]{1})|(2[0-4]{1}):[0-5]{1}[0-9]{1}$/,
        '请输入正确的日期和时间,格式如XXXX-MM-DD hh:mm'
    );

    Validator.addRule(
        'arithmetic_float',
        /^[0-9]+(\.[0-9]?)?$/,
        '{{display}}必须为正数，保留一位小数'
    );

    var Testpaper = Widget.extend({

        events: {
            'change #lesson-mediaId-field': '_changeLessonMedia',
            'click [name=testMode]' : '_onSwitchTestMode',
            'click [name=doTimes]' : 'showRedoInterval'
        },

        setup : function(){
            this._init();
        },

        _init :function(){
            this.set('_$testStartTime', $('#lesson-testpaper-start-time-field'));
            this.set('_$testStartTimeDiv', $('#testpaper-start-time-div'));
            this.set('_isRealTimeTestpaper', $('#real-time-testpaper').val() == 'realTime' ? true:false);
            this.set('_testStartTime', new Date(this.get('_$testStartTime').val()));
            this.set('_$testMode', $('[name=testMode]'));

            this._initValidator();
            this._addChoiceTestValidator();
            if(!this.get('_isRealTimeTestpaper')){
                return;
            }

            this.get('_$testStartTimeDiv').show();
            var now = new Date();
            if(this.get('_testStartTime') < now){
                this.get('_$testMode').attr("disabled",true);
                this.get('_$testStartTime').attr("disabled",true);
            }
            this._addTestStartTimeValidatorItem();
            this._addTimePicker();
        },

        _sortList : function($list){
            var data = $list.sortable("serialize").get();
            $.post($list.data('sortUrl'), {ids: data}, function (response) {
                var lessonNum = chapterNum = unitNum = 0;

                $list.find('.item-lesson, .item-chapter').each(function () {
                    var $item = $(this);
                    if ($item.hasClass('item-lesson')) {
                        lessonNum++;
                        $item.find('.number').text(lessonNum);
                    } else if ($item.hasClass('item-chapter-unit')) {
                        unitNum++;
                        $item.find('.number').text(unitNum);
                    } else if ($item.hasClass('item-chapter')) {
                        chapterNum++;
                        unitNum = 0;
                        $item.find('.number').text(chapterNum);
                    }

                });
            });
        },

        _changeLessonMedia : function(){
            var getItemsTable = function(testpaperId, showSuggestHours) {
                $.post($('#lesson-mediaId-field').data('getTestpaperItems'), {testpaperId:testpaperId},function(html){
                    $("#questionItemShowTable").html(html);
                    $("#questionItemShowDiv").show();

                    if (showSuggestHours) {
                        var suggestHours = $(".suggestHoursHidden").val();
                        $("#lesson-suggest-hour-field").val(Number(suggestHours).toFixed(1));
                    }
                    
                });
            }
            
            var mediaId = $('#lesson-mediaId-field').find('option:selected').val();
            if (mediaId != '') {
                $('#lesson-title-field').val($('#lesson-mediaId-field').find('option:selected').text());
                getItemsTable(mediaId, true);
            } else {
                $('#lesson-title-field').val('');
            }
        },

        _initValidator : function(){
            var that = this;
            validator = new Validator({
                element: '#course-lesson-form',
                autoSubmit: false
            });

            validator.addItem({
                element: '#lesson-title-field',
                required: true
            });

            validator.addItem({
                element: '#lesson-mediaId-field',
                required: true,
                errormessageRequired: '请选择试卷'
            });

            validator.addItem({
                element: '#lesson-title-field',
                required: true
            });

            validator.addItem({
                element: '#lesson-suggest-hour-field',
                required: true,
                rule: 'decimal',
                errormessageRequired: '请填写建议时长'
            });

            if ($('[name="doTimes"]').val() == 0) {
                validator.addItem({
                    element: '[name="redoInterval"]',
                    required: true,
                    rule: 'arithmetic_float max{max:1000000000}',
                    errormessageMax:'时间不能超过10位'
                });
            }

            $('#lesson-suggest-hour-field').bind('blur',function(){
                var val = $(this).val();
                if (isNaN(val)) {
                    return false;
                }
                var multiple = Math.ceil(val / 0.5)*0.5;
                var temp = val > multiple ? (multiple+0.5) : multiple;
                $(this).val(temp.toFixed(1));
            })

            validator.on('formValidated', function (error, msg, $form) {
                if (error) {
                    return;
                }
                $('#course-testpaper-btn').button('submiting').addClass('disabled');

                var $panel = $('.lesson-manage-panel');
                $.post($form.attr('action'), $form.serialize(), function (html) {

                    var id = '#' + $(html).attr('id'),
                        $item = $(id);
                    var $parent = $('#' + $form.data('parentid'));
                    if ($item.length) {
                        $item.replaceWith(html);
                        Notify.success('试卷课时已保存');
                    } else {
                        $panel.find('.empty').remove();

                        if ($parent.length) {
                            var add = 0;
                            if ($parent.hasClass('item-chapter  clearfix')) {
                                $parent.nextAll().each(function () {
                                    if ($(this).hasClass('item-chapter  clearfix')) {
                                        $(this).before(html);
                                        add = 1;
                                        return false;
                                    }
                                });
                                if (add != 1) {
                                    $("#course-item-list").append(html);
                                    add = 1;
                                }

                            } else {
                                $parent.nextAll().each(function () {
                                    if ($(this).hasClass('item-chapter  clearfix'))
                                        return false;
                                    if ($(this).hasClass('item-chapter item-chapter-unit clearfix')) {
                                        $(this).before(html);
                                        add = 1;
                                        return false;
                                    }
                                });
                            }
                            if (add != 1) {
                                $("#course-item-list").append(html);
                            }
                            var $list = $("#course-item-list");
                            that._sortList($list);
                        } else {
                            $("#course-item-list").append(html);
                        }
                        Notify.success('添加试卷课时成功');
                    }
                    $(id).find('.btn-link').tooltip();
                    $form.parents('.modal').modal('hide');
                });

            });

            this.set('_validator', validator);
        },

        _onSwitchTestMode: function(event){
            var $this = $(event.currentTarget);
            if($this.val() == 'realTime'){
                this._addTestStartTimeValidatorItem();
                this.set('_isRealTimeTestpaper', true);
                this._addTimePicker();
                this._addChoiceTestValidator();
                this.get('_$testStartTimeDiv').show();
            }else {
                this.set('_isRealTimeTestpaper', false);
                this._removeTestStartTimeValidatorItem();
                this._removeTimePicker();
                this._addChoiceTestValidator();
                this.get('_$testStartTimeDiv').hide();
            }
        },

        _addTimePicker : function(){
            var that = this;
            this.get('_$testStartTime').datetimepicker({
                language:'zh-CN',
                autoclose: true,
                format: 'yyyy-mm-dd hh:ii'
            }).on('hide',function(){
                that.set('_testStartTime', new Date(that.get('_$testStartTime').val()));
                that.get('_validator').query(that.get('_$testStartTime')).execute();
            });

            $( "#modal" ).scroll(function() {
                that.get('_$testStartTime').datetimepicker('place');
            });
        },

        _addTestStartTimeValidatorItem : function(){
            if (this.get('_validator') instanceof Validator){
                this.get('_validator').addItem({
                    element: this.get('_$testStartTime'),
                    required: true,
                    rule: 'gt_current_time date_and_time',
                    display:"考试开始时间"
                });
            }
        },

        _removeTestStartTimeValidatorItem : function(){
            if (this.get('_validator') instanceof Validator){
                this.get('_validator').removeItem(this.get('_$testStartTime'));
            }
        },

        _removeTimePicker : function(){
            this.get('_$testStartTime').datetimepicker('remove');
        },

        _addChoiceTestValidator : function () {
            if(!this.get('_validator')){
                return;
            }

            this.get('_validator').removeItem('#lesson-mediaId-field');

            if(this.get('_isRealTimeTestpaper')){
                this.get('_validator').addItem({
                    element: '#lesson-mediaId-field',
                    required: true,
                    rule: 'remote',
                    errormessageRequired: '请选择试卷'
                });
            }else {
                this.get('_validator').addItem({
                    element: '#lesson-mediaId-field',
                    required: true,
                    errormessageRequired: '请选择试卷'
                });
            }

        },

        showRedoInterval: function(event) {
            var $this = $(event.currentTarget);

            if ($this.val() == 1) {
                $('#lesson-redo-interval-field').closest('.form-group').hide();
                this.get('_validator').removeItem('[name="redoInterval"]');
            } else {
                $('#lesson-redo-interval-field').closest('.form-group').show();
                this.get('_validator').addItem({
                    element: '[name="redoInterval"]',
                    required: true,
                    rule: 'arithmetic_float max{max:1000000000}',
                    errormessageMax:'时间不能超过10位'
                });
            }
        }
    });

    exports.run = function () {
        var testpaper = new Testpaper({
            element: '#course-lesson-form'
        }).render();

        $('[data-toggle="tooltip"]').tooltip();
    };
});

