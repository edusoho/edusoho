define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var DraggableWidget = require('../marker/mange');
    var scalejson = {
        "scaleid": '0023',
        "scaletime": '23',
        "scaleleft":'310px',
        "subject": [{
            'id': '1',
            'ordinal': '1'
        },{
            'id': '2',
            'ordinal': '2'
        }]
    };

    var videoHtml = $('#lesson-dashboard');
    var courseId = videoHtml.data("course-id");
    var lessonId = videoHtml.data("lesson-id");

    var myDraggableWidget = new DraggableWidget({
        element: "#lesson-dashboard",
        initscale:scalejson,
        addScale: function(scalejson) {
            var url = $('.toolbar-question-marker').data('queston-marker-add-url');

            $.post(url,{
                questionId:scalejson.subject[0].id,
                second:scalejson.scaletime,
                markerId:scalejson.markerId
            },function(data){
                scalejson.markerId=data.markerId;
                console.log(scalejson);
            });
            
            return scalejson;
        },
        mergeScale: function(scalejson) {
            console.log(scalejson);
            return scalejson;
        },
        updateScale: function(scalejson) {
            console.log(scalejson);
            return scalejson;
        },
        deleteScale: function(scalejson) {
            console.log(scalejson);
            return scalejson;
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

        $(".question-tr").on('click', '.marker-preview', function() {
            $.get($(this).data('url'), function(response) {
                $('.modal').modal('show');
                $('.modal').html(response);

            })
        })



    }
});