define(function(require, exports, module) {

  exports.run = function() {
 
    var resizeTimer;

    $(".js-search").focus(function () {
        $(this).prop("placeholder", "搜索").addClass("active");
    }).blur(function () {
        $(this).prop("placeholder", "").removeClass("active");
    });

   }

});