define(function(require, exports, module) {
    require('jquery.sortable');
    var Widget = require('widget');

    var DraggableWidget = Widget.extend({
        attrs: {
            item: 'li',
            placeholder: '.placeholder',
            time: '68',
            timecontent: '.coord',
            timepartnum: '6',
            right_list: '#right-item-list',
            left_list: "#left-item-list",
            group_list: ".simple_with_animation",
            arryid :[]
        },
        events: {
            'mousedown {{attrs.item}}': 'itemDraggable'
        },
        setup: function() {
            this._initSortable();
            this._initTimeContent();
        },
        itemDraggable: function(e) {
            var $this = $(e.currentTarget);
            var _obj = this;
            var isMove = true;
            var num = 0;
            var offsetenter = $(".dashboard-content").offset().left + $(".dashboard-content").width();
            var $timecontent = $(_obj.get("timecontent"));
            var $right_list = $(this.element).find(_obj.get('right_list'));
            var $left_list = $(this.element).find(_obj.get('left_list'));
            var $list_item = $right_list.find(_obj.get('item'));
            var value = '<i class="es-icon es-icon-infooutline mrl"></i>' + "将题目拖至左侧时间条";

            var $question_scale = $timecontent.find("#default-scale");
            var $question_scale_details = $question_scale.find(".question-details");

            // 显示时间轴
            $question_scale.css("visibility", "visible");
            $question_scale_details.css("visibility", "visible");

            $(document).mousemove(function(event) {
                if (isMove) {
                    // :右边拖动交互
                    $right_list.find(_obj.get('placeholder')).html(value);
                    // :鼠标进入右侧交互
                    //显示移动时间轴的位置
                    if (event.pageX > offsetenter) {
                        $question_scale.css("left", offsetenter - 20 - 1);
                    } else if (event.pageX < offsetenter && event.pageX > 20) {
                        $question_scale.css("left", event.pageX - 20 - 1);
                    } else if (event.pageX < 20) {
                        $question_scale.css("left", 0);
                    }
                    //显示移动时间轴的时间
                    var scale_left = parseInt($question_scale.css("left"));
                    var time = parseInt(_obj.get("time"));
                    var width = $(".dashboard-content").width();
                    var scale_value = Math.round(scale_left * time / width);

                    $question_scale_details.html(_obj._convertTime(scale_value));
                }
            }).mouseup(function() {
                // 隐藏默认时间轴
                // $question_scale.css("visibility", "hidden");
                $question_scale_details.css("visibility", "hidden");

                // 停止拖动
                isMove = false;

                var timeiD = _obj._convertNUm($question_scale_details.html());
                console.log(timeiD);



                var left = $question_scale.css("left");
                var arryid = _obj.get("arryid");

                if ($left_list.children().length > 0){
                    var bool = false;
                    // 5秒以内一个弹题
                    
                    if(arryid.length>0 ) {
                        console.log(arryid);
                        for (var i = arryid.length - 1; i >= 0; i--) {
                            if(arryid[i]!=timeiD  ) {
                                arryid.push(timeiD);
                                bool = true;
                            }
                            if(Math.abs(parseInt(timeiD)-parseInt(arryid[i])) <= 5) {
                                bool = false;
                                timeiD = arryid[i];
                            }
                        };
                    }else {
                        bool = true;
                        arryid.push(timeiD);
                    }
                    if(bool) {
                        var $newscale = $('<a class="question-scale blue" id="' + timeiD + '"><div class="question-details"><ul class="lesson-list simple_with_animation"></ul></div></a>');
                        //获取到默认的时间轴内容，生成一个id的
                        $newscale.appendTo($timecontent.find('.time-scale')); 
                        // 将拖放过来的li给这个新的newscale中的ul 
                        $left_list.children().appendTo($newscale.find(".simple_with_animation"));
                        $newscale.find(".simple_with_animation").after('<div class="time">'+$question_scale_details.html()+'</div>');
                        $newscale.css("left",left);
                    }else {
                        //相同直接获取存在的ID
                        var $_scale = $timecontent.find('.time-scale').find('a[id='+timeiD+']');
                        console.log($_scale);
                         $left_list.children().appendTo($_scale.find(".simple_with_animation"));
                    }
                    
                }
                $list_item.each(function(index, dom) {
                    $(dom).find(".num").text(index);
                });

                
            });
        },
        _initSortable: function() {
            var _obj = this;
            var _classname = $(_obj.element).find(_obj.get('group_list'));
            // var $list =  $(_classname).sortable({
            //     distance: 20,
            //     itemSelector: '.item-lesson',
            //     onDrop: function (item, container, _super) {
            //         _super(item, container);
            //         _obj._sortList($list);
            //     },
            //     serialize: function(parent, children, isContainer) {
            //         return isContainer ? children : parent.attr('id');
            //     },
            //     isValidTarget:function (item, container) {
            //         if(item.siblings('li').length){ 
            //             return true;
            //         }else{
            //             return false;
            //         }
            //     }
            // });
            $(_classname).sortable({
                group: _classname,
                pullPlaceholder: false,
                // animation on drop
                onDrop: function($item, container, _super) {
                    var $clonedItem = $('<li/>').css({
                        height: 0
                    });
                    $item.before($clonedItem);
                    $clonedItem.animate({
                        'height': $item.height()
                    });

                    $item.animate($clonedItem.position(), function() {
                        $clonedItem.detach();
                        _super($item, container);
                    });
                },
                // set $item relative to cursor position
                onDragStart: function($item, container, _super) {
                    var offset = $item.offset(),
                        pointer = container.rootGroup.pointer;
                    adjustment = {
                        left: pointer.left - offset.left,
                        top: pointer.top - offset.top
                    };
                    _super($item, container);
                },
                onDrag: function($item, position) {
                    $item.css({
                        left: position.left - adjustment.left,
                        top: position.top - adjustment.top
                    });
                }
            });
        },
        _initTimeContent: function() {
            var _obj = this;
            // 以秒为单位
            var $_timecontent = $(_obj.get("timecontent"));
            var _width = $_timecontent.width();
            var _totaltime = _obj.get("time");
            var _partnum = _obj.get("timepartnum");

            if (_partnum > 0) {
                var _parttime = Math.round(_totaltime / _partnum);
                var _partwidth = Math.round(_width / _partnum);
                for (var i = 1; i <= _partnum; i++) {
                    var num = i * _parttime;
                    var time = _obj._convertTime(num);

                    $_timecontent.find('.time-scale').append('<a style="left:' + i * _partwidth + 'px" data-toggle="tooltip" data-placement="top"' + 'title="' + time + '"></a>');
                }
                $('[data-toggle="tooltip"]').tooltip();
            }
        },
        _sortList: function($list) {
            // var data = $list.sortable("serialize").get();
            // console.log(data);
            // $.post($list.data('sortUrl'), {ids:data}, function(response){
            //     var lessonNum = chapterNum = unitNum = 0;

            //     $list.find('.item-lesson').each(function() {
            //         var $item = $(this);
            //         if ($item.hasClass('item-lesson')) {
            //             lessonNum ++;
            //             $item.find('.num').text(lessonNum);
            //         } else if ($item.hasClass('item-chapter-unit')) {
            //             unitNum ++;
            //             $item.find('.num').text(unitNum);
            //         } else if ($item.hasClass('item-chapter')) {
            //             chapterNum ++;
            //             unitNum = 0;
            //             $item.find('.num').text(chapterNum);
            //         }

            //     });
            // });
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
            if(arr.length>0) {
                for (var i = 0; i < arr.length; i++) {
                   string += arr[i];
                };
            }
            return string;
        },
    });
    module.exports = DraggableWidget;

});