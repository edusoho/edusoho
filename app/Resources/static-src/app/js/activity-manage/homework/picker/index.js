import QuestionPicker from 'app/common/component/question-picker';
import BatchSelect from 'app/common/widget/batch-select';

let $questionPickerBody = $('#question-picker-body',window.parent.document);
new QuestionPicker($questionPickerBody , $('#step2-form'));
new BatchSelect($questionPickerBody);
