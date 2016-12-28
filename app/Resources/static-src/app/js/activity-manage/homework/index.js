import BatchSelect from '../../../common/widget/batch-select';
import DeleteAction from '../../../common/widget/delete-action';
import QuestionOperate from '../../../common/component/question-operate';
import Create from './create';

let $from = $('#step2-form');
new Create($('#iframe-content'));
new BatchSelect($from);
new DeleteAction($from);
new QuestionOperate($from,$("#attachment-modal",window.parent.document));