import contract from './modules/contract';
import classroomMember from './modules/classroomMember';
import courseMember from './modules/courseMember';
import exportData from './modules/export';
import file from './modules/file';
import setting from './modules/setting';
import tag from './modules/tag'
import category from './modules/category';
import organization from './modules/organization';
import itemBank from './modules/itemBank'
import security from './modules/security';
import me from './modules/me';
import itemBankExerciseMember from './modules/itemBankExerciseMember';

const Api = {
  contract,
  classroomMember,
  courseMember,
  itemBankExerciseMember,
  exportData,
  file,
  me,
  security,
  setting,
  tag,
  category,
  organization,
  itemBank,
};

export default Api;
