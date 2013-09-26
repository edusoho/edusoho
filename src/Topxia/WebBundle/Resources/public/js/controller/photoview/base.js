define(function(require, exports, module) {

	var $=require('jquery');
  var Validator = require('bootstrap.validator');
  require('jquery.raty');
  require('common/validator-rules').inject(Validator);
  require("jquery.lazyload");
	
  var showEvent=function(){
 		var obj=$(this);
 		var data=getData(obj);
 		showPopup(data);
  }

  var getData=function(obj){
    var imgurl=obj.attr('src');
    var commenturl=obj.attr('data-comment');
    var createurl=obj.attr('data-comment-create');
    var title=obj.parent().parent().find('h4').text();
    return {'imgurl':imgurl,'commenturl':commenturl,'createurl':createurl,'title':title};
  }

  var showPopup=function(data){
    var popupObj=$('.popup-gallery');
    popupObj.find('.popup-gallery-photoarea img').attr('src', data.imgurl);
    popupObj.find('#qustion-form').attr('action', data.createurl);
    popupObj.find('.popup-gallery-title').text(data.title);

    $.get(data.commenturl,function(json) {
        var postlist=$('.popup-gallery-postlist');
        postlist.html('');
        postlist.html(json);
    });

    popupObj.show();
    var closeObj=popupObj.find('.popup-gallery-close');
    closeObj.click(function() {
      clearPopupData();
      $('.popup-gallery').hide();
    });
     
  }



  var clearPopupData=function(){
    $('.popup-gallery-postlist').html('');
    $('#qustiontext').val('');
  }

	var tagname='.ajax-popup-link';
	var buiderImg=function(){
		$(tagname).unbind()
		$(tagname).click(showEvent);
	}


  var piclist;
  var pic_index=0;
  var up=function(){
    if(pic_index>0){
      pic_index--;
      var id=piclist.ids[pic_index];
      getinfo(id);
    }
    
  }
  
  var down=function(){
    if(pic_index<piclist.ids.length-1){
      pic_index++;
      var id=piclist.ids[pic_index];
      getinfo(id);
    }
    
  }

  var path=function(url,id){
    url=url.replace('0',id);
    return url;
  }
  var getinfo=function(id){
    var popupObj=$('.popup-gallery');
    var url=path(popupObj.attr('data-url'),id);
    var commenturl=path(popupObj.attr('data-comment'),id) ;
    var commentcreaturl= path(popupObj.attr('data-create'),id);
    $.post(url,function(json){
      popupObj.find('.popup-gallery-photoarea img').attr('src', json.url);
      popupObj.find('#qustion-form').attr('action',commentcreaturl);
      popupObj.find('.popup-gallery-title').text(json.title);
      popupObj.find('.popup-gallery-text').text(json.content);

      $.get(commenturl,function(json) {
          var postlist=$('.popup-gallery-postlist');
          postlist.html('');
          postlist.html(json);
        });
      });

  }


  exports.run = function() {
     buiderImg();

    $(function() {
          $("img.lazy").lazyload({
              effect : "fadeIn"
          });
      });

    //获取图片数据集合
    var ids=$('.photoview-list').attr('data-list-ids');
    piclist=$.parseJSON(ids);

    //上一张图片

    $('.popup-gallery-lbtn').click(up);

    $('.popup-gallery-rbtn').click(down);

    //下一张图片

    //提问
      var validator = new Validator({
            element: '#qustion-form',
            autoSubmit: false
        });

        validator.addItem({
            element: '[name="qustion[content]"]',
            required: true
        });

        validator.on('formValidated', function(error, msg, $form) {
            if (error) {
                return;
            }
            $.post($form.attr('action'), $form.serialize(), function(json) {
               $('#qustiontext').val('');
               $(".popup-gallery-postlist").prepend(json); //业务逻辑
            });
        });
  };

});