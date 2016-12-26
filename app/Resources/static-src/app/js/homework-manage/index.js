import QuestionPicker from '../../common/component/question-picker';
import BatchSelect from '../../common/widget/batch-select';

let $questionPickerBody = $('#question-picker-body',window.parent.document);
new QuestionPicker($questionPickerBody , $('#step2-form'));
new BatchSelect($questionPickerBody);
