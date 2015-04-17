define(function(require, exports, module) {

   var Widget = require('widget');

   var AnnouncementWidget = Widget.extend({
      attrs: {
         speed: 500
      },

      events: {
         'mouseover': 'mouseover',
         'onmouseout': 'mouseout'
      },

      setup: function() {
         //this.marquee();
         var self = this;
         var AnnouncementsInterval=setInterval(this.marquee, self, this.get("speed"));
         this.set("AnnouncementsInterval", AnnouncementsInterval);
      },

      marquee: function(self){
         console.log(self.get("speed"));
         var obj = $(self.element[0]).find(".items");
         var childrenCount = obj.children().length;
         var childHeight = $(obj.children()[0]).height();
         var marginTop = obj.data("margin-top");
         if(!marginTop){
            marginTop = 0;
         }

         console.log((childrenCount-1)*childHeight);
         var offset = obj.offset();
         console.log(marginTop );
         if(marginTop < (childrenCount-1)*childHeight){
            console.log(offset.top+10);
            obj.offset({top:(offset.top++), left:offset.left});
            obj.data("margin-top", marginTop++);
         } else {
            obj.offset({top:offset.top-marginTop, left:offset.left})
         }
      },

      mouseover: function(){
         clearInterval(this.get("AnnouncementsInterval"));
      }, 

      mouseout: function() {
         var AnnouncementsInterval=setInterval(this.marquee, speed)
         this.set("AnnouncementsInterval", AnnouncementsInterval);
      }


   });

   module.exports = AnnouncementWidget;
});

