define(function(require, exports, module) {
    require('jquery.sortable');
    var Widget = require('widget');

    var DraggableWidget = Widget.extend({
        attrs: {
            item: '.item-lesson',
            placeholder: '.placeholder',
            videotime: '68',
            editbox: '.editbox',
            scalebox: '.scalebox',
            timepartnum: '6',
            subject_lesson_list: '#subject-lesson-list',
            editbox_lesson_list: "#editbox-lesson-list",
            group_list: ".gruop-lesson-list",
            arryid: [],
            newId: '',
            isDraggable: 'false',
            initscale: {},
            Dragitem: [],
            addScale: function(markerJson, $marker, $item_lesson) {
                return markerJson;
            },
            mergeScale: function(markerJson, $marker, $merg_emarker, childrenum) {
                return scalejson;
            },
            updateScale: function($marker, markerJson, old_position, old_time) {
                return scalejson;
            },
            deleteScale: function(markerJson, $marker, $marker_list_item) {
                return scalejson;
            }
        },
        events: {
            'mousedown {{attrs.item}}': 'itemDraggable',
            'click .lesson-list .icon-close': 'itemRmove',
            'mousedown .scale.blue': 'slideScale',
            'mouseenter .scale.blue': 'hoverScale'
        },
        setup: function() {
            this._initSortable();
            this._initeditbox();
            this._initScale(this.get('initscale'));
        },
        itemDraggable: function(e) {
            var $this = $(e.currentTarget);
            var _obj = this;
            var $obj = $(this.element)
            var isMove = true;
            //开始拖动事件
            _obj.set('isDraggable', 'true');
            var $editbox = $(_obj.get("editbox"));
            var $scalebox = $(_obj.get("scalebox"));
            var $subject_lesson_list = $(this.element).find(_obj.get('subject_lesson_list'));
            var $editbox_lesson_list = $(this.element).find(_obj.get('editbox_lesson_list'));
            var value = '<span class="show-sub"><i class="es-icon es-icon-infooutline mrm"></i>' + '将题目拖至左侧时间条' + '</span>' + '<span class="show-edit">左右拖动选择时间确定位置后松开鼠标</span>';

            var $scale = $editbox.find("#default-scale");
            var $scale_details = $scale.find(".scale-details");

            // 显示时间轴
            $scale.css("visibility", "visible");
            $scale_details.css("visibility", "visible");

            var arry = [];
            // 遍历时间刻度
            $scalebox.children('.scale.blue').each(function() {
                console.log($this);
                var $item_blue = $(this);
                var scale = {
                    'id': $item_blue.attr('id'),
                    'sec': _obj._convertSec($item_blue.find('.time').html())
                };
                arry.push(scale);
            });

            // console.log(arry);
            $(document).mousemove(function(event) {
                if (isMove) {
                    $subject_lesson_list.find(_obj.get('placeholder')).html(value);
                    _obj._moveShow($scale, $scale_details, $scalebox, _obj, arry);
                }
            }).mouseup(function() {
                // 停止拖动
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
                            console.log("timesec" + timesec);
                            if (Math.abs(parseInt(timesec) - parseInt(arry[i].sec)) <= 5) {
                                console.log('合并');
                                console.log(Math.abs(parseInt(timesec) - parseInt(arry[i].sec)));
                                bool = false
                                id = arry[i].id;
                                timesec = arry[i].sec;
                                break;
                            }
                        }
                    }
                    if (bool) {
                        console.log("增加");
                        var $new_scale = $('<a class="scale blue" id=""><div class="border"></div><div class="scale-details"><ul class="lesson-list"></ul><div class="time">' + timestr + '</div></div></a>').css("left", postionleft).appendTo($scalebox);
                        $editbox_lesson_list.children().appendTo($new_scale.find('.lesson-list'));
                        // 新生成的scale注册拖动事件:
                        _obj._newSortList($new_scale.find(_obj.get('item')));
                        // 第一个元素：在这返回数据到后台，其他在Drop中返回：
                        if (_obj.get('Dragitem').length <= 0) {
                            _obj._addScale($new_scale, timestr, postionleft, 1);
                        }
                        // 第一条数据无序排序
                        // 其他在Drop中返回：
                        console.log($editbox_lesson_list.children().length);
                        var Dragitem = [];
                        Dragitem.push($new_scale);
                        Dragitem.push(timestr);
                        Dragitem.push(postionleft);
                        _obj.set("Dragitem", Dragitem);
                        console.log("增加完成");

                    } else {
                        //相同直接获取存在的ID
                        console.log("合并");
                        console.log("id " + id);
                        var $_scale = $scalebox.find('a[id=' + id + ']');
                        $editbox_lesson_list.children().appendTo($_scale.find(".lesson-list"));
                        //隐藏
                        $_scale.find('.border').removeClass('show');
                        // 其他在Drop中传递当前信息给后台，并进行排序
                        var Dragitem = [];
                        Dragitem.push($_scale);
                        Dragitem.push(_obj._convertTime(timesec));
                        Dragitem.push($_scale.css('left'));
                        _obj.set("Dragitem", Dragitem);

                    }
                }
                console.log("拖动停止");
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
                console.log(arry);

                $(document).mousemove(function(event) {
                    if (isMove) {
                        _obj._moveShow($this, $time, $scalebox, _obj, arry);
                    }
                }).mouseup(function() {
                    // 避免上次被隐藏的元素响应鼠标点击事件，｛发生一次合并后，再次增加时间轴会再次响应。｝
                    if ($this.length > 0 && $this.is(":visible")) {
                        console.log($this);
                        console.log("拖动过程中触发up");
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
                        console.log(arry);
                        var new_time = _obj._convertSec($time.html());
                        console.log('new_time' + new_time);
                        // 判断是否移动，阻止点击事件触发方法，或者移动回原位置；
                        if (old_time != new_time) {
                            var additem = true;
                            var mergeid = -1;
                            // 至少同时存在有俩个时间轴才需进行是否合并的判断，否则直接修改；
                            if (arry.length > 1) {
                                for (var i = arry.length - 1; i >= 0; i--) {
                                    console.log("循环时间" + arry[i].sec);
                                    if (Math.abs(parseInt(new_time) - parseInt(arry[i].sec)) <= 5) {
                                        //当前时间(排除自身)，已存在时间轴，将合并到该时间轴，然后自身的题目合并到该时间轴，并移除自身。
                                        // 合并后被合并元素可能会触发该事件加判断arry[i].id.toString().length
                                        if ($this.attr('id') != arry[i].id) {
                                            additem = false;
                                            mergeid = arry[i].id;
                                            console.log("当前时间" + new_time);
                                            console.log("被拖动的 " + $this.attr('id'));
                                            console.log("将要被合并到目标的地id " + arry[i].id);
                                            console.log("目标的地时间" + arry[i].sec);
                                            console.log(i);
                                            break;
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
                                    }

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
                afterMove: function(placeholder, container) {
                    console.log("afterMove");
                    if (oldContainer != container) {
                        oldContainer = container;
                    }
                },
                onDrop: function($item, container, _super) {
                    _super($item, container);
                    var Dragitem = _obj.get('Dragitem');
                    if (Dragitem.length > 0) {
                        _obj._sortList(Dragitem[0].find('.lesson-list'));
                        _obj._addScale(Dragitem[0], Dragitem[1], Dragitem[2], Dragitem[0].find('.lesson-list').children().length);
                    }
                    // var id = _obj.get("newId");
                    // if (id.toString().length > 0) {
                    //     var $scale = $(_obj.get("scalebox")).find('a[id=' + id + ']')
                    //     _obj._addScale($scale, $scale.find('.time').html(), true);
                    // }
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
        _initScale: function(scalejson) {
            // var $editbox = $(this.get('editbox'));
            // var $subject_lesson_list = $(this.get('subject_lesson_list'));
            // var $newscale = $('<a class="scale blue" id="' + scalejson.scaleid + '"><div class="border"></div><div class="scale-details"><ul class="lesson-list"></ul><div class="time">' + this._convertTime(scalejson.scaletime) + '</div></div></a>').css("left", scalejson.scaleleft).appendTo($editbox.find('.scalebox'));
            // var $lesson_list = $newscale.find('.lesson-list');
            // var subject = scalejson.subject;
            // for (var i = 0; i < subject.length; i++) {
            //     $subject_lesson_list.find(this.get('item') + '[data-id=' + subject[i].id + ']').find('.number .num').html(subject[i].ordinal);
            //     $subject_lesson_list.find(this.get('item') + '[data-id=' + subject[i].id + ']').appendTo($lesson_list);
            // }
            // var arry = this.get('arryid').push(scalejson.scaleid);
        },
        _sortList: function($list) {
            var num = 1;
            $list.find('.item-lesson').each(function() {
                $(this).find('.num').text(num);
                num++;
            });
        },
        _newSortList: function($list) {
            $list.sortable({
                distance: 20,
                itemSelector: '.item-lesson',
                onDrop: function(item, container, _super) {
                    _super(item, container);
                    this._sortList($list);
                },
                serialize: function(parent, children, isContainer) {
                    return isContainer ? children : parent.attr('id');
                },
                isValidTarget: function(item, container) {
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
        _convertNUm: function(num) {
            var string = "";
            var arr = num.toString().split(":");
            if (arr.length > 0) {
                for (var i = 0; i < arr.length; i++) {
                    string += arr[i];
                };
            }
            return string;
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
            }
            $.extend(this.get("deleteScale")(markerJson, $marker, $marker_list_item));
        }
    });
    module.exports = DraggableWidget;
});