define(function(require, exports, module) {

    exports.run = function() {
        /* 屏蔽右击 */
        document.oncontextmenu=function(){
          return false;
        }

        /* 屏蔽CTRL */
        document.onkeydown=function(){
          event.ctrlKey=false;
        }
        /* 屏蔽拖拉 */
        document.onselectstart=function(){
          event.returnValue=false;
        }

    }


});

