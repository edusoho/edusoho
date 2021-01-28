define(function(require, exports, module) {

        exports.autoSubmitCondition=function(){
            $("[name=analysisDateType]").on("change",function(){
                if($("[name=analysisDateType]").val()){
                    $("#btn-search").submit();
                }
            });

            if($("[name=startTime]").attr("value")==$("#btn-month").attr("currentMonthStart")&&$("[name=endTime]").attr("value")==$("#btn-month").attr("currentMonthEnd")){

                $("#btn-month").attr("class","btn btn-default active");
            }

            if($("[name=startTime]").attr("value")==$("#btn-lastMonth").attr("lastMonthStart")&&$("[name=endTime]").attr("value")==$("#btn-lastMonth").attr("lastMonthEnd")){

                $("#btn-lastMonth").attr("class","btn btn-default active");
            }

            if($("[name=startTime]").attr("value")==$("#btn-lastThreeMonths").attr("lastThreeMonthsStart")&&$("[name=endTime]").attr("value")==$("#btn-lastThreeMonths").attr("lastThreeMonthsEnd")){

                $("#btn-lastThreeMonths").attr("class","btn btn-default active");
            }

            $("#btn-month").on("click",function(){
                $("[name=startTime]").attr("value",$("#btn-month").attr("currentMonthStart"));
                $("[name=endTime]").attr("value",$("#btn-month").attr("currentMonthEnd"))
                $("#btn-search").submit();
            });

            $("#btn-lastMonth").on("click",function(){
                $("[name=startTime]").attr("value",$("#btn-lastMonth").attr("lastMonthStart"));
                $("[name=endTime]").attr("value",$("#btn-lastMonth").attr("lastMonthEnd"))
                $("#btn-search").submit();
            })   
            
            $("#btn-lastThreeMonths").on("click",function(){
                $("[name=startTime]").attr("value",$("#btn-lastThreeMonths").attr("lastThreeMonthsStart"));
                $("[name=endTime]").attr("value",$("#btn-lastThreeMonths").attr("lastThreeMonthsEnd"))
                $("#btn-search").submit();
            }) 


        }; 

});