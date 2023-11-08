import { Toast } from 'vant';
import i18n from '@/lang';


export const closedToast = (kind) => {
  switch (kind) {
    case 'course':
      Toast(i18n.t('goods.courseClosedToast'));
      break;
    case 'classroom':
      Toast(i18n.t('goods.classroomClosedToast'));
      break;
    case 'exercise':
      Toast(i18n.t('goods.exerciseClosedToast'));
      break;
  }
}