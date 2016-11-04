/**
 * Created by Simon on 03/11/2016.
 */

  class Chooser {

    constructor() {
        this.$parentiframe = $(window.parent.document).find('#task-manage-content-iframe');
    }

    _close() {
        $('.file-chooser-main').addClass('hidden');
        $('.file-chooser-bar').removeClass('hidden');
        this.$parentiframe.height(this.$parentiframe.contents().find('body').height());
    }

    _open() {
        $('.file-chooser-bar').addClass('hidden');
        $('.file-chooser-main').removeClass('hidden');
        this.$parentiframe.height(this.$parentiframe.contents().find('body').height());
    }

}

export default Chooser;


