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
      break;
    case 'year':
      return [
        priceItem(1, '年', yearPrice),
        priceItem(2, '年', yearPrice),
        priceItem(3, '年', yearPrice)
      ];
      break;
    case 'year_and_month':
      return [
        priceItem(1, '个月', monthPrice),
        priceItem(3, '个月', monthPrice),
        priceItem(1, '年', yearPrice)
      ];
      break;
    default:
      return [];
      break;
  }
};

export default priceItems;
