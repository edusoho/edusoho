import QuestionPicker from '../../../common/component/question-picker';
import BatchSelect from '../../../common/widget/batch-select';
import SelectLinkage from 'app/js/question-manage/widget/select-linkage.js';

new QuestionPicker($('#question-picker-body'), $('#question-checked-form'));
new BatchSelect($('#question-picker-body'));

new SelectLinkage($('[name="courseId"]'),$('[name="lessonId"]'));

$('.js-pick-button').click(function(){
  $(this).button('loading').addClass('disabled');
});