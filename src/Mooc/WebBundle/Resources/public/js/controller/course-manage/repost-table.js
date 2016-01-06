define(function(require, exports, module) {
	exports.run = function() {
	var  listChange=function(){
    var inputGroupText2=$("input[name=major]");
    var  TextLink2=$(".js-grades ul a");
    TextLink2.click(function(event){
       event.preventDefault();
      var $this=$(this);
      inputGroupText2.val($this.text());
    })
  }();
  var iconHover=function(){
    $this=$('.js-icon');
    $this.mouseenter(function(){
      $('.hover-model').css('display','block');
    }).mouseleave(function(){
      $('.hover-model').css('display','none');
    })
  }();
	};
});	

