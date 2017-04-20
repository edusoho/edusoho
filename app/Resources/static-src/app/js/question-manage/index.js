// import QuestionPicker from '../../../common/component/question-picker';
import BatchSelect from '../../common/widget/batch-select';
import DeleteAction from '../../common/widget/delete-action';
import { shortLongText } from '../../common/widget/short-long-text';
import SelectLinkage from 'app/js/question-manage/widget/select-linkage.js';

// new QuestionPicker($('#quiz-table-container'), $('#quiz-table'));
new BatchSelect($('#quiz-table-container'));
new DeleteAction($('#quiz-table-container'));
shortLongText($('#quiz-table-container'));


new SelectLinkage($('[name="courseId"]'),$('[name="lessonId"]'));


