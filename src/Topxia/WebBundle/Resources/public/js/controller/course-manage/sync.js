define(function(require, exports, module) {

    exports.run = function() {
        onEdit();
    };

    onEdit = function(){
        $('.btn-success').on('click',function(){
              var url = $(".btn-success").data("url");
              document.location.href = url;
        })
    }

});