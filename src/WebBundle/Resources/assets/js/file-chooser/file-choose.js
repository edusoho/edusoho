/**
 * Created by Simon on 31/10/2016.
 */
import MaterialLibChoose from '../../common/chooser/materiallib-choose';

import FileImport from '../../common/chooser/import-video';

import CourseFileChoose from '../../common/chooser/coursefile-choose';

var $parentIframe = $(window.parent.document).find('#task-manage-content-iframe');

/*
var materialLibChoose = new MaterialLibChoose($('#chooser-course-panel'));

var fileImport = new FileImport($('#chooser-import-panel'));

var courseFileChoose = new CourseFileChoose($('#chooser-material-panel'));
*/

$("#material a").click(function (e) {
    e.preventDefault();
    $(this).tab('show')
    $parentIframe.height($parentIframe.contents().find('body').height());
});


//export  {MaterialLibChoose, FileImport, CourseFileChoose} ;