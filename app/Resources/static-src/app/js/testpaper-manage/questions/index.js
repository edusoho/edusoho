import 'jquery-sortable';
import BatchSelect from '../../../common/widget/batch-select';
import QuestionOperate from '../../../common/component/question-operate';
import QuestionManage from './manage';

let $testpaperItemsManager = $('#testpaper-items-manager');
new QuestionOperate($testpaperItemsManager,$('#modal'));
new QuestionManage($testpaperItemsManager);
new BatchSelect($testpaperItemsManager);


