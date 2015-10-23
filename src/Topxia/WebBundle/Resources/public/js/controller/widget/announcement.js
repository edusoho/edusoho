define(function(require, exports, module) {

   var Widget = require('widget');

   var AnnouncementWidget = Widget.extend({
      attrs: {
         speed: 50
      },

      events: {
         'mouseover': 'mouseover',
         'mouseout': 'mouseout'
      },

      setup: function() {
         var self = this;
         var AnnouncementsInterval=setInterval(function(){
            self.marquee(self);
         }, this.get("speed"));
         this.set("AnnouncementsInterval", AnnouncementsInterval);
      },

      marquee: function(self){
         var obj = $(self.element[0]).find(".items");
         var childrenCount = obj.children().length;
         if(childrenCount<=1){
            clearInterval(this.get("AnnouncementsInterval"));
            return;
         }
         var childHeight = $(obj.children()[0]).height();
         var marginTop = obj.data("margin-top");
         if(!marginTop){
            marginTop = 0;
         }

         var offset = obj.offset();
         if(marginTop < (childrenCount-1)*childHeight){
            offset.top--;
            marginTop++;
            obj.offset({top:offset.top, left:offset.left});
            obj.data("margin-top", marginTop);
         } else {
            obj.offset({top:offset.top+(childHeight), left:offset.left})
            obj.data("margin-top", marginTop-childHeight);
            $(obj.children()[0]).appendTo(obj);

         }
      },

      mouseover: function(){
         clearInterval(this.get("AnnouncementsInterval"));
      }, 

      mouseout: function() {
         var self = this;
         var AnnouncementsInterval = setInterval(function(){
            self.marquee(self);
         }, this.get("speed"));
         this.set("AnnouncementsInterval", AnnouncementsInterval);
      }


   });

   module.exports = AnnouncementWidget;
});

