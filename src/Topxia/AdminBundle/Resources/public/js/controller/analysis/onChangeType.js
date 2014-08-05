define(function(require, exports, module) {


    exports.forwordByType=function(){
    $("[name=analysisDateType]").on("change",function(){

                switch($("[name=analysisDateType]").val())
                {
                    case "register":
                    window.location.href=$("[name=register]").attr("value");break;
                    case "login":
                    window.location.href=$("[name=login]").attr("value");break;
                    case "course":
                    window.location.href=$("[name=course]").attr("value");break;
                    case "lesson":
                    window.location.href=$("[name=lesson]").attr("value");break;
                    case "joinLesson":
                    window.location.href=$("[name=lesson_join]").attr("value");break;
                    case "paidLesson":
                    window.location.href=$("[name=lesson_paid]").attr("value");break;
                    case "finishedLesson":
                    window.location.href=$("[name=lesson_finished]").attr("value");break;
                    case "videoViewed":
                    window.location.href=$("[name=video_viewed]").attr("value");break;
                    case "cloudVideoViewed":
                    window.location.href=$("[name=video_cloud_viewed]").attr("value");break;
                    case "localVideoViewed":
                    window.location.href=$("[name=video_local_viewed]").attr("value");break;
                    case "netVideoViewed":
                    window.location.href=$("[name=video_net_viewed]").attr("value");break;
                    case "income":
                    window.location.href=$("[name=income]").attr("value");break;
                    case "courseIncome":
                    window.location.href=$("[name=course_income]").attr("value");break;
                    case "exitLesson":
                    window.location.href=$("[name=lesson_exit]").attr("value");break;
                }

        });
    };
   

});