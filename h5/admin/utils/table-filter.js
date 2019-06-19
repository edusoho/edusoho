import { formatTime } from '@/utils/date-toolkit';

const tableFilter = (item, label, subProperty) => {
  const labelStr = subProperty ? subProperty.toLocaleLowerCase() : label.toLocaleLowerCase();
  const labelField = subProperty ? item[label][subProperty] : item[label];
  if (labelStr.includes('price')) {
    if (!labelField) {
      return '未设置';
    }
    return `${labelField}元`;
  }
  switch (label) {
    case 'deadline':
      if (item.deadlineMode && item.deadlineMode === 'day') {
        return `领取${item.fixedDay}天后有效`;
      }
      if (!item.deadline) {
        return '未知日期';
      }
      const date = new Date(item.deadline);
      return formatTime(date).slice(0, 10);
    case 'createdTime':
      if (!item.createdTime) {
        return '未知日期';
      }
      const date1 = new Date(item.createdTime);
      return formatTime(date1);
    case 'delete':
      return '移除';
    case 'generatedNum':
      return `${item.unreceivedNum} / ${item.generatedNum}`;
    case 'rate':
      let targetType = '全部商品';
      let discountType = '折扣';
      let text = '折';
      const target = item.target;

      if (item.targetType === 'classroom') {
        targetType = target ? target.title : '全部班级';
      }
      if (item.targetType === 'course') {
        targetType = target ? target.title : '全部课程';
      }
      if (item.targetType === 'vip') {
        targetType = target ? target.name : '全部会员';
      }
      if (item.type === 'minus') {
        discountType = '抵价';
        text = '元';
      }
      return `${discountType + item.rate + text} / ${targetType}`;
    default:
      // 有子属性的返回子属性
      if (subProperty) {
        return item[label][subProperty];
      }
      return item[label];
  }
};

export default tableFilter;
