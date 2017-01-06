import BatchSelect from '../../../common/widget/batch-select';
import QuestionOperate from '../../../common/component/question-operate';
import Create from './create';

let $from = $('#step2-form');
new Create($('#iframe-content'));
new BatchSelect($from);
new QuestionOperate($from,$("#attachment-modal",window.parent.document));