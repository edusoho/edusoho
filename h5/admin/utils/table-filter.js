import { formatTime } from '@/utils/date-toolkit';

const tableFilter = (item, label) => {

  if (label.toLocaleLowerCase().includes('price')) {
    if (!item[label]) {
      return '未设置';
    }
    return `${item[label]}元`;
  }

  switch (label) {
    case 'deadline':
     if (!item['deadline']) {
        return '未知日期'
      }
      const date = new Date(item['deadline']);
      return formatTime(date).slice(0, 10);
    case 'delete':
      return `移除`;
    case 'price':
      if (!item['price']) {
        return '未设置';
      }
      return `${item['price']}元`;
    case 'generatedNum':
      return `${item['unreceivedNum']} / ${item['generatedNum']}`;
    case 'rate':
      let targetType = '全部商品';
      let discountType = '折扣';
      let text = '折';
      const target = item.target;

      if (item.targetType === 'classroom') {
        targetType = target ? target.title : '全部班级';
      }
      if (item.targetType === 'course' && !target) {
        targetType = target ? target.title : '全部课程';
      }
      if (item.targetType === 'vip') {
        targetType = '会员';
      }
      if (item.type === 'minus') {
        discountType = '抵价';
        text = '元';
      }
      return `${discountType + item.rate + text} / ${targetType}`;
    default:
      return item[label]
  }
}

export default tableFilter;
