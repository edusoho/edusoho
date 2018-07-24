import QuestionPicker from 'app/common/component/question-picker';
import BatchSelect from 'app/common/widget/batch-select';
import SelectLinkage from 'app/js/question-manage/widget/select-linkage.js';

let $questionPickerBody = $('#question-picker-body',window.parent.document);
const $step2Form = $('#task-create-content-iframe').contents().find('#step2-form');
new QuestionPicker($questionPickerBody , $step2Form);
new BatchSelect($questionPickerBody);

new SelectLinkage($('[name="courseId"]',window.parent.document),$('[name="lessonId"]',window.parent.document));
