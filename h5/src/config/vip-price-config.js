// 按月开通：1个月，3个月，12个月
// 按年开通：1年，2年，3年
// 按年月开通： 1个月，3个月，1年
const unitLabel = {
  month: '个月',
  year: '年'
};

const priceItem = (num, unit, singlePrice) => ({
  time: `${num}${unitLabel[unit]}`,
  price: num * singlePrice,
  num,
  unit
});

const priceItems = (buyType, monthPrice, yearPrice) => {
  switch (buyType) {
    case 'month':
      return [
        priceItem(1, 'month', monthPrice),
        priceItem(3, 'month', monthPrice),
        priceItem(12, 'month', monthPrice)
      ];
    case 'year':
      return [
        priceItem(1, 'year', yearPrice),
        priceItem(2, 'year', yearPrice),
        priceItem(3, 'year', yearPrice)
      ];
    case 'year_and_month':
      return [
        priceItem(1, 'month', monthPrice),
        priceItem(3, 'month', monthPrice),
        priceItem(1, 'year', yearPrice)
      ];
    default:
      return [];
  }
};

export default priceItems;
