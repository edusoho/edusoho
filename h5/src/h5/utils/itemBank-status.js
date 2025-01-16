import i18n from '@/lang';
import { formatFullTime } from './date-toolkit';
const getBtnText = status => {
  switch (status) {
    case 'doing':
    case 'paused':
      return {
        text: i18n.t('questionBank.continue'),
        class: 'bg-[#408ffb] border-[#408ffb] font-medium text-1 text-[#FFFFFF]',
      };
    case 'reviewing':
    case 'finished':
      return {
        text: i18n.t('questionBank.viewReport'),
        class: 'bg-white border-[#FF7D00] text-[#FF7D00]',
      };
    default:
      return {
        text: i18n.t('questionBank.start'),
        class: 'bg-white border-[#408ffb]',
      };
  }
};
const learnExpiry = ItemBankExercise => {
  const expiryMode = ItemBankExercise.expiryMode;
  const expiryDays = ItemBankExercise.expiryDays;
  const startDateStr = formatFullTime(
    new Date(ItemBankExercise.expiryStartDate * 1000),
  );
  const endDateStr = formatFullTime(
    new Date(ItemBankExercise.expiryEndDate * 1000),
  );
  switch (expiryMode) {
    case 'forever':
      return i18n.t('questionBank.permanent');
    case 'end_date':
      return endDateStr + '之前可学习';
    case 'days':
      return expiryDays + '天内可学习';
    case 'date':
      return `${startDateStr} 至 ${endDateStr}`;
  }
};
export { getBtnText, learnExpiry };
