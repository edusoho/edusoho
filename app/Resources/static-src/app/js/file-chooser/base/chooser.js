import Emitter from "component-emitter";
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
}
export default Chooser;
