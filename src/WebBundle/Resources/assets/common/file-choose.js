/**
 * Created by Simon on 31/10/2016.
 */
import MaterialLibChoose from '../common/chooser/materiallib-choose';
import VideoImport from '../common/chooser/import-video';
import CourseFileChoose from '../common/chooser/coursefile-choose';
import Emitter from 'es6-event-emitter';

class FileChooser extends Emitter {

    constructor() {
        super();
        this.initFileChooser();
    }

    initFileChooser() {
        const materialLibChoose = new MaterialLibChoose($('#chooser-material-panel'));
        const courseFileChoose = new CourseFileChoose($('#chooser-course-panel'));
        const videoImport = new VideoImport($('#import-video-panel'));

        materialLibChoose.on('select', this.fileSelect.bind(this));
        courseFileChoose.on('select', this.fileSelect.bind(this));
        videoImport.on('select', this.fileSelect.bind(this));
    }

    fileSelect(file) {
        this.trigger('select', file);
    }

}
export default FileChooser ;

$("#material a").click(function (e) {
    e.preventDefault();
    $(this).tab('show')
    var $parentIframe = $(window.parent.document).find('#task-manage-content-iframe');
    $parentIframe.height($parentIframe.contents().find('body').height());
});