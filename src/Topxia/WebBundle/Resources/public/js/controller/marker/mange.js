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
            initscale:{},
            addScale: function(scalejson) {
                return scalejson;
            },
            mergeScale: function(scalejson) {
                return scalejson;
            },
            updateScale: function(scalejson) {
                return scalejson;
            },
            deleteScale: function(scalejson) {
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

            $(document).mousemove(function(event) {
                if (isMove) {
                    $subject_lesson_list.find(_obj.get('placeholder')).html(value);
                    _obj._moveShow($scale, $scale_details, $scalebox, _obj);
                }
            }).mouseup(function() {
                // 停止拖动
                isMove = false;
                // 隐藏默认时间轴
                $scale.css("visibility", "hidden");
                $scale_details.css("visibility", "hidden");
                var timeiD = _obj._convertNUm($scale_details.html());
                var left = $scale.css("left");
                var arry = _obj.get("arryid");
                if ($editbox_lesson_list.children().length > 0) {
                    var bool = true;
                    if (arry.length > 0) {
                        // 遍历所有元素都与当前时间差，一旦遍历有时间挫小于5秒就跳出循环，认定为最接近元素。
                        for (var i = arry.length - 1; i >= 0; i--) {
                            if (Math.abs(parseInt(timeiD) - parseInt(arry[i])) <= 5) {
                                bool = false
                                timeiD = arry[i];
                                break;
                            }
                        }
                        if (bool) {
                            // 遍历完成后为出现靠近元素，数组记录ID
                            arry.push(timeiD);
                        }
                    } else {
                        // 第一个元素
                        arry.push(timeiD);
                    }
                    if (bool) {
                        // 生成一个时间轴：但前时间作为时间轴的ID
                        var $newscale = $('<a class="scale blue" id="' + timeiD + '"><div class="border"></div><div class="scale-details"><ul class="lesson-list"></ul></div></a>').css("left", left).appendTo($editbox.find('.scalebox'));
                        $editbox_lesson_list.children().appendTo($newscale.find(".lesson-list"));
                        $newscale.find(".lesson-list").after('<div class="time">' + $scale_details.html() + '</div>');
                        // 拖拽后排序：在当前获取不到最新的li,在拖动事件的完成后查找当前的时间轴id中的ul进行排序
                        // 新生成的scale注册拖动事件:
                        _obj._newSortList($newscale.find(".lesson-list"), _obj);
                        // 第一个元素：在这返回数据到后台，其他在Drop中返回：第一次添加时newId为空
                        if (_obj.get('newId').toString().length <= 0) {
                            _obj._addScale($newscale, $scale_details.html(), true);
                        }
                        _obj.set("newId", timeiD);
                    } else {
                        console.log("text");
                        _obj.set("newId", timeiD);
                        //相同直接获取存在的ID
                        var $_scale = $editbox.find('.scalebox').find('a[id=' + timeiD + ']');
                        $editbox_lesson_list.children().appendTo($_scale.find(".lesson-list"));
                        //隐藏
                        $_scale.find('.border').removeClass('show');
                    }
                }
                console.log(arry);
                // 拖动时间停止
                _obj.set('isDraggable', 'false');
            });
        },
        itemRmove: function(e) {
            $this = $(e.currentTarget);
            var $list = $this.closest('.lesson-list');
            var $list_item = $this.closest('.item-lesson');
            var num = $list.children().length;
            var $scale = $this.closest('.scale.blue');
            var scaleid = $scale.attr('id');

            $list_item.appendTo($(this.get("subject_lesson_list")));
            this._sortList($list);
            // this._sortList($(this.get("subject-lesson-list")));
            //判断当前子元数小于0移除蓝色的时间挫；

            this._deleteScale(scaleid, $list_item.find('.idname').html());
            if (num <= 1) {
                $scale.remove();
                // 清楚数组中保留的时间ID
                var arr = this.get("arryid");
                if (arr.length > 0) {
                    for (var i = arr.length - 1; i >= 0; i--) {
                        if (arr[i] == scaleid) {
                            arr.splice(i, 1);
                            console.log(arr);
                        }
                    };
                }
            }
        },
        slideScale: function(e) {
            //避免拖动过程中触发事件
            if (this.get('isDraggable') == 'false') {
                var _obj = this;
                var $this = $(e.currentTarget);
                var $scalebox = $(_obj.get("scalebox"));
                var $time = $this.find(".scale-details .time");
                var old_id = $this.attr('id');
                var isMove = true;
                $(document).mousemove(function(event) {
                    var left = 0;
                    if (isMove) {
                        _obj._moveShow($this, $time, $scalebox, _obj);
                    }
                }).mouseup(function() {
                    isMove = false;
                    $(document).unbind(); //移除所有 
                    old_id = $this.attr('id');
                    $this.attr('id', _obj._convertNUm($this.find(".scale-details .time").html()));
                    var newid = $this.attr('id');
                    // 判断是非移动，阻止点击时间造成的影响
                    if (newid != old_id) {
                        var additem = true;
                        var mergeid = -1;
                        var arry = _obj.get("arryid");
                        if (arry.length > 0) {
                            for (var i = arry.length - 1; i >= 0; i--) {
                                if (arry[i] == old_id) {
                                    // 要循环所有将旧id删除，不能中途跳出循环
                                    arry.splice(i, 1);
                                }
                                if ($this.attr('id') == arry[i] || Math.abs(parseInt($this.attr('id')) - parseInt(arry[i])) <= 5) {
                                    // 将合并到该目标div，数组无需纪录ID，自身隐藏
                                    additem = false;
                                    mergeid = arry[i];
                                }
                            }
                        }
                        if (additem) {
                            arry.push($this.attr('id'));
                            _obj._updateScale(old_id, $this.attr('id'), _obj._convertTime($this.attr('id')));
                        } else {
                            var $_scale = $scalebox.find('a[id=' + mergeid + ']');
                            if ($_scale.length > 0) {
                                $this.find('.lesson-list').children().appendTo($_scale.find('.lesson-list'));
                                $this.remove();
                                _obj._sortList($_scale.find('.lesson-list'));
                                _obj._mergeScale(mergeid, old_id);
                            }

                        }
                        $scalebox.find('.border').removeClass('show');
                        console.log(arry);
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
        _moveShow: function($scale, $scale_details, $scalebox, _obj) {
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
            var timeiD = _obj._convertNUm(_obj._convertTime(scale_value));
            var arryid = _obj.get("arryid");
            if (arryid.length > 0) {;
                for (var i = arryid.length - 1; i >= 0; i--) {
                    if (Math.abs(parseInt(timeiD) - parseInt(arryid[i])) <= 5) {
                        $scalebox.find('.scale.blue' + '[id=' + arryid[i] + ']').find('.border').addClass('show');
                    } else {
                        $scalebox.find('.scale.blue' + '[id=' + arryid[i] + ']').find('.border').removeClass('show');
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
                    if (oldContainer != container) {
                        oldContainer = container;
                    }
                },
                onDrop: function($item, container, _super) {
                    _super($item, container);
                    var id = _obj.get("newId");
                    if (id.toString().length > 0) {
                        var $scale = $(_obj.get("scalebox")).find('a[id=' + id + ']')
                        _obj._sortList($scale.find('.lesson-list'));
                        _obj._addScale($scale, $scale.find('.time').html(), true);
                    }
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
            console.log(scalejson);
            var $editbox = $(this.get('editbox'));
            var $subject_lesson_list = $(this.get('subject_lesson_list'));
            var $newscale = $('<a class="scale blue" id="' +scalejson.scaleid + '"><div class="border"></div><div class="scale-details"><ul class="lesson-list"></ul><div class="time">'+this._convertTime(scalejson.scaletime)+'</div></div></a>').css("left", scalejson.scaleleft).appendTo($editbox.find('.scalebox'));
            var $lesson_list = $newscale.find('.lesson-list');
            var subject =scalejson.subject;
            for (var i = 0; i < subject.length; i++) {
                console.log(subject[i].id);
                $subject_lesson_list.find(this.get('item')+'[data-id='+subject[i].id+']').find('.number .num').html(subject[i].ordinal);
                $subject_lesson_list.find(this.get('item')+'[data-id='+subject[i].id+']').appendTo($lesson_list);
            }
            var arry =  this.get('arryid').push(scalejson.scaleid);
            console.log(arry);
        },
        _sortList: function($list) {
            var num = 1;
            $list.find('.item-lesson').each(function() {
                $(this).find('.num').text(num);
                num++;
            });
        },
        _newSortList: function($list, _obj) {
            $list.sortable({
                distance: 20,
                itemSelector: '.item-lesson',
                onDrop: function(item, container, _super) {
                    _super(item, container);
                    _obj._sortList($list);
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
        _addScale: function($_scale, time, bool) {
            if (bool) {
                var $item_lesson = $_scale.find(this.get('item') + ':last');
                var id = $_scale.attr('id');
                // 最后一个孩子为新增的元素
                var scalejson = {
                    "scaleid": id,
                    "scaletime": this._convertSec(time),
                    "scaleleft":$_scale.css('left'),
                    "subject": [{
                        'id': $item_lesson.find(".idname").html(),
                        'ordinal': $item_lesson.find('.num').html()
                    }]
                };
                var tes = $.extend(scalejson, this.get("addScale")(scalejson));
                console.log(tes);
            }
        },
        _mergeScale: function(id, removeid) {
            // 合并时后台去处理顺序，被合并数按序号依次增加
            var scalejson = {
                "scaleid": id,
                "remove_scaleid": removeid
            };
            $.extend(scalejson, this.get("mergeScale")(scalejson));
        },
        _updateScale: function(id, newid, time) {
            var scalejson = {
                "scaleid": id,
                "new_scaleid": newid,
                "scaletime":this._convertSec(time)
            };
            $.extend(scalejson, this.get("updateScale")(scalejson));
        },
        _deleteScale: function(id, subjectid) {
            var scalejson = {
                "scaleid": id,
                "subjectid": subjectid
            };
            $.extend(scalejson, this.get("deleteScale")(scalejson));
        }
    });
    module.exports = DraggableWidget;
});