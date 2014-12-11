define(function(require, exports, module) {
    
    var learnerShow = {
        lessonId : '',
        initShow: function(){
            url = "../../course/lesson/"+learnerShow.lessonId+'/learn/show';
            $.post(url,function(html){
                $('.lesson-learner-show').append(html);
            });
        },

        changeShow: function(){
            url = "../../course/lesson/"+learnerShow.lessonId+'/learn/show';
            $.post(url,function(html){
                $('.lesson-learner-show').children('.row').replaceWith(html);
            });
        },
    }
    module.exports = learnerShow;
});