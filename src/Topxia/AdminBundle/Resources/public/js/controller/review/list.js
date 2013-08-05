define(function(require, exports, module){
        
  exports.run = function(){

    $(".choose-all").click(function() {
      if( $(this).is(":checked") == true){
        $("input[name='review-item']").prop("checked", true);
      } else {
        $("input[name='review-item']").prop("checked", false);
      }
    });

    $(".review-delete").on('click', function(){
      var ids = [];
      $("input[name='review-item']:checked ").each(function() {
          ids.push($(this).attr("value"));
      });
      
      if(ids.length == 0){
        return ;
      }

      if (!confirm('真的要删除这些课程评价吗？')) {
        return ;
      }

      $.post($(this).data('url'), {ids:ids}, function(){
        window.location.reload();
      }); 
    });

  };

});