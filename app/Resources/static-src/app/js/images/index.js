$(function(){
    $("#commonImages").on("click",".pagination li",function(){
        let url = $("#commonImages").data("url");
        let conditions = "page="+$(this).data("page");
        $.ajax({
            type: 'GET',
            url: url,
            data: conditions
        }).done(function(resp){
            $(".ul-context").html(resp);
            init();
        }).fail(function(){
            console.log("fail");
        });
    })
})