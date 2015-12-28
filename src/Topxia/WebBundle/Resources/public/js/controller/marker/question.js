define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var DraggableWidget = require('../marker/mange');
    // 未避免初始化前端排序操作，将questionMarkers按生序方式返回，可省略questionMarkers.seq
    var initMarkerArry =[];
    var videoHtml = $('#lesson-dashboard');
    var courseId = videoHtml.data("course-id");
    var lessonId = videoHtml.data("lesson-id");

    $.ajax({ 
      type: "get", 
      url: $('.toolbar-question-marker').data('marker-metas-url'), 
      cache:false, 
      async:false, 
      success:function(data){
        initMarkerArry = data;
      }
    });

    var tempid = 0;

    var myDraggableWidget = new DraggableWidget({
        element: "#lesson-dashboard",
        initMarkerArry:initMarkerArry,
        addScale: function(markerJson,$marker) {
            // console.log(markerJson);
            // console.log("markerJson.id"+markerJson.id);
            // console.log("markerJson.second:   "+markerJson.second);
            // console.log("markerJson.position:   "+markerJson.position);
            // console.log("markerJson.questionMarkers.id:   "+markerJson.questionMarkers[0].id);
            // console.log("markerJson.questionMarkers.seq:   "+markerJson.questionMarkers[0].seq);
            // console.log("markerJson.questionMarkers.questionId:   "+markerJson.questionMarkers[0].questionId);
            // console.log(markerJson);
            var url = $('.toolbar-question-marker').data('queston-marker-add-url');
            var param = {
                markerId:markerJson.id,
                second:markerJson.second,
                questionId:markerJson.questionMarkers[0].questionId,
                seq:markerJson.questionMarkers[0].seq
            };
            $.post(url,param,function(data){
                if(data.id == undefined) {
                    return ;
                }
                if(markerJson.id == undefined) {
                    console.log("新增ID");
                    markerJson.id = data.markerId;
                    $marker.attr('id',data.markerId);
                }
                // 返回题目的ID
                if(markerJson.questionMarkers[0].id == undefined) {
                    markerJson.questionMarkers[0].id = tempid;
                    $marker.find('.item-lesson[question-id='+markerJson.questionMarkers[0].questionId+']').attr('id',data.id);
                }
                tempid++;
            });
            console.log(markerJson);

            // if(true) {//成功回调
            //     //如果时间轴ID为空，为新增加的时间轴需要返回的ID
            //     if(markerJson.id == undefined) {
            //         markerJson.id = tempid;
            //         $marker.attr('id',markerJson.id)
            //     }
            //     // 返回题目的ID
            //     if(markerJson.questionMarkers[0].id == undefined) {
            //         markerJson.questionMarkers[0].id = tempid;
            //         $marker.find('.item-lesson[question-id='+markerJson.questionMarkers[0].questionId+']').attr('id',markerJson.questionMarkers[0].id);
            //     }
            //     tempid++;
            //     console.log("markerJson.id"+markerJson.id);
            //     console.log("markerJson.questionMarkers.id:   "+markerJson.questionMarkers[0].id);
            // }
            // else {
            //     // 失败将li恢复到原位
            //     //判断是否移除时间轴:如果当前为第一个li则需要删除时间轴
            //     // 无需重新排序：添加总是为最后一个所以直接移除无需再重新排序
            // }
            // var url = $('.toolbar-question-marker').data('queston-marker-add-url');
            // $.post(url,{
            //     questionId:scalejson.subject[0].id,
            //     second:scalejson.scaletime,
            //     markerId:scalejson.markerId
            //     //{question_marker_id}

            // },function(data){
            //     scalejson.markerId=data.markerId;
            //     scalejson.questionMarkerId='';
            //     console.log(scalejson);
            // });
            return markerJson;
        },
        mergeScale: function(markerJson,$marker,$merg_emarker,childrenum) {
            // console.log(markerJson);
            // console.log("markerJson.id"+markerJson.id);
            // console.log("markerJson.merg_id:   "+markerJson.merg_id);
            var url = $('.toolbar-question-marker').data('marker-merge-url');

            $.post(url,{sourceMarkerId:markerJson.id,targetMarkerId:markerJson.merg_id},function(data){
                
                //console.log(scalejson);
            });

            // if(true) {
            //     console.log(markerJson);
            //     //后台需要将，markerJson.id中的所有题目移动到markerJson.merg_id中，如两id都分别有2题，那被移动题目的序号由1，2变为3，4
            //     //成功回调，将$marker真移除
            //     $marker.remove();
            // }else {
            //     // 将merg_emarker中list的最后面取childrenum个子元素放回$marker中的list，并将$marker重新排序
            // }
            return markerJson;
        },
        updateScale: function($marker,markerJson,old_position,old_time) {
            // console.log(markerJson);
            // console.log("markerJson.id"+markerJson.id);
            // console.log("markerJson.second:   "+markerJson.second);
            // console.log("markerJson.position:   "+markerJson.position);
            

            var url = $('.toolbar-question-marker').data('marker-update-url');

            console.log($marker);
            
            console.log(markerJson);
            var param = {
                id:markerJson.id,
                second:markerJson.second
            };
            $.post(url,param,function(data){
                
                console.log(scalejson);
            });

            // if(true) {
            //     //成功回调，后台直接修改数据即可
            // }else {
            //     // 前台：将时间轴移动回原来的位置并改变时间轴的时间
            // }
            return markerJson;
        },
        deleteScale: function(markerJson,$marker,$marker_list_item) {
            // console.log(markerJson);
            // console.log("markerJson.id"+markerJson.id);
            // console.log("markerJson.questionMarkers.id:   "+markerJson.questionMarkers[0].id);
            // console.log("markerJson.questionMarkers.seq:   "+markerJson.questionMarkers[0].seq);
            // console.log("markerJson.questionMarkers.questionId:   "+markerJson.questionMarkers[0].questionId);
            //  var url = $('.toolbar-question-marker').data('queston-marker-delete-url');

            // $.post(url,{questionId:20},function(data){
                
            //     console.log(scalejson);
            // });

            var url = $('.toolbar-question-marker').data('queston-marker-delete-url');

            $.post(url,{questionId:markerJson.questionMarkers[0].id},function(data){
                //if(data==true){
                    if($marker.is(":hidden")) {
                        $marker.remove();
                    }
                //}
                //console.log(scalejson);
            });


            // if(true) {
            //     //后台需注意：移除题目后前台已经重新排序，后台数据的序号也需要改变：将当前移除的题目序号后的题目序号依次加一
            //     // 成功回调

            //     console.log($marker);
            //     console.log($marker_list_item);
            //     //判断$marker是否hide，如果hide需要直接移除{时间轴上的所有题目已经移除}
            //     if($marker.is(":hidden")) {
            //         $marker.remove();
            //     }
            // }
            // else {
            //     // 将？$marker_list_item放回到$marker中，并将$marker显示；
            //     // 如果list中item数量大于1，而且？$marker_list_item不是最后一个孩子需要重新排序
            // }
        },
        updateSeq:function($scale,markerJson) {
            console.log(markerJson);
            var url = $('.toolbar-question-marker').data('queston-marker-sort-url');

            // var markerJson = {
            //     "id": $marker.attr('id'),
            //     "questionMarkers": [{
            //         "id": questionMarkers_id,
            //         "seq": seq,
            //         "new_seq": new_seq
            //     }]
            // }  
            if(true) {
                // 后台： 更据当前的序号编号，重新排序
            }else {
                //撤销排序
            }  
        }
    })
    exports.run = function() {
        $form = $('.mark-from');
        var validator = new Validator({
            element: $form,
            autoSubmit: false,
            autoFocus: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return;
                }
                $.post($form.attr('action'), $form.serialize(), function(response) {
                    $('.question').html(response);
                });

            }
        });

        $(".pagination a").on('click', function(e) {
            e.preventDefault();
            $.get($(this).attr('href'), function(response) {
                $('.question').html(response);
            })
        })

        $("#subject-lesson-list").on('click', '.marker-preview', function() {
            $.get($(this).data('url'), function(response) {
                $('.modal').modal('show');
                $('.modal').html(response);

            })
        })
    }
});