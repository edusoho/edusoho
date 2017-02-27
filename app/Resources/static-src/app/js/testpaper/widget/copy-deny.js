class CopyDeny {
  constructor() {

    document.oncontextmenu = function(){
        return false;
    }
    document.onselectstart = function(){
        return false;
    }
  }
}

export default CopyDeny;