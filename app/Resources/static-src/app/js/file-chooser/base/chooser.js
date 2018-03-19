import Emitter from 'component-emitter';
class Chooser extends Emitter {
  constructor() {
    super();
  }
  _open() {
    $('.file-chooser-bar').addClass('hidden');
    $('.file-chooser-main').removeClass('hidden');
  }
  _close() {
    $('.file-chooser-main').addClass('hidden');
    $('.file-chooser-bar').removeClass('hidden');
  }
  _getUrlParameter(url, param) {
    var sPageParams = url.split('?');
    if (sPageParams && sPageParams.length == 2) {
      var sPageURL = decodeURIComponent(sPageParams[1]);
      var sURLVariables = sPageURL.split('&');
      for (let i = 0; i < sURLVariables.length; i++) {
        var sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === param) {
          return sParameterName[1] === undefined ? null : sParameterName[1];
        }
      }
    }
    return null;

  }
}
export default Chooser;
