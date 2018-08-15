
window.ltc.loadCss();
let load = window.ltc.load('jquery', 'validate');
load.then(function(){

  $.fn.serializeObject = function()
  {
    let o = {};
    let a = this.serializeArray();
    $.each(a, function() {
      if (o[this.name]) {
        if (!o[this.name].push) {
          o[this.name] = [o[this.name]];
        }
        o[this.name].push(this.value || '');
      } else {
        o[this.name] = this.value || '';
      }
    });
    return o;
  };
  window.ltc.messenger.on('getFinishCondition', (msg) => {
    window.ltc.messenger.sendToParent('returnFinishCondition', {valid:true,data:$('#step3-form').serializeObject()});
  });
});