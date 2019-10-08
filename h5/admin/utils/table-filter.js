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
      const discountType = '折扣';
      let text = '折';
      const numberType = item.targetDetail.numType;
      const productType = item.targetDetail.product;
      let targetType = '全部商品';
      if (numberType === 'single') {
        switch (productType) {
          case 'course':
          case 'classroom':
            targetType = '指定商品';
            break;
          case 'vip':
            targetType = '指定会员';
            break;
          default:
            targetType = '';
        }
      } else if (numberType === 'all') {
        // 全部
        switch (productType) {
          case 'course':
            targetType = '全部课程';
            break;
          case 'classroom':
            targetType = '全部班级';
            break;
          case 'all':
            targetType = '全部商品';
            break;
          case 'vip':
            targetType = '全部会员';
            break;
          default:
            targetType = '';
        }
      } else {
        switch (productType) {
          case 'course':
          case 'classroom':
            targetType = '部分商品';
            break;
          default:
            targetType = '';
        }
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
