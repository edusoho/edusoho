import { formatTime } from '@/utils/date-toolkit';

const tableFilter = (item, label) => {
console.log(item, label,999)
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
    case 'targetType':
      if (item['targetType'] === 'course') {
        return '课程'
      } else if (item['targetType'] === 'classroom') {
        return '班级'
      } else if (item['targetType'] === 'vip') {
        return '会员'
      }
      return '全站';
    case 'generatedNum':
      return `${item['usedNum']} / ${item['generatedNum']}`;
    default:
      return item[label]
  }
}

export default tableFilter;
