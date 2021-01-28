import BatchSelect from 'app/common/widget/batch-select';
import QuestionOperate from 'app/common/component/question-operate';
import Create from './homework';

let $from = $('#step2-form');
new Create($('#iframe-content'));
new BatchSelect($from);
new QuestionOperate($from,$('#attachment-modal',window.parent.document));