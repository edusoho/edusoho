define(function(require, exports, module) {
    require('jquery.sortable');

    $(".register-list").sortable({
        'distance': 20
    });

    $("#hide-list-btn").on("click", function() {
        $("#show-register-list").hide();
        $("#show-list").show();
    });

    $("#show-list-btn").on("click", function() {
        $("#show-register-list").show();
        $("#show-list").hide();
    });
});