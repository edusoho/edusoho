define(function(require, exports, module) {

    var Widget = require('widget');
    var Validator = require('bootstrap.validator');
    var saveModule = require('../../../../homework/js/controller/homework/save.js');
    var InitIndexActiveModule = require('../../../../homework/js/controller/homework/active.js');
    require('ckeditor');
    var Notify = require('common/bootstrap-notify');
    var changeAnswers = {};
    var changeTeacherSay = {};

    Validator.addRule(
        'maxScoreCheck',
        function (options) {
            var field=options.element;
            return field.data('maxScore')>=field.val();
        }, "评分不能超过题目分数"
    );

    exports.run = function() {
        var validator = new Validator({
            element: '#review-form',
            triggerType: 'change',
            onFormValidated: function(error){
                // if (error) {
                //     return false;
                // }
                // $('#course-create-btn').button('submiting').addClass('disabled');
            }
        });

        $('input.score').each(function(index,item){
            var maxScore=$(item).data('maxScore');
            if(maxScore && parseInt(maxScore)>0){
                validator.addItem({
                    element: '[name="'+$(item).attr('name')+'"]',
                    required: true,
                    rule: 'maxScoreCheck'
                });
            }
        });

         var View = Widget.extend({
            setup: function() {

            },

            events: {
                'click #submit-review-btn': 'onSubmitReview',
                'click #submit-pair-review-btn': 'onSubmitPairReview',
                'click #randomize-next-pair-review-btn': 'onRandomizeNextPairReview',
                'click .question-index': 'onClickSetCard'
            },

            onSubmitReview: function(event) {
            },

            onRandomizeNextPairReview: function(event){
                if (!confirm('确认要放弃当前作业互评，随机挑选一份新的作业互评么？')) return false;
                var btn = $(event.currentTarget);
                location.href = btn.data('url');
            },

            onSubmitPairReview: function(event){
                var btn = $(event.currentTarget);

                validator.execute();
                if($('.text-danger').length>0){
                    return false;
                }
                
                var items=[];
                $('.score').each(function(index,item){
                    var field=$(item);
                    var reviewItem={};
                    reviewItem.homeworkItemResultId=field.data('itemId');
                    reviewItem.score=field.val();
                    var selector=$('[name=review\\['+reviewItem.id+'\\]]');
                    if(selector.length>0){
                        reviewItem.review = selector.val();
                    }
                    items.push(reviewItem);
                });

                $.post(btn.data('url'),{data:{items: items}},function(res){
                    location.href = btn.data('goto');
                    // location.href= window.location.protocol+"//"+window.location.host+"/course/"+res.courseId+"/check/homework/reviewing/list";
                });
            },

           onClickSetCard: function(event) {
                var position = $('.question-'+$(event.currentTarget).data('anchor')).offset();
                $(document).scrollTop(position.top-10);
            }
        });       

        var v = new View({
            element: '#homework-set'
        }).render();

        $('#modal').on('show.bs.modal', function (e) {
            validator.execute();

            if($('.text-danger').length>0){
                e.preventDefault();
                return false;
            }
        })
    };
});