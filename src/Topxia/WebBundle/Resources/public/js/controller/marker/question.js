define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var DraggableWidget = require('../marker/mange');
    // 未避免初始化前端排序操作，将questionMarkers按生序方式返回，可省略questionMarkers.seq
    var initMarkerArry = [];
    var videoHtml = $('#lesson-dashboard');
    var courseId = videoHtml.data("course-id");
    var lessonId = videoHtml.data("lesson-id");
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
    var tempid = 0;

    var myDraggableWidget = new DraggableWidget({
        element: "#lesson-dashboard",
        initMarkerArry: initMarkerArry,
        _video_time: 139,
        addScale: function(markerJson, $marker, markers_array) {
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
                        if (markers_array[i].id == markerJson.questionMarkers[0].id) {
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
            $.post($(this).attr('href'), {
                "target": target.val()
            }, function(response) {
                $('#subject-lesson-list').html(response);
            })
        })

        $("#subject-lesson-list").on('click', '.drag .marker-preview', function() {
            $.get($(this).data('url'), function(response) {
                $('.modal').modal('show');
                $('.modal').html(response);

            })
        })
    };

});
