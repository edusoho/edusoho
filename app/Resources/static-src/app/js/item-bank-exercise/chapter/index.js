class ChapterToogle {

  constructor() {
    this.toogle();
  }

  toogle(){
    let $th = $('#btn_fath');
    var ele = $th.children('.move');
    $th.click(function (event) {
      if(ele.attr('data-state') == 'on'){
        ele.animate({left: '0'}, 300, function(){
          ele.attr('data-state', 'off');
        });
        $th.removeClass('on').addClass('off');
        $.ajax({
          type: 'GET',
          url: $('#openUrl').val(),
          contentType: 'application/x-www-form-urlencoded;charset=utf-8',
          data: {exerciseId:$('#exerciseId').val(),chapterEnable:'off'},
          dataType: 'json',
          success: function(data){
            location.reload();
          }
        });
      }else if(ele.attr('data-state') == 'off'){
        ele.animate({left: '30px'}, 300, function(){
          $(this).attr('data-state', 'on');
        });
        $th.removeClass('off').addClass('on');
        $.ajax({
          type: 'GET',
          url: $('#openUrl').val(),
          contentType: 'application/x-www-form-urlencoded;charset=utf-8',
          data: {exerciseId:$('#exerciseId').val(),chapterEnable:'on'},
          dataType: 'json',
          success: function(data){
            location.reload();
          }
        });
      }
    });
  }
}
new ChapterToogle();