define(function(require, exports, module) {
    var $ = require('$');
    var Base = require('base');

     var contactFloat = Base.extend({
         attrs: {
            name: 'xxxx'
       },
         animate: function(t,o) {
         var n = {"float": "left",minStatue: !1,skin: "gray",durationTime: 1e3}, t = $.extend(n, t);
         
         o.each(function() {
             var n = $(this), h= n.find("h1 a") ,hover = n.find("#onlineSort4"), r = n.find(".online-contact-title_above a"), i = n.find(".online-contact-show"), s = n.find(".online-contact-content"), o = n.find(".online-contact-list").width(), u = n.find(".online-contact-list"), a = n.offset().top;
           n.css(t.float, 0), t.minStatue && ($(".online-contact-show").css("float", t.float), s.css("width", 0), i.css("width", 48)), t.skin && n.addClass("side_" + t.skin), r.bind("click", function() {
                 s.animate({width: "0"}, "fast"), i.stop(!0, !0).delay(300).animate({width: "48px"}, "fast").css("float", "right")
             }), i.click(function() {
                 $(this).animate({width: "0px"}, "fast"), n.width(o), s.stop(!0, !0).delay(200).animate({width: "167px"}, "fast")
             }), h.bind('click',function(){
                    var id = $(this).attr('id');
                    var num = parseInt(id);
                    for (var i = 1; i <=6 ; i++)
                 {
                      if (i == num)
                 {
            $("#onlineSort" + i).attr ("class","online_bar expand");
            $("#onlineType" + i).css("display" , "block");
                  }
              else
               {
            $("#onlineSort" + i).attr("class", "online_bar mycollapse") ;
            $("#onlineType" + i).css("display" , "none");
         }
    }
             }),
             hover.hover(function(){
                $(".displayimg").css("display", "block");
             },function(){
                $(".displayimg").css("display", "none");
             })
         });

     }
        
    });


    module.exports = contactFloat;

});