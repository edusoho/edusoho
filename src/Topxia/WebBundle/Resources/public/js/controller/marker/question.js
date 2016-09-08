define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var DraggableWidget = require('../marker/mange');

    require('jquery.intro');
    require('jquery.intro-css');
    var Cookie = require('cookie');

    // 未避免初始化前端排序操作，将questionMarkers按生序方式返回，可省略questionMarkers.seq
    var initMarkerArry = [];
    var mediaLength = 30;
    $.ajax({
        type: "get",
        url: $('.js-pane-question-content').data('marker-metas-url'),
        cache: false,
        async: false,
        success: function(data) {
            initMarkerArry = data.markersMeta;
            mediaLength = data.videoTime;
        }
    });

    var myDraggableWidget = new DraggableWidget({
        element: "#lesson-dashboard",
        initMarkerArry: initMarkerArry,
        _video_time: mediaLength,
        addScale: function(markerJson, $marker, markers_array) {
            console.log(markerJson);
            var url = $('.js-pane-question-content').data('queston-marker-add-url');
            var param = {
                markerId: markerJson.id,
                second: markerJson.second,
                questionId: markerJson.questionMarkers[0].questionId,
                seq: markerJson.questionMarkers[0].seq
            };
            $.post(url, param, function(data) {
                if (data.id == undefined) {
                    return;
                }
                //新增时间刻度
                if (markerJson.id == undefined) {
                    $marker.attr('id', data.markerId);
                    markers_array.push({ id: data.markerId, time: markerJson.second });
                    //排序

                }
                $marker.removeClass('hidden');
                $marker.find('.item-lesson[question-id=' + markerJson.questionMarkers[0].questionId + ']').attr('id', data.id);
            });
            return markerJson;
        },
        mergeScale: function(markerJson, $marker, $merg_emarker, markers_array) {
            var url = $('.js-pane-question-content').data('marker-merge-url');
            $.post(url, {
                sourceMarkerId: markerJson.id,
                targetMarkerId: markerJson.merg_id
            }, function(data) {
                $marker.remove();
                for (i in markers_array) {
                    if (markers_array[i].id == markerJson.id) {
                        markers_array.splice(i, 1);
                        break;
                    }
                }
                console.log(markers_array);
            });
            return markerJson;
        },
        updateScale: function(markerJson, $marker) {
            var url = $('.js-pane-question-content').data('marker-update-url');
            var param = {
                id: markerJson.id,
                second: markerJson.second
            };
            $.post(url, param, function(data) {});
            return markerJson;
        },
        deleteScale: function(markerJson, $marker, $marker_question, marker_questions_num, markers_array) {
            var url = $('.js-pane-question-content').data('queston-marker-delete-url');
            $.post(url, {
                questionId: markerJson.questionMarkers[0].id
            }, function(data) {
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
        updateSeq: function($scale, markerJson) {
            if (markerJson == undefined || markerJson.questionMarkers == undefined || markerJson.questionMarkers.length == 0) {
                return;
            }

            var url = $('.js-pane-question-content').data('queston-marker-sort-url');
            param = new Array();

            for (var i = 0; i < markerJson.questionMarkers.length; i++) {
                param.push(markerJson.questionMarkers[i].id);
            }

            $.post(url, { questionIds: param });
        }
    });

    function sortList($list) {
        myDraggableWidget._sortList($list);
    }


    exports.run = function() {
        $form = $('.mark-from');
        $.post($form.attr('action'), $form.serialize(), function(response) {
            $('#subject-lesson-list').html(response);
            $('[data-toggle="popover"]').popover();
            initIntro();
            if(!Cookie.get("marker-manage-guide")){
                initIntro();
            } 
            Cookie.set("marker-manage-guide",'true',{expires:360,path:"/"});
        });
        var validator = new Validator({
            element: $form,
            autoSubmit: false,
            autoFocus: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return;
                }
                $.post($form.attr('action'), $form.serialize(), function(response) {
                    $('#subject-lesson-list').html(response);
                });
            }
        });
        var target = $('select[name=target]');

        $("#subject-lesson-list").on('click', '.pagination a', function(event) {
            event.preventDefault();
            console.log(target.val());
            $.post($(this).attr('href'), {
                "target": target.val()
            }, function(response) {
                $('#subject-lesson-list').html(response);
            })
        })

        $(".marker-manage").on('click', '.js-question-preview', function(e) {
            $.get($(this).data('url'), function(response) {
                $('.modal').modal('show');
                $('.modal').html(response);
            })
        })  

        var target = $('select[name=target]');
        $(".marker-manage").on('click', '.js-more-questions', function(e) {
            var $this = $(this).hide().parent().addClass('loading'),
                $list = $('#subject-lesson-list').css('max-height',$('#subject-lesson-list').height()),
                getpage = parseInt($this.data('current-page'))+1,
                lastpage =$this.data('last-page');
            $.post($this.data('url')+getpage, {
                "target": target.val()
            }, function(response) {
                $this.remove();
                $list.append(response).animate({scrollTop:40*($list.find('.item-lesson').length+1)});
                if(getpage == lastpage) {
                    $('.js-more-questions').parent().remove();
                }
            });

        })

        function initIntro() {
            var $list = $('#subject-lesson-list'),
                imgurl =$list.attr('data-intro-img'),
                imgheight = $(window).height()-$list.find('li:first-child').offset().top - 210,
                imgleft = (158*imgheight/286+70) > 0 ?(158*imgheight/286+70) : 0,
                introimg = "<div class='intro-img' style='left:-"+imgleft/2+"px'><img  src=" + imgurl + " style='height:" + imgheight + "px'>[视频编辑区域]</div>",
                intro = introJs();

            $('#step-1').addClass('color-warning');
            intro.setOptions({
                nextLabel: '知道了',
                doneLabel: '知道了',
                prevLabel: '上一步',
                hidePrev: true,
                showBullets: false,
                steps: [{
                    element: '#step-1',
                    intro: "为保险起见，所有对视频的编辑操作都将实时保存。",
                    position: 'bottom-middle-aligned',
                }, {
                    element: $list.find('li:first-child .icon-drag')[0],
                    intro: "<p class='title'>添加随堂练习，只需一步操作</p><div class='remask'>将题目拖拽到<span class='color-warning'>「视频编辑区域」</span></div>" + introimg,
                    position: 'left',
                }]
            }).start().onchange(function() {
                $('.introjs-skipbutton').css('display', 'inline-block');
                $('.introjs-prevbutton').css('display', 'none');
                $('#step-1').removeClass('color-warning');
            })
        }
    };

});
