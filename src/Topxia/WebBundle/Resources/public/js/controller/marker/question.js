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
      url: $('.toolbar-question-marker').data('marker-metas-url'), 
      cache:false, 
      async:false, 
      success:function(data){
        initMarkerArry = data.markersMeta;
        mediaLength =data.videoTime;
      }
    });
    var tempid = 0;

    var myDraggableWidget = new DraggableWidget({
        element: "#lesson-dashboard",
        initMarkerArry:initMarkerArry,
        videotime:mediaLength,
        addScale: function(markerJson,$marker) {
            var url = $('.toolbar-question-marker').data('queston-marker-add-url');
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
                if (markerJson.id == undefined) {
                    markerJson.id = data.markerId;
                    $marker.attr('id', data.markerId);
                }
                //显示时间轴
                if ($marker.is(":hidden")) {
                    $marker.show();
                }
                // 返回题目的ID
                if (markerJson.questionMarkers[0].id == undefined) {
                    $marker.find('.item-lesson[question-id=' + markerJson.questionMarkers[0].questionId + ']').attr('id', data.id);
                }
            });
            return markerJson;
        },
        mergeScale: function(markerJson, $marker, $merg_emarker, childrenum) {
            var url = $('.toolbar-question-marker').data('marker-merge-url');

            $.post(url, {
                sourceMarkerId: markerJson.id,
                targetMarkerId: markerJson.merg_id
            }, function(data) {
                // 删除被合并的marker
                if ($marker.is(":hidden")) {
                    $marker.remove();
                }
                //删除驻点
            });
            return markerJson;
        },
        updateScale: function($marker, markerJson, old_position, old_time) {
            var url = $('.toolbar-question-marker').data('marker-update-url');

            var param = {
                id: markerJson.id,
                second: markerJson.second
            };
            $.post(url, param, function(data) {
                //删除驻点
            });

            return markerJson;
        },
        deleteScale: function(markerJson, $marker, $marker_list_item) {
            var url = $('.toolbar-question-marker').data('queston-marker-delete-url');
            $.post(url, {
                questionId: markerJson.questionMarkers[0].id
            }, function(data) {
                if ($marker.is(":hidden")) {
                    $marker.remove();
                    //查找
                }
            });
        },
        updateSeq: function($scale, markerJson) {
            if (markerJson == undefined || markerJson.questionMarkers == undefined || markerJson.questionMarkers.length == 0) {
                return;
            }

            var url = $('.toolbar-question-marker').data('queston-marker-sort-url');
            param = new Array();

            for (var i = 0; i < markerJson.questionMarkers.length; i++) {
                param.push(markerJson.questionMarkers[i].id);
            }

            $.post(url, {questionIds: param});
        }
    });

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