import DoTestpaper from './do-test-base';

import AttachmentActions from 'app/js/attachment/widget/attachment-actions';
if($('#facein-init-modal').length < 1){
    new DoTestpaper($('.js-task-testpaper-body'));
}
new AttachmentActions($('.js-task-testpaper-body'));