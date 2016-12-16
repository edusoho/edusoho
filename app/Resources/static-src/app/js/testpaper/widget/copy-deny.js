class CopyDeny {
  constructor() {
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
}

export default CopyDeny;