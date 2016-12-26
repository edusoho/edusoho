import QuestionPicker from '../../common/component/question-picker';
import BatchSelect from '../../common/widget/batch-select';

let $questionPickerBody = $('#question-picker-body');
new QuestionPicker($questionPickerBody,$('#question-checked-form'));
new BatchSelect($questionPickerBody);
