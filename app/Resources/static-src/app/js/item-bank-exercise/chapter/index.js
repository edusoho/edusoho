class ChapterToogle {

  constructor() {
    this.toogle();
  }

  toogle(){
    cd.onoff({
      el: '#switch'
    }).on('change', (value) => {
      console.log('switch', value);
      $.ajax({
        type: 'GET',
        url: $('#openUrl').val(),
        contentType: 'application/x-www-form-urlencoded;charset=utf-8',
        data: {exerciseId:$('#exerciseId').val(),chapterEnable: value},
        dataType: 'json',
        success: function(data){
          location.reload();
        }
      });
    });
  }

}
new ChapterToogle();