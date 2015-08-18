define(function(require, exports, module) {
    var Widget = require('widget');
	var teacherFeedback = {};
    exports.run = function() {
        var homeworkCheckBody = new homeworkBodyCheck({
            element: '#homework-check-body'
        });

		var homeworkCheckFoot = new homeworkFootCheck({
            element: '#homework-check-foot'
        });
    };

     var homeworkBodyCheck = Widget.extend({

		setup: function() {
            var items=[];
            $('.score').each(function(index,item){
                var field=$(item);
                var reviewItem={};
                reviewItem.questionId=field.data('questionId');
                reviewItem.score=field.val();
                var selector=$('[name=review\\['+reviewItem.questionId+'\\]]');
                if(selector.length>0){
                    reviewItem.review = selector.val();
                }
                items.push(reviewItem);
            });

            homeworkReview = {items: items};
		},

     	events:{
			'change #homework-teacherSay-select':'onChangeTeacherSaySelect'
     	},

		onChangeTeacherSaySelect: function (event) {
			var element = event.currentTarget;
			var elementText = $(element).find('option:selected').text();
			$('#homework-teacherSay-input').text(elementText);
		}
     });

	var homeworkFootCheck = Widget.extend({

     	events:{
            'click #homework-check-btn': 'onClickCheckBtn'
     	},
        onClickCheckBtn: function(event) {
            var $btn = $(event.currentTarget);
                $btn.button('saving');
                $btn.attr('disabled', 'disabled');

            elementText = $('#homework-teacherSay-input').val();
            teacherFeedback = {teacherFeedback:elementText};
            var data = $.extend(homeworkReview,teacherFeedback);
            $.post($btn.data('url'),{data:data},function(res){
                location.href = $btn.data('goto');
                // location.href= window.location.protocol+"//"+window.location.host+"/course/"+res.courseId+"/check/homework/reviewing/list";
            });
        }
	});

});