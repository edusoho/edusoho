define(function (require, exports, module) {
    require('jquery.sortable');
    var Widget = require('widget');
    var Messenger = require('../player/messenger');

    var DraggableWidget = Widget.extend({
        attrs: {
            item: '.item-lesson',
            placeholder: '.placeholder',
            _video_time: '18', //视频总时长
            editbox: '.editbox',
            scalebox: '.js-scalebox',
            timepartnum: '6',
            subject_lesson_list: '#subject-lesson-list',
            editbox_lesson_list: "#editbox-lesson-list",
            group_list: ".gruop-lesson-list",
            isDraggable: false, //拖动时阻止滑动事件响应
            initMarkerArry: [], //初始化数据
            updateSqeArry: [],
            markers_array: new Array(), //所有标记好的时间集合
            courseId: $('#lesson-dashboard').data("course-id"),
            addScale: function (markerJson, $marker, markers_array) {
                return markerJson;
            },
            mergeScale: function (markerJson, $marker, $merg_emarker, markers_array) {
                return markerJson;
            },
            updateScale: function (markerJson, $marker) {
                return markerJson;
            },
            deleteScale: function (markerJson, $marker, $marker_question, marker_questions_num, markers_array) {
                return markerJson;
            },
            updateSeq: function ($scale, markerJson) {
                return markerJson;
            }
        },
        events: {
            'mousedown .gruop-lesson-list .drag': 'itemDraggable',
            'click .lesson-list [data-role="question-remove"]': 'itemRmove',
            'click #subject-lesson-list .item-lesson': 'stopEvent',
            'mousedown .scale-blue': 'slideScale',
            'mouseenter .scale-blue': 'hoverScale',
            'mousedown .scale-blue .item-lesson': 'previewQuestion',
            'mousedown .js-question-preview': 'previewMouseDown',

        },
        setup: function () {
            this._initSortable();
            this._initeditbox(false);
            this._initMarkerArry(this.get('initMarkerArry'));
            this._lisentresize();
            this.initPlayer();
        },
        initPlayer: function () {
            var _self = this;
            var changeleft = true;
            var $editbox_list = $('#editbox-lesson-list');
            var videoHtml = $('#lesson-dashboard');
            var courseId = videoHtml.data("course-id");
            var lessonId = videoHtml.data("lesson-id");
            var mediaId = videoHtml.data("lesson-mediaid");
            var playerUrl = '/course/' + courseId + '/lesson/' + lessonId + '/player?hideBeginning=true&hideQuestion=1';
            var html = '<iframe src=\'' + playerUrl + '\' name=\'viewerIframe\' id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'100%\' style=\'border:0px\'></iframe>';
            $("#lesson-video-content").html(html);
            var messenger = new Messenger({
                name: 'parent',
                project: 'PlayerProject',
                children: [document.getElementById('viewerIframe')],
                type: 'parent'
            });
            messenger.on("timechange", function (data) {
                if (changeleft) {
                    $('.scale-white').css('left', _self._getleft(data.currentTime));
                }
            });
            $('.scale-white').on('mousedown', function (event) {
                changeleft = false;
                $(document).on('mousemove.playertime', function (event) {
                    window.getSelection ? window.getSelection().removeAllRanges() : document.selection.empty();
                    var left = event.pageX > ($editbox_list.width() + 20) ? ($editbox_list.width() + 20) : event.pageX && event.pageX <= 20 ? 20 : event.pageX;
                    $('.scale-white').css('left', left);
                    var times = _self._gettime(left);
                    messenger.sendToChild({id: 'viewerIframe'}, 'setCurrentTime', {time: times});
                }).on('mouseup.playertime', function (event) {
                    $(document).off('mousemove.playertime');
                    $(document).off('mousedown.playertime');
                    changeleft = true;
                    // messenger.sendToChild({ id: 'viewerIframe' }, 'setPlayerPlay');
                });

            });
        },
        itemRmove: function (e) {
            e.stopPropagation();
            $this = $(e.currentTarget);
            var $list = $this.closest('[data-role="scale-blue-list"]'),
                $marker_question = $this.closest('li'),
                $marker = $this.closest('.scale-blue');
            this._deleteScale($marker, $marker_question, $list.children().length, this.get('markers_array'));
        },
        stopEvent: function(e) {
            e.stopPropagation();
        },
        hoverScale: function (e) {
            var $this = $(e.currentTarget);
            if ($this.offset().left - 20 < 110) {
                $this.find('.scale-details').css('margin-left', '-' + ($this.offset().left - 20) + 'px');
            } else {
                $this.find('.scale-details').css('margin-left', '-110px');
            }
        },
        previewQuestion: function (e) {
            e.stopPropagation();
            var $this = $(e.currentTarget), url = $this.data('url');
            if (url) {
                var imgUrl = app.config.loading_img_path;
                var $target = $($this.data('target'));
                var $loadingImg = "<img src='" + imgUrl + "' class='modal-loading' style='z-index:1041;width:60px;height:60px;position:absolute;top:50%;left:50%;margin-left:-30px;margin-top:-30px;'/>";
                $target.html($loadingImg);
                $target.load(url);
            }
        },
        previewMouseDown: function (e) {
            //阻止默认事件，父层的拖动
            e.stopPropagation();
        },
        itemDraggable: function (e) {
            var _self = this,
                marker_array = [],
                $merge_marker = null,
                _mover_left = null,
                _move_time = null;

            var $dragingitem = $(e.currentTarget),
                $editbox_list = $('#editbox-lesson-list'),
                $scale_red = $('[data-role="scale-red"]'),
                $scale_red_details = $scale_red.find('[data-role="scale-red-details"]'),
                $dragingitemcopy = $dragingitem.clone().removeClass('drag').addClass('disdragg');

            $dragingitem.after($dragingitemcopy);
            _self._maskShow(true);
            //查询现有的时间刻度：数组保存现有的所有时间刻度 markers_array
            $(document).on('mousemove.dragitem', function (event) {
                if ($editbox_list.find('.placeholder').length <= 0) {
                    $scale_red.addClass('hidden');
                    $editbox_list.removeClass('highlight');
                    return;
                }
                //显示红线
                $editbox_list.addClass('highlight');

                _mover_left = event.pageX > ($editbox_list.width() + 20) ? ($editbox_list.width() + 20) : event.pageX && event.pageX <= 20 ? 20 : event.pageX;
                _move_time = _self._gettime(_mover_left);
                $scale_red_details.text(_self._convertTime(_move_time));
                $scale_red.removeClass('hidden').css('left', _mover_left);

                //markers_array靠近的元素提示合并,
                if (_self.get('markers_array').length > 0) {
                    $('.scale-blue').removeClass('highlight');
                    marker_array = [];
                    $merge_marker = null;
                    for (i in _self.get('markers_array')) {
                        if (Math.abs(_self.get('markers_array')[i].time - _move_time) <= 5) {
                            marker_array = [{
                                id: _self.get('markers_array')[i].id,
                                time: _self.get('markers_array')[i].time
                            }];
                            //靠近的元素刻度线高亮条件ID
                            $merge_marker = $('.scale-blue[id=' + _self.get('markers_array')[i].id + ']').addClass('highlight');
                            return;
                        }
                    }
                }
            }).on('mouseup.dragitem', function (event) {
                $(document).off('mousemove.dragitem');
                $(document).off('mouseup.dragitem');
                _self._maskShow(false);
                $scale_red.addClass('hidden');
                $editbox_list.removeClass('highlight');
                //未拖动
                var $moveeditem = $editbox_list.find('.item-lesson');
                if ($moveeditem.length <= 0) {
                    $scale_red.addClass('hidden');
                    $editbox_list.removeClass('highlight');
                    $dragingitemcopy.remove();
                    return;
                }
                //标记已存在，新增题目
                if (marker_array.length > 0) {
                    var $list = $merge_marker.find('[data-role="scale-blue-list"]');
                    $moveeditem.appendTo($list).find('[data-role="sqe-number"]').text($list.children().length);
                    _self._addScale($merge_marker, marker_array[0].time, $list.children().length, _self.get('markers_array'));
                    $merge_marker.removeClass('highlight');
                    _self._newSortList($list);
                } else { //新增标记和题目
                    var $scale_blue = $('[data-role="scale-blue"]'),
                        $new_scale_blue = $scale_blue.clone().css('left', _mover_left).removeAttr('data-role'),
                        $scale_blue_list = $new_scale_blue.find('[data-role="scale-blue-list"]'),
                        $scale_blue_time = $new_scale_blue.find('[data-role="scale-blue-time"]').text(_self._convertTime(_move_time));
                    $scale_blue_list.children().remove();
                    $scale_blue_list.append($moveeditem);
                    $scale_blue.after($new_scale_blue);
                    _self._addScale($new_scale_blue, _move_time, 1, _self.get('markers_array'));
                }
            });
        },
        slideScale: function (e) {
            var _self = this,
                marker_array = [],
                $merge_marker = null,
                _mover_left = null,
                _move_time = null;
            var $moveitem = $(e.currentTarget),
                $editbox_list = $('#editbox-lesson-list'),
                _oldleft = $moveitem.css('left');
            _self._maskShow(true);
            $('.marker-manage').addClass('slideing');
            $moveitem.addClass('moveing');
            $(document).on('mousemove.slide', function (event) {
                window.getSelection ? window.getSelection().removeAllRanges() : document.selection.empty();
                _mover_left = event.pageX > ($editbox_list.width() + 20) ? ($editbox_list.width() + 20) : event.pageX && event.pageX <= 20 ? 20 : event.pageX;
                _move_time = Math.round((_mover_left - 20) * _self.get('_video_time') / $editbox_list.width());
                $moveitem.css('left', _mover_left);
                $moveitem.find('[data-role="scale-blue-time"]').text(_self._convertTime(_move_time));

                if (_self.get('markers_array').length > 0) {
                    $('.scale-blue').removeClass('highlight');
                    marker_array = [];
                    $merge_marker = null;
                    for (i in _self.get('markers_array')) {
                        if (Math.abs(_self.get('markers_array')[i].time - _move_time) <= 5 && $moveitem.attr('id') != _self.get('markers_array')[i].id) {
                            marker_array = [{
                                id: _self.get('markers_array')[i].id,
                                time: _self.get('markers_array')[i].time
                            }];
                            //靠近的元素刻度线高亮条件ID
                            $merge_marker = $('.scale-blue[id=' + _self.get('markers_array')[i].id + ']').addClass('highlight');
                            return;
                        }
                    }
                }
            }).on('mouseup.slide', function (event) {
                $(document).off('mousemove.slide');
                $(document).off('mouseup.slide');
                _self._maskShow(false);
                $moveitem.removeClass('moveing');
                $('.marker-manage').removeClass('slideing');
                if (marker_array.length > 0) {
                    var $list = $merge_marker.find('[data-role="scale-blue-list"]');
                    $list.append($moveitem.find('[data-role="scale-blue-list"]').children());
                    _self._sortList($list);
                    $merge_marker.removeClass('highlight');
                    _self._mergeScale($moveitem, $merge_marker, _self.get('markers_array'));
                } else {
                    //新增
                    _self._updateScale($moveitem, _move_time);
                }
            })
        },
        _maskShow: function (show) {
            (show) ? $('[data-role="player-mask"]').removeClass('hidden') : $('[data-role="player-mask"]').addClass('hidden');
        },
        _initSortable: function () {
            var _obj = this;
            $("#subject-lesson-list").sortable({
                group: 'no-drop',
                drop: false,
                delay: 500,
                handle: '.drag',
                onDrop: function ($item, container, _super) {
                    if ($item.hasClass('item-lesson')) {
                        _super($item, container);
                        var $_scale = $item.closest('.scale.blue');
                        if ($_scale.find('.lesson-list .item-lesson').length > 0) {
                            _obj._sortList($_scale.find('.lesson-list'));
                            _obj._addScale($_scale, $_scale.find('.time').html(), $_scale.css("left"), $_scale.find('.lesson-list').children().length);
                        }
                    }
                }
            });
            
            $("#editbox-lesson-list").sortable({
                group: 'no-drop',
                drag: false
            });
        },
        _initeditbox: function (isresize) {
            var _self = this,
                $_editbox = $(_self.get("editbox"));
            if (isresize) {
                $_editbox.find('.scale.scale-default:visible').each(function () {
                    $(this).css('left', _self._getleft(_self._convertSec($(this).find('[data-role="scale-time"]').text())));
                });
                $_editbox.find('.scale.scale-blue:visible').each(function () {
                    $(this).css('left', _self._getleft(_self._convertSec($(this).find('[data-role="scale-blue-time"]').text())));
                });
            } else {
                var _partnum = _self.get("timepartnum");
                var _parttime = _self.get("_video_time") / _partnum;
                for (var i = 0; i <= _partnum; i++) {
                    var $new_scale_default = $('[data-role="scale-default"]').clone().css('left', _self._getleft(_parttime * i)).removeClass('hidden').removeAttr('data-role');
                    $new_scale_default.find('[data-role="scale-time"]').text(_self._convertTime(Math.round(_parttime * i)));
                    $('[data-role="scale-default"]').before($new_scale_default);
                }
            }
        },
        _lisentresize: function () {
            var _self = this;
            $(window).resize(function () {
                _self._initeditbox(true);
            });
        },
        _initMarkerArry: function (initMarkerArry) {
            if (initMarkerArry.length > 0) {
                var $scale_blue = $('[data-role="scale-blue"]');
                for (var i = 0; i < initMarkerArry.length; i++) {
                    $new_scale_blue = $scale_blue.clone().css('left', this._getleft(initMarkerArry[i].second)).removeAttr('data-role').removeClass('hidden').attr('id', initMarkerArry[i].id);
                    $scale_blue_time = $new_scale_blue.find('[data-role="scale-blue-time"]').text(this._convertTime(initMarkerArry[i].second));
                    var questionMarkers = initMarkerArry[i].questionMarkers;
                    var $scale_blue_item = $new_scale_blue.find('[data-role="scale-blue-item"]');
                    for (var j = 0; j < questionMarkers.length; j++) {
                        var $new_scale_blue_item = $scale_blue_item.clone().removeAttr('data-role').attr({ 'question-id': questionMarkers[j].questionId, 'id': questionMarkers[j].id});
                        $new_scale_blue_item.find('[data-role="sqe-number"]').text(j + 1);
                        $new_scale_blue_item.find('[data-role="question-type"]').text('单选题');
                        $new_scale_blue_item.find('[data-role="question-info"]').text(questionMarkers[j].stem.replace(/<.*?>/ig, ""));
                        $new_scale_blue_item.data('url','/course/'+this.get('courseId')+'/question/'+questionMarkers[j].questionId+'/marker/preview');
                        $scale_blue_item.before($new_scale_blue_item);
                    }
                    $scale_blue.after($new_scale_blue);
                    $scale_blue_item.remove();
                    this.get('markers_array').push({id: initMarkerArry[i].id, time: initMarkerArry[i].second});

                }
                this._newSortList($(this.get('scalebox')).find('[data-role="scale-blue-list"]'));
            }
        },
        _sortList: function ($list) {
            var num = 1;
            $list.find('.item-lesson').each(function () {
                $(this).find('[data-role="sqe-number"]').text(num);
                num++;
            });
        },
        _newSortList: function ($list) {
            var _self = this;
            $list.sortable({
                delay: 500,
                itemSelector: '.item-lesson',
                onDrop: function ($item, container, _super) {
                    _super($item, container);
                    _self._maskShow(false);
                    var $scale_blue = $item.closest('.scale-blue');
                    var markerJson = {
                        "id": '',
                        "questionMarkers": []
                    };
                    markerJson.id = $scale_blue.attr('id');
                    _self._sortList($scale_blue.find('[data-role="scale-blue-list"]'));
                    $scale_blue.find("li").each(function () {
                        var questionMarkers = {
                            'id': $(this).attr('id'),
                            'seq': $(this).find('[data-role="sqe-number"]').html()
                        };
                        markerJson.questionMarkers.push(questionMarkers);
                    });
                    _self._updateSeq($scale_blue, markerJson);
                    $scale_blue.removeClass('moveing');
                },
                serialize: function (parent, children, isContainer) {
                    return isContainer ? children : parent.attr('id');
                },
                isValidTarget: function ($item, container) {
                    _self._maskShow(true);
                    $item.closest('.scale-blue').addClass('moveing');
                    return true;
                }
            });
        },
        _convertTime: function (num) {
            var time = "";
            var h = parseInt((num % 86400) / 3600);
            var s = parseInt((num % 3600) / 60);
            var m = num % 60;
            if (h > 0) {
                time += h + ':';
            }
            if (s.toString().length < 2) {
                time += '0' + s + ':';
            } else {
                time += s + ':';

            }
            if (m.toString().length < 2) {
                time += '0' + m;
            } else {
                time += m;
            }
            return time;
        },
        _convertSec: function (num) {
            var arry = num.split(':');
            var sec = 0;
            for (var i = 0; i < arry.length; i++) {
                if (arry.length > 2) {
                    if (i == 0) {
                        sec += arry[i] * 3600;
                    }
                    if (i == 1) {
                        sec += arry[i] * 60;
                    }
                    if (i == 2) {
                        sec += parseInt(arry[i]);
                    }
                }
                if (arry.length <= 2) {
                    if (i == 0) {
                        sec += arry[i] * 60;
                    }
                    if (i == 1) {
                        sec += parseInt(arry[i]);
                    }
                }
            }
            return sec;
        },
        _addScale: function ($marker, time, seq, markers_array) {
            var $marker_item = $marker.find('li' + ':last');
            var markerJson = {
                "id": $marker.attr('id'),
                "second": time,
                "questionMarkers": [{
                    "id": $marker_item.attr('id'),
                    "seq": seq,
                    "questionId": $marker_item.attr('question-id')
                }]
            }
            $.extend(this.get("addScale")(markerJson, $marker, markers_array));
        },
        _mergeScale: function ($marker, $merg_marker, markers_array) {
            // 合并时后台去处理顺序，被合并数按序号依次增加
            var markerJson = {
                "id": $marker.attr('id'),
                "merg_id": $merg_marker.attr('id')
            }
            $.extend(this.get("mergeScale")(markerJson, $marker, $merg_marker, markers_array));
        },
        _updateScale: function ($marker, time) {
            var markerJson = {
                "id": $marker.attr('id'),
                "second": time,
            }
            $.extend(this.get("updateScale")(markerJson, $marker));
        },
        _deleteScale: function ($marker, $marker_question, marker_questions_num, markers_array) {
            var markerJson = {
                "id": $marker.attr('id'),
                "questionMarkers": [{
                    "id": $marker_question.attr('id'),
                    "seq": $marker_question.find('[data-role="sqe-number"]').html(),
                    "questionId": $marker_question.attr('question-id')
                }]
            };
            $.extend(this.get("deleteScale")(markerJson, $marker, $marker_question, marker_questions_num, markers_array));
        },
        _updateSeq: function ($scale, markerJson) {
            $.extend(this.get("updateSeq")($scale, markerJson));
        },
        _getleft: function (time) {
            var _width = $('#editbox-lesson-list').width();
            var _totaltime = parseInt(this.get("_video_time"));
            var _left = time * _width / _totaltime;
            return _left + 20;
        },
        _gettime: function (left) {
            return Math.round((left - 20) * this.get('_video_time') / $('#editbox-lesson-list').width());
        },
    });
    // 未避免初始化前端排序操作，将questionMarkers按生序方式返回，可省略questionMarkers.seq
    var initMarkerArry = [];
    var mediaLength = 30;
    $.ajax({
        type: "get",
        url: $('.js-pane-question-content').data('marker-metas-url'),
        cache: false,
        async: false,
        success: function (data) {
            initMarkerArry = data.markersMeta;
            mediaLength = data.videoTime;
        }
    });

    var myDraggableWidget = new DraggableWidget({
        element: "#lesson-dashboard",
        initMarkerArry: initMarkerArry,
        _video_time: mediaLength,
        addScale: function (markerJson, $marker, markers_array) {
            var url = $('.js-pane-question-content').data('queston-marker-add-url');
            var param = {
                markerId: markerJson.id,
                second: markerJson.second,
                questionId: markerJson.questionMarkers[0].questionId,
                seq: markerJson.questionMarkers[0].seq
            };
            $.post(url, param, function (data) {
                if (data.id == undefined) {
                    return;
                }
                //新增时间刻度
                if (markerJson.id == undefined) {
                    $marker.attr('id', data.markerId);
                    markers_array.push({id: data.markerId, time: markerJson.second});
                    //排序

                }
                $marker.removeClass('hidden');
                $marker.find('.item-lesson[question-id=' + markerJson.questionMarkers[0].questionId + ']').attr('id', data.id);
            });
            return markerJson;
        },
        mergeScale: function (markerJson, $marker, $merg_emarker, markers_array) {
            var url = $('.js-pane-question-content').data('marker-merge-url');
            $.post(url, {
                sourceMarkerId: markerJson.id,
                targetMarkerId: markerJson.merg_id
            }, function (data) {
                $marker.remove();
                for (i in markers_array) {
                    if (markers_array[i].id == markerJson.id) {
                        markers_array.splice(i, 1);
                        break;
                    }
                }
            });
            return markerJson;
        },
        updateScale: function (markerJson, $marker) {
            var url = $('.js-pane-question-content').data('marker-update-url');
            var param = {
                id: markerJson.id,
                second: markerJson.second
            };
            if(markerJson.second){
                $.post(url, param, function (data) {
                });
            }else{
                console.log('do not need upgrade scale...');
            }
            return markerJson;
        },
        deleteScale: function (markerJson, $marker, $marker_question, marker_questions_num, markers_array) {
            var url = $('.js-pane-question-content').data('queston-marker-delete-url');
            $.post(url, {
                questionId: markerJson.questionMarkers[0].id
            }, function (data) {
                $marker_question.remove();
                $('#subject-lesson-list').find('.item-lesson[question-id=' + markerJson.questionMarkers[0].questionId + ']').removeClass('disdragg').addClass('drag');
                if ($marker.find('[data-role="scale-blue-list"]').children().length <= 0) {
                    $marker.remove();
                    for (i in markers_array) {
                        if (markers_array[i].id == $marker.attr('id')) {
                            markers_array.splice(i, 1);
                            break;
                        }
                    }
                } else {
                    //剩余排序
                    sortList($marker.find('[data-role="scale-blue-list"]'));
                }
            });
        },
        updateSeq: function ($scale, markerJson) {
            if (markerJson == undefined || markerJson.questionMarkers == undefined || markerJson.questionMarkers.length == 0) {
                return;
            }

            var url = $('.js-pane-question-content').data('queston-marker-sort-url');
            param = new Array();

            for (var i = 0; i < markerJson.questionMarkers.length; i++) {
                param.push(markerJson.questionMarkers[i].id);
            }

            $.post(url, {questionIds: param});
        }
    });

    function sortList($list) {
        myDraggableWidget._sortList($list);
    }
});