// 按月开通：1个月，3个月，12个月
// 按年开通：1年，2年，3年
// 按年月开通： 1个月，3个月，1年

const priceItem = (timeAmout, unit, singlePrice) => ({
  time: `${timeAmout}${unit}`,
  price: timeAmout * singlePrice
});

const priceItems = (buyType, monthPrice, yearPrice) => {
  switch (buyType) {
    case 'month':
      return [
        priceItem(1, '个月', monthPrice),
        priceItem(3, '个月', monthPrice),
        priceItem(12, '个月', monthPrice)
      ];
    case 'year':
      return [
        priceItem(1, '年', yearPrice),
        priceItem(2, '年', yearPrice),
        priceItem(3, '年', yearPrice)
      ];
    case 'year_and_month':
      return [
        priceItem(1, '个月', monthPrice),
        priceItem(3, '个月', monthPrice),
        priceItem(1, '年', yearPrice)
      ];
    default:
      return [];
  }
};

export default priceItems;
