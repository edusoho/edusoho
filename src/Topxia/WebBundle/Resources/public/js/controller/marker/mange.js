define(function(require, exports, module) {
    require('jquery.sortable');
    var Widget = require('widget');

    var DraggableWidget = Widget.extend({
        attrs: {
            item: '.simple_with_animation .item-lesson',
            placeholder: '.placeholder',
            time: '68',
            timecontent: '.coord',
            timescale: '.time-scale',
            timepartnum: '6',
            right_list: '#right-item-list',
            left_list: "#left-item-list",
            group_list: ".simple_with_animation",
            arryid: [],
            newId: '',
            isDraggable: 'false'
        },
        events: {
            'mousedown {{attrs.item}}': 'itemDraggable',
            'click .lesson-list .icon-close': 'itemRmove',
            'mousedown .question-scale.blue': 'slideScale'
        },
        setup: function() {
            this._initSortable();
            this._initTimeContent();
        },
        itemDraggable: function(e) {
            var $this = $(e.currentTarget);
            var _obj = this;
            var isMove = true;
            _obj.set('isDraggable', 'true');
            var num = 0;
            var offsetenter = $(".dashboard-content").offset().left + $(".dashboard-content").width();
            var $timecontent = $(_obj.get("timecontent"));
            var $times_cale = $(_obj.get("timescale"));
            var $right_list = $(this.element).find(_obj.get('right_list'));
            var $left_list = $(this.element).find(_obj.get('left_list'));
            var $list_item = $right_list.find(_obj.get('item'));
            var value = '<i class="es-icon es-icon-infooutline mrl"></i>' + "将题目拖至左侧时间条";

            var $scale_red = $timecontent.find("#default-scale");
            var $scale_red_details = $scale_red.find(".question-details");

            // 显示时间轴
            $scale_red.css("visibility", "visible");
            $scale_red_details.css("visibility", "visible");

            $(document).mousemove(function(event) {
                if (isMove) {
                    // :右边拖动交互
                    $right_list.find(_obj.get('placeholder')).html(value);

                    //鼠标进入右侧交互
                    //显示移动时间轴的位置
                    if (event.pageX > offsetenter) {
                        $scale_red.css("left", offsetenter - 20 - 1);
                    } else if (event.pageX < offsetenter && event.pageX > 20) {
                        $scale_red.css("left", event.pageX - 20 - 1);
                    } else if (event.pageX < 20) {
                        $scale_red.css("left", 0);
                    }
                    //显示移动时间轴的时间
                    var scale_left = parseInt($scale_red.css("left"));
                    var time = parseInt(_obj.get("time"));
                    var width = $(".dashboard-content").width();
                    var scale_value = Math.round(scale_left * time / width);
                    $scale_red_details.html(_obj._convertTime(scale_value));

                    // 查找5秒范围类的ID，提示合并效果
                    var timeiD = _obj._convertNUm(_obj._convertTime(scale_value));
                    var arryid = _obj.get("arryid");
                    if (arryid.length > 0) {;
                        for (var i = arryid.length - 1; i >= 0; i--) {
                            if (Math.abs(parseInt(timeiD) - parseInt(arryid[i])) <= 5) {
                                $times_cale.find('.question-scale.blue' + '[id=' + arryid[i] + ']').find('.border').addClass('show');
                            } else {
                                $times_cale.find('.question-scale.blue' + '[id=' + arryid[i] + ']').find('.border').removeClass('show');
                            }
                        }
                    }
                }
            }).mouseup(function() {
                // 停止拖动
                isMove = false;
                // 隐藏默认时间轴
                $scale_red.css("visibility", "hidden");
                $scale_red_details.css("visibility", "hidden");
                
                var timeiD = _obj._convertNUm($scale_red_details.html());
                var left = $scale_red.css("left");
                var arryid = _obj.get("arryid");

                if ($left_list.children().length > 0) {
                    var bool = true;
                    if (arryid.length > 0) {
                        // 遍历所有元素都与当前时间差，一旦遍历有时间挫小于5秒就跳出循环，认定为最接近元素。
                        for (var i = arryid.length - 1; i >= 0; i--) {
                            if(Math.abs(parseInt(timeiD) - parseInt(arryid[i])) <= 5) {
                                bool =false
                                timeiD = arryid[i];
                                break;
                            }
                        }
                        if(bool) {
                            // 遍历完成后为出现靠近元素，数组记录ID
                            arryid.push(timeiD); 
                        }
                    }else {
                        arryid.push(timeiD); 
                    }
                    if (bool) {

                        var $newscale = $('<a class="question-scale blue" id="' + timeiD + '"><div class="border"></div><div class="question-details"><ul class="lesson-list simple_with_animation"></ul></div></a>');
                        //获取到默认的时间轴内容，生成一个id的
                        $newscale.appendTo($timecontent.find('.time-scale'));
                        // 将拖放过来的li给这个新的newscale中的ul 
                        $left_list.children().appendTo($newscale.find(".simple_with_animation"));
                        $newscale.find(".simple_with_animation").after('<div class="time">' + $scale_red_details.html() + '</div>');
                        $newscale.css("left", left);
                        // 拖拽后排序
                        _obj.set("newId", timeiD);
                        // 注册内部item拖动事件:
                        var $list = $newscale.find(".simple_with_animation").sortable({
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
                        //注册滑块事件
                        // $newscale.
                    } else {
                        //相同直接获取存在的ID
                        var $_scale = $timecontent.find('.time-scale').find('a[id=' + timeiD + ']');
                        var $list = $_scale.find(".simple_with_animation");
                        var $item = $left_list.children();
                        $item.appendTo($list);
                        // 拖拽后排序
                        _obj.set("newId", timeiD);
                        //隐藏
                        $_scale.find('.border').removeClass('show')
                    }
                }
                console.log(arryid);
                // 拖动时间停止
                _obj.set('isDraggable', 'false');
            });
        },
        itemRmove: function(e) {
            $this = $(e.currentTarget);
            var num = $this.closest('ul').children().length;
            var $scale_red = $this.closest('.question-scale');
            var arrid = $this.closest('.question-scale').attr('id');
            var $list = $this.closest('ul');

            $this.closest('li').appendTo($(this.get("right_list")));
            this._sortList($list);
            // this._sortList($(this.get("right_list")));
            //判断当前子元数小于0移除蓝色的时间挫；
            if (num <= 1) {
                $scale_red.remove();
                // 清楚数组中保留的时间ID

                var arr = this.get("arryid");
                if (arr.length > 0) {
                    for (var i = arr.length - 1; i >= 0; i--) {
                        if (arr[i] == arrid) {
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
                var $times_cale = $(_obj.get("timescale"));
                var $time = $this.find(".question-details .time");
                var offsetenter = $(".dashboard-content").offset().left + $(".dashboard-content").width();
                var old_id = $this.attr('id');
                var isMove = true;
                $(document).mousemove(function(event) {
                    var left = 0;
                    if (isMove) {
                        if (event.pageX > offsetenter) {
                            left = offsetenter - 20 - 1;
                        } else if (event.pageX < offsetenter && event.pageX > 20) {
                            left = event.pageX - 20 - 1;
                        } else if (event.pageX < 20) {
                            left = 0;
                        }
                        $this.css('left', left);
                        //显示移动时间轴的时间
                        var totaltime = parseInt(_obj.get("time"));
                        var width = $(".dashboard-content").width();
                        var scale_value = Math.round(left * totaltime / width);
                        $time.css("visibility", "visible");
                        $time.html(_obj._convertTime(scale_value));

                        // 查找5秒范围类的ID，提示合并效果
                        var timeiD = _obj._convertNUm(_obj._convertTime(scale_value));
                        var arryid = _obj.get("arryid");
                        if (arryid.length > 0) {
                            for (var i = arryid.length - 1; i >= 0; i--) {
                                if (Math.abs(parseInt(timeiD) - parseInt(arryid[i])) <= 5) {
                                    $times_cale.find('.question-scale.blue' + '[id=' + arryid[i] + ']').find('.border').addClass('show');
                                } else {
                                    $times_cale.find('.question-scale.blue' + '[id=' + arryid[i] + ']').find('.border').removeClass('show');
                                }
                            }
                        }
                    }
                }).mouseup(function() {
                    isMove = false;
                    old_id = $this.attr('id');
                    $this.attr('id', _obj._convertNUm($this.find(".question-details .time").html()));
                    var newid = $this.attr('id');
                    // 判断是非移动，阻止点击时间造成的影响
                    if (newid != old_id) {
                        console.log("已移动");
                        var additem = true;
                        var mergeid = -1;
                        var arry = _obj.get("arryid");
                        if (arry.length > 0) {
                            for (var i = arry.length - 1; i >= 0; i--) {
                                if (arry[i] == old_id) {
                                    // 要循环所有将旧id删除，不能中途跳出循环
                                    arry.splice(i, 1);
                                }
                                if($this.attr('id') == arry[i]||Math.abs(parseInt($this.attr('id')) - parseInt(arry[i])) <= 5) {
                                    // 将合并到该目标div，数组无需纪录ID，自身隐藏
                                    additem = false;
                                    mergeid = arry[i];
                                    console.log("需要合并");
                                }
                            }
                        }
                        if (additem) {
                            arry.push($this.attr('id'));
                            console.log("需要添加:slideScale"+$this.attr('id'));
                        } else{
                            var $_scale = $times_cale.find('a[id=' + mergeid + ']');
                            if ($_scale.length > 0) {
                                $this.find('.simple_with_animation').children().appendTo($_scale.find('.simple_with_animation'));
                                _obj._sortList($_scale.find('.simple_with_animation'));
                                $this.remove();
                            }
                        }
                        $times_cale.find('.border').removeClass('show');
                        console.log(arry);
                    }
                });
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
                        var $list = $(_obj.get("timecontent")).find('a[id=' + id + ']').find('.simple_with_animation');
                        _obj._sortList($list);
                    }
                }
            });
        },
        _initTimeContent: function() {
            var _obj = this;
            var $_timecontent = $(_obj.get("timecontent"));
            var _width = $_timecontent.width();
            // 以秒为单位
            var _totaltime = _obj.get("time");
            var _partnum = _obj.get("timepartnum");

            if (_partnum > 0) {
                var _parttime = Math.round(_totaltime / _partnum);
                var _partwidth = Math.round(_width / _partnum);
                for (var i = 1; i <= _partnum; i++) {
                    var num = i * _parttime;
                    var time = _obj._convertTime(num);

                    $_timecontent.find(_obj.get("timescale")).append('<a style="left:' + i * _partwidth + 'px" data-toggle="tooltip" data-placement="top"' + 'title="' + time + '"></a>');
                }
                $('[data-toggle="tooltip"]').tooltip();
            }
        },
        _sortList: function($list) {
            var num = 1;
            $list.find('.item-lesson').each(function() {
                $(this).find('.num').text(num);
                num++;
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
    });
    module.exports = DraggableWidget;

});