define(function(require, exports, module) {
    require('jquery.sortable');
    var Widget = require('widget');

    var DraggableWidget = Widget.extend({
        attrs: {
            item: '.item-lesson',
            placeholder: '.placeholder',
            videotime: '68', //视频总时长
            editbox: '.editbox',
            scalebox: '.scalebox',
            timepartnum: '6',
            subject_lesson_list: '#subject-lesson-list',
            editbox_lesson_list: "#editbox-lesson-list",
            group_list: ".gruop-lesson-list",
            isDraggable: 'false', //拖动时阻止滑动事件响应
            initMarkerArry: [], //初始化数据
            updateSqeArry: [],
            addScale: function(markerJson, $marker, $item_lesson) {
                return markerJson;
            },
            mergeScale: function(markerJson, $marker, $merg_emarker, childrenum) {
                return markerJson;
            },
            updateScale: function($marker, markerJson, old_position, old_time) {
                return markerJson;
            },
            deleteScale: function(markerJson, $marker, $marker_list_item) {
                return markerJson;
            },
            updateSqe: function($marker, questionMarkers_id, seq, new_seq) {
                return true;
            }
        },
        events: {
            'mousedown .gruop-lesson-list .item-lesson': 'itemDraggable',
            'click .lesson-list .icon-close': 'itemRmove',
            'mousedown .scale.blue': 'slideScale',
            'mouseenter .scale.blue': 'hoverScale',
            'mousedown .scale.blue .item-lesson': 'itemSqe',
            'mousedown .marker-preview': 'previewMouseDown'
        },
        setup: function() {
            this._initSortable();
            this._initeditbox();
            this._initMarkerArry(this.get('initMarkerArry'));
        },
        itemDraggable: function(e) {
            var _obj = this;
            console.log("开始拖拽");
            //开始拖动事件
            _obj.set('isDraggable', 'true');

            var $this = $(e.currentTarget);
            var $obj = $(this.element)
            var isMove = true;
            var $editbox = $(_obj.get("editbox"));
            var $scalebox = $(_obj.get("scalebox"));
            var $subject_lesson_list = $(this.element).find(_obj.get('subject_lesson_list'));
            var $editbox_lesson_list = $(this.element).find(_obj.get('editbox_lesson_list'));
            var value = '<span class="show-sub"><i class="es-icon es-icon-infooutline mrm"></i>' + '将题目拖至左侧时间条' + '</span>' + '<span class="show-edit">左右拖动选择时间确定位置后松开鼠标</span>';

            // 显示红色时间轴
            var $scale = $editbox.find("#default-scale");
            var $scale_details = $scale.find(".scale-details");
            $scale.css("visibility", "visible");
            $scale_details.css("visibility", "visible");

            var arry = [];
            // 遍历时间刻度
            $scalebox.children('.scale.blue').each(function() {
                var $item_blue = $(this);
                var scale = {
                    'id': $item_blue.attr('id'),
                    'sec': _obj._convertSec($item_blue.find('.time').html())
                };
                arry.push(scale);
            });
            $(document).mousemove(function(event) {
                if (isMove) {
                    $subject_lesson_list.find(_obj.get('placeholder')).html(value);
                    _obj._moveShow($scale, $scale_details, $scalebox, _obj, arry);
                }
            }).mouseup(function() {
                console.log("停止拖拽");
                // 停止拖动
                $(document).off('mousemove');
                $(document).off('mouseup');
                isMove = false;
                // 隐藏默认时间轴
                $scale.css("visibility", "hidden");
                $scale_details.css("visibility", "hidden");
                var timestr = $scale_details.html();
                var postionleft = $scale.css("left");
                var timesec = _obj._convertSec(timestr);
                var id = '';
                var arry = [];
                // 遍历时间刻度:整理删除
                $scalebox.children('.scale.blue').each(function() {
                    var $item_blue = $(this);
                    var scale = {
                        'id': $item_blue.attr('id'),
                        'sec': _obj._convertSec($item_blue.find('.time').html())
                    };
                    arry.push(scale);
                });

                if ($editbox_lesson_list.children().length > 0) {
                    var bool = true;
                    if (arry.length > 0) {
                        // 遍历所有元素都与当前时间差，一旦遍历有时间挫小于5秒就跳出循环，认定为最接近元素。
                        for (var i = arry.length - 1; i >= 0; i--) {
                            if (Math.abs(parseInt(timesec) - parseInt(arry[i].sec)) <= 5) {
                                bool = false
                                id = arry[i].id;
                                timesec = arry[i].sec;
                                break;
                            }
                        }
                    }
                    if (bool) {
                        console.log("增加时间轴");
                        var $new_scale = $('<a class="scale blue" id=""><div class="border"></div><div class="scale-details"><ul class="lesson-list"></ul><div class="time">' + timestr + '</div></div></a>').css("left", postionleft).appendTo($scalebox);
                        $editbox_lesson_list.children().appendTo($new_scale.find('.lesson-list'));
                        // 新生成的scale注册拖动事件:
                        _obj._newSortList($new_scale.find('.lesson-list'));
                        if ($new_scale.find('.lesson-list .item-lesson').length > 0) {
                            console.log("处理增加时间轴回调:li已经移动完成");
                            _obj._addScale($new_scale, timestr, $new_scale.css("left"), $new_scale.find('.lesson-list').children().num);
                        } else {
                            // 判断
                            console.log("增加时间轴完成:li未移动完成需要在drop 中去传递数据到后台");
                        }
                    } else {
                        //相同直接获取存在的ID
                        console.log("需要合并");
                        console.log("id " + id);
                        var $_scale = $scalebox.find('a[id=' + id + ']');
                        $editbox_lesson_list.children().appendTo($_scale.find(".lesson-list"));
                        //隐藏
                        $_scale.find('.border').removeClass('show');

                        if ($_scale.find('.lesson-list .item-lesson').length > 0) {
                            console.log("排序和处理合并回调:li已经移动完成");
                            _obj._sortList($_scale.find('.lesson-list'));
                            _obj._addScale($_scale, timestr, $_scale.css("left"), $_scale.find('.lesson-list').children().length);
                        } else {
                            console.log("合并和排序完成li未移动完成需要在drop 中去传递数据到后台");
                        }
                    }
                }
                console.log("拖拽处理完成");
                // 拖动时间停止
                _obj.set('isDraggable', 'false');

            });
        },
        itemRmove: function(e) {
            $this = $(e.currentTarget);
            var $list = $this.closest('.lesson-list');
            var num = $list.children().length;
            //要移除的li
            var $list_item = $this.closest(this.get('item'));
            // 所属时间轴
            var $scale = $this.closest('.scale.blue');

            //将li移除，放回原位，然后list重新排序
            $list_item.appendTo($(this.get("subject_lesson_list")));
            this._sortList($list);

            //判断当前子元数小于0移除蓝色的时间挫；
            if (num <= 1) {
                // 假删除，待成功回调后真删除
                $scale.hide();
            }
            this._deleteScale($scale, $list_item);
        },
        slideScale: function(e) {
            //避免拖动过程中触发事件
            if (this.get('isDraggable') == 'false') {
                console.log("开始滑动");
                var _obj = this;
                var $this = $(e.currentTarget);
                var $scalebox = $(_obj.get("scalebox"));
                var $scale_details = $this.find(".scale-details ");
                var $time = $this.find(".scale-details .time");
                var old_time = _obj._convertSec($time.html());
                var old_position = $this.css('left');
                var isMove = true;
                var arry = [];
                // 遍历时间刻度
                $scalebox.children('.scale.blue').each(function() {
                    var $item_blue = $(this);
                    var scale = {
                        'id': $item_blue.attr('id'),
                        'sec': _obj._convertSec($item_blue.find('.time').html())
                    };
                    arry.push(scale);
                });
                $(document).mousemove(function(event) {
                    if (isMove) {
                        _obj._moveShow($this, $time, $scalebox, _obj, arry);
                    }
                }).mouseup(function() {
                    console.log("停止滑动");
                    $(document).off('mousemove');
                    $(document).off('mouseup');
                    // 避免上次被隐藏的元素响应鼠标点击事件，｛发生一次合并后，再次增加时间轴会再次响应。｝
                    if ($this.length > 0 && $this.is(":visible")) {
                        console.log("sfsdf");
                        isMove = false;
                        var arry = [];
                        $scalebox.children('.scale.blue').each(function() {
                            var $item_blue = $(this);
                            var scale = {
                                'id': $item_blue.attr('id'),
                                'sec': _obj._convertSec($item_blue.find('.time').html())
                            };
                            arry.push(scale);
                        });
                        var new_time = _obj._convertSec($time.html());
                        // 判断是否移动，阻止点击事件触发方法，或者移动回原位置；
                        console.log(old_time != new_time);
                        if (old_time != new_time) {
                            var additem = true;
                            var mergeid = -1;
                            // 至少同时存在有俩个时间轴才需进行是否合并的判断，否则直接修改；
                            if (arry.length > 1) {
                                for (var i = arry.length - 1; i >= 0; i--) {
                                    if (Math.abs(parseInt(new_time) - parseInt(arry[i].sec)) <= 5) {
                                        //当前时间(滑动操作需排除自身)，已存在时间轴，将合并到该时间轴，然后自身的题目合并到该时间轴，并移除自身。
                                        if ($this.attr('id') != arry[i].id) {
                                            additem = false;
                                            mergeid = arry[i].id;
                                            break;
                                        }
                                    }
                                }
                            }
                            if (additem) {
                                console.log("滑动修改");
                                // 做修改操作：直接改变时间轴的时间即可
                                _obj._updateScale($this, new_time, old_position, old_time);
                            } else {
                                console.log('滑动合并');
                                // 合并后被合并元素可能会触发该事件加判断$this.length后期优化
                                if ($this.length > 0) {
                                    console.log('开始滑动合并');
                                    var $_scale = $scalebox.find('.scale.blue[id=' + mergeid + ']');
                                    var childrenum = $this.find('.lesson-list').children().length;
                                    $this.find('.lesson-list').children().appendTo($_scale.find('.lesson-list'));
                                    // 假删除：防止请求成功后再真删
                                    $this.hide();
                                    _obj._sortList($_scale.find('.lesson-list'));
                                    _obj._mergeScale($this, $_scale, childrenum);
                                    console.log('滑动处理结束');
                                }
                            }
                            $scalebox.find('.border').removeClass('show');
                        }
                    }
                });
            }
        },
        hoverScale: function(e) {
            var $this = $(e.currentTarget);
            if ($this.offset().left - 20 < 110) {
                $this.find('.scale-details').css('margin-left', '-' + ($this.offset().left - 20) + 'px');
            } else {
                $this.find('.scale-details').css('margin-left', '-110px');
            }
        },
        itemSqe: function(e) {
            //阻止默认事件，父层的滑动
            e.stopPropagation();
        },
        previewMouseDown: function(e) {
            //阻止默认事件，父层的拖动
            e.stopPropagation();
        },
        _moveShow: function($scale, $scale_details, $scalebox, _obj, arry) {
            var offsetenter = $(".dashboard-content").offset().left + $(".dashboard-content").width();

            var left = 0;
            //鼠标进入右侧交互
            //显示移动时间轴的位置
            if (event.pageX > offsetenter) {
                left = offsetenter - 20 - 1;
            } else if (event.pageX < offsetenter && event.pageX > 20) {
                left = event.pageX - 20 - 1;
            } else if (event.pageX < 20) {
                left = 0;
            }

            $scale.css("left", left);

            //显示移动时间轴的时间
            var time = parseInt(_obj.get("videotime"));
            var width = $(".dashboard-content").width();
            var scale_value = Math.round(left * time / width);
            $scale_details.html(_obj._convertTime(scale_value));
            // 查找5秒范围类的ID，提示合并效果
            var timesec = scale_value;
            if (arry.length > 0) {
                for (var i = arry.length - 1; i >= 0; i--) {
                    if (Math.abs(parseInt(timesec) - parseInt(arry[i].sec)) <= 5) {
                        $scalebox.find('.scale.blue' + '[id=' + arry[i].id + ']').find('.border').addClass('show');
                    } else {
                        $scalebox.find('.scale.blue' + '[id=' + arry[i].id + ']').find('.border').removeClass('show');
                    }
                }
            }
        },
        _initSortable: function() {
            var _obj = this;
            var _classname = $(_obj.element).find(_obj.get('group_list'));
            var oldContainer;
            var $list = $(_classname).sortable({
                group: _classname,
                delay: 500,
                onDrop: function($item, container, _super) {
                    console.log("onDrop");
                    _super($item, container);
                    var $_scale = $item.closest('.scale.blue');
                    if ($_scale.find('.lesson-list .item-lesson').length > 0) {
                        console.log("在onDrop中传递数据到后台");
                        _obj._sortList($_scale.find('.lesson-list'));
                        _obj._addScale($_scale, $_scale.find('.time').html(), $_scale.css("left"), $_scale.find('.lesson-list').children().length);

                    }
                    console.log("onDrop over");
                }
            });
        },
        _initeditbox: function() {
            var _obj = this;
            var $_editbox = $(_obj.get("editbox"));
            var _width = $_editbox.width();
            // 以秒为单位
            var _totaltime = _obj.get("videotime");
            var _partnum = _obj.get("timepartnum");

            if (_partnum > 0) {
                var _parttime = Math.round(_totaltime / _partnum);
                var _partwidth = Math.round(_width / _partnum);
                for (var i = 1; i <= _partnum; i++) {
                    var num = i * _parttime;
                    var time = _obj._convertTime(num);

                    $_editbox.find(_obj.get("scalebox")).append('<a style="left:' + i * _partwidth + 'px" data-toggle="tooltip" data-placement="top"' + 'title="' + time + '"></a>');
                }
                $('[data-toggle="tooltip"]').tooltip();
            }
        },
        _initMarkerArry: function(initMarkerArry) {
            if (initMarkerArry.length > 0) {
                var $editbox = $(this.get('editbox'));
                var $subject_lesson_list = $(this.get('subject_lesson_list'));
                for (var i = 0; i < initMarkerArry.length; i++) {
                    var $newscale = $('<a class="scale blue" id="' + initMarkerArry[i].id + '"><div class="border"></div><div class="scale-details"><ul class="lesson-list"></ul><div class="time">' + this._convertTime(initMarkerArry[i].second) + '</div></div></a>').css("left", initMarkerArry[i].position).appendTo($editbox.find('.scalebox'));
                    var $lesson_list = $newscale.find('.lesson-list');
                    var questionMarkers = initMarkerArry[i].questionMarkers;
                    console.log(questionMarkers.length);

                    for (var j = 0; j < questionMarkers.length; j++) {
                        $subject_lesson_list.find(this.get('item') + '[question-id=' + questionMarkers[j].questionId + ']').attr('id', questionMarkers[j].id).appendTo($lesson_list).find('.number .num').html(questionMarkers[j].seq);
                    }
                }
            }
        },
        _sortList: function($list) {
            var num = 1;
            $list.find('.item-lesson').each(function() {
                $(this).find('.num').text(num);
                num++;
            });
        },
        _newSortList: function($list) {
            var _obj = this;
            $list.sortable({
                distance: 20,
                itemSelector: '.item-lesson',
                onDrop: function(item, container, _super) {
                    console.log("onDrop");
                    _super(item, container);
                    _obj._sortList($list);
                    //判断是否需要传给后台进行排序
                    if (_obj.get('updateSqeArry').length > 0) {
                        if ($(item).find('.number .num').html() != _obj.get('updateSqeArry')[0]) {
                            后台进
                        }
                    }

                },
                serialize: function(parent, children, isContainer) {
                    console.log("serialize");
                    return isContainer ? children : parent.attr('id');
                },
                isValidTarget: function(item, container) {
                    if (_obj.get('updateSqeArry').length < 0) {
                        _obj.get('updateSqeArry').push($(item).find('.number .num').html());
                    }
                    if (item.siblings('li').length) {
                        return true;
                    } else {
                        return false;
                    }
                }
            });
        },
        _convertTime: function(num) {
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
        _convertSec: function(num) {
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
        _addScale: function($marker, timestr, postionleft, seq) {
            var $marker_item = $marker.find('li' + ':last');
            var markerJson = {
                "id": $marker.attr('id'),
                "second": this._convertSec(timestr),
                "position": postionleft,
                "questionMarkers": [{
                    "id": $marker_item.attr('id'),
                    "seq": seq,
                    "questionId": $marker_item.attr('question-id')
                }]
            }
            $.extend(this.get("addScale")(markerJson, $marker));
        },
        _mergeScale: function($marker, $merg_marker, childrenum) {
            // 合并时后台去处理顺序，被合并数按序号依次增加
            var markerJson = {
                "id": $marker.attr('id'),
                "merg_id": $merg_marker.attr('id')
            }
            $.extend(this.get("mergeScale")(markerJson, $marker, $merg_marker, childrenum));
        },
        _updateScale: function($marker, newtime, old_position, old_time) {
            var markerJson = {
                "id": $marker.attr('id'),
                "second": newtime,
                "position": $marker.css('left')
            }
            $.extend(this.get("updateScale")($marker, markerJson, old_position, old_time));
        },
        _deleteScale: function($marker, $marker_list_item) {
            var markerJson = {
                "id": $marker.attr('id'),
                "questionMarkers": [{
                    "id": $marker_list_item.attr('id'),
                    "seq": $marker_list_item.find('.number .num').html(),
                    "questionId": $marker_list_item.attr('question-id')
                }]
            };
            $.extend(this.get("deleteScale")(markerJson, $marker, $marker_list_item));
        },
        _updateSqe: function($marker, questionMarkers_id, seq, new_seq) {
            var markerJson = {
                "id": $marker.attr('id'),
                "questionMarkers": [{
                    "id": questionMarkers_id,
                    "seq": seq,
                    "new_seq": new_seq
                }]
            };
            $.extend(this.get("updateSqe")(markerJson, $marker, $marker_list_item));
        }
    });
    module.exports = DraggableWidget;
});