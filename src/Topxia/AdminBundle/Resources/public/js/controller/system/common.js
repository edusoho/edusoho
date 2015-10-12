define(function(require, exports, module) {
    require('jquery.sortable');

    $(".register-list").sortable({
        'distance': 20
    });

    $("#hide-list-btn").on("click", function() {
        $("#show-register-list").hide();

        var fieldNameHtml = '';
        $('.register-list input:checkbox:checked').each(function(){
            var fieldName = $(this).closest('li').text();
            fieldNameHtml += '<button type="button" class="btn btn-default btn-xs">'+$.trim(fieldName)+'</button>&nbsp;';
        })

        $('#show-list .pull-left').html(fieldNameHtml);
        $("#show-list").show();
    });

    $("#show-list-btn").on("click", function() {
        $("#show-register-list").show();
        $("#show-list").hide();
    });
});