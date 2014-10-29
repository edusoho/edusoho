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
			var teacherSay = [];
            var questionIds = [];

            $teacherCheck = $('.question-teacher-say-input');

            $teacherCheck.parents().find('.teacher-say').each(function(index,item){
                var $item = $(item);
                teacherSay.push($item.val());
                questionIds.push($item.data('questionId'));
            });

            changeTeacherSay = {teacherSay:teacherSay,questionIds:questionIds};
		},

     	events:{
			'change #homework-teacherSay-select':'onChangeTeacherSaySelect',
     	},

		onChangeTeacherSaySelect: function (event) {
			var element = event.currentTarget;
			var elementText = $(element).find('option:selected').text();
			$('#homework-teacherSay-input').text(elementText)
			teacherFeedback = {teacherFeedback:elementText}; 
		}
     });

	var homeworkFootCheck = Widget.extend({

     	events:{
            'click #homework-check-btn': 'onClickCheckBtn',
     	},
        onClickCheckBtn: function(event) {
            var $btn = $(event.currentTarget);
                $btn.button('saving');
                $btn.attr('disabled', 'disabled');
            var data = $.extend(changeTeacherSay,teacherFeedback);
            $.post($btn.data('url'),{data:data},function(res){
                location.href= window.location.protocol+"//"+window.location.host+"/course/"+res.courseId+"/check/homework/reviewing/list";
            });
        }
	});

});