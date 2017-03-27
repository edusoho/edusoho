define(function (require, exports, module) {

    var Validator = require('bootstrap.validator');
    var messenger = require('./index.js');
    var DraggableWidget = require('./manage');
    var Cookie = require('cookie');
    require('common/validator-rules').inject(Validator);

    exports.run = function () {
        $form = $('.js-mark-from');
        var count = parseInt((document.body.clientHeight - 350) / 50) ? parseInt((document.body.clientHeight - 350) / 50) : 1;
        $.post($form.attr('action'), $form.serialize() + '&pageSize=' + count, function (response) {
            $('#subject-lesson-list').html(response);
            $('[data-toggle="popover"]').popover();
            if (!Cookie.get("MARK-MANGE-GUIDE")) {
                initIntro();
            } else {
                initDrag();
                $('#step-1').removeClass('introhelp-icon-help');

            }
            Cookie.set("MARK-MANGE-GUIDE", 'true', {expires: 360, path: "/"});
        });

        var validator = new Validator({
            element: $form,
            autoSubmit: false,
            autoFocus: false,
            onFormValidated: function (error, results, $form) {
                if (error) {
                    return;
                }
                var count = parseInt((document.body.clientHeight - 350) / 50) ? parseInt((document.body.clientHeight - 350) / 50) : 1;

                $.post($form.attr('action'), $form.serialize() + '&pageSize=' + count, function (response) {
                    $('#subject-lesson-list').html(response);
                });
            }
        });

        $(".js-marker-manage-content").on('change', 'select[name=target]', function () {
            var count = parseInt((document.body.clientHeight - 350) / 50) ? parseInt((document.body.clientHeight - 350) / 50) : 1;
            $.post($form.attr('action'), $form.serialize() + '&pageSize=' + count, function (response) {
                $('#subject-lesson-list').html(response);
            });
        })


        $(".js-marker-manage-content").on('click', '.js-question-preview', function (e) {
            $.get($(this).data('url'), function (response) {
                $('.modal').modal('show');
                $('.modal').html(response);
            })
        })

        var target = $('select[name=target]');
        $(".js-marker-manage-content").on('click', '.js-more-questions', function (e) {
            var $this = $(this).hide().parent().addClass('loading'),
                $list = $('#subject-lesson-list').css('max-height', $('#subject-lesson-list').height()),
                getpage = parseInt($this.data('current-page')) + 1,
                lastpage = $this.data('last-page');
            $.post($this.data('url') + getpage, {
                "target": target.val()
            }, function (response) {
                $this.remove();
                $list.append(response).animate({scrollTop: 40 * ($list.find('.item-lesson').length + 1)});
                if (getpage == lastpage) {
                    $('.js-more-questions').parent().remove();
                }
            });

        })

        $(".js-marker-manage-content").on('click', '.js-close-introhelp', function (e) {
            var $this = $(this);
            $this.closest('.show-introhelp').removeClass('show-introhelp');
            if ($('.show-introhelp').height() <= 0) {
                $('.js-introhelp-overlay').addClass('hidden');
                initDrag();
            }
        });

        function initIntro() {
            $('.js-introhelp-overlay').removeClass('hidden');
            $('.show-introhelp').addClass('show');
            var $img = $('.js-introhelp-img img'),
                img = document.createElement('img'),
                imgheight = $(window).height() - $img.offset().top - 80;
            img.src = $img.attr('src');
            left = imgheight * img.width / img.height / 2 + 50;
            $img.height(imgheight);
            $('.js-introhelp-img').css('margin-left', '-' + left + 'px');

        }
    };

    function initDrag(){
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
            messenger:messenger,
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
    }


});
