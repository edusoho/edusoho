define(function(require, exports, module) {

    var ThreadShowWidget = require('../../../../topxiaweb/js/controller/thread/thread-show.js');
    
    exports.run = function() {

        var threadShowWidget = new ThreadShowWidget({
            element: '.class-detail-content'
        });

        var $onlyTeacherBtnHtml = $('.js-only-teacher-div').html();
        threadShowWidget.element.find('.js-all-post-head').append($onlyTeacherBtnHtml);
        
        threadShowWidget.element.on('click','.js-only-teacher',function(){
            var $self = $(this);
            var $filter = $self.hasClass('active')? '' : '?adopted=1';
            var $url = $self.data('url')+$filter;
            document.location.href = $url;
        });

        var $userIds = '';
        threadShowWidget.element.find('.thread-post').each(function(){
            $userIds+=$(this).data('userId')+',';
        });
        $userIds = $userIds.substring(0,$userIds.length-1);
        $.get($('#isTeachersUrl').val()+'?ids='+$userIds,function(ids){
            var $idArray = ids.split(',');
            for (var i = 0; i < $idArray.length ; i++) {
                threadShowWidget.element.find('.user-id-'+$idArray[i]).each(function(){
                    $(this).addClass('teacher');
                });
            };
        });




    };

});