class CopyDeny {
  constructor() {

    document.oncontextmenu = function(){
        return false;
    }
    document.onselectstart = function(){
        return false;
    }
    document.onmousedown = function(){
        return false;
    }
  }
}

export default CopyDeny;