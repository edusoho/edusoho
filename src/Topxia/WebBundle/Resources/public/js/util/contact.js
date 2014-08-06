define(function(require, exports, module) {
    var e = require('$');
    var Base = require('base');

    // var Pig = Base.extend({
    //     attrs: {
    //         name: 'xxxx'
    //     },
    //     contact: function(t) {
    //     var n = {"float": "left",minStatue: !1,skin: "gray",durationTime: 1e3}, t = e.extend(n, t);
    //     console.log(t);
    //     this.each(function() {
    //         var n = e(this), r = n.find(".close_btn"), i = n.find(".show_btn"), s = n.find(".side_content"), o = n.find(".side_list").width(), u = n.find(".side_list"), a = n.offset().top;
    //         n.css(t.float, 0), t.minStatue && (e(".show_btn").css("float", t.float), s.css("width", 0), i.css("width", 25)), t.skin && n.addClass("side_" + t.skin), e(window).bind("scroll", function() {
    //             var r = a + e(window).scrollTop() + "px";
    //             n.animate({top: r}, {duration: t.durationTime,queue: !1})
    //         }), r.bind("click", function() {
    //             s.animate({width: "0"}, "fast"), i.stop(!0, !0).delay(300).animate({width: "33px"}, "fast").css("float", "right")
    //         }), i.click(function() {
    //             e(this).animate({width: "0px"}, "fast"), n.width(o), s.stop(!0, !0).delay(200).animate({width: "167px"}, "fast")
    //         })
    //     })
    // }
        
    // });
exports.run = function(t,o) {
         var n = {"float": "left",minStatue: !1,skin: "gray",durationTime: 1e3}, t = e.extend(n, t);
         
         o.each(function() {
             var n = e(this), r = n.find(".close_btn"), i = n.find(".show_btn"), s = n.find(".side_content"), o = n.find(".side_list").width(), u = n.find(".side_list"), a = n.offset().top;
           n.css(t.float, 0), t.minStatue && (e(".show_btn").css("float", t.float), s.css("width", 0), i.css("width", 25)), t.skin && n.addClass("side_" + t.skin), r.bind("click", function() {
                 s.animate({width: "0"}, "fast"), i.stop(!0, !0).delay(300).animate({width: "33px"}, "fast").css("float", "right")
             }), i.click(function() {
                 e(this).animate({width: "0px"}, "fast"), n.width(o), s.stop(!0, !0).delay(200).animate({width: "167px"}, "fast")
             })
         })
     }

    //module.exports = service;

});