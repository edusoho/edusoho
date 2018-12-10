export default {
  methods: {
    timeExpire(item) {
      let createdTime = '';
      let deadline = '';

      if (!item.createdTime) {
        deadline = item.deadline.slice(0, 10);
        return `有效期截止：${deadline}`;
      }
      createdTime = item.createdTime.slice(0, 10);
      deadline = item.deadline.slice(0, 10);
      return `${createdTime} 至 ${deadline}`;
    },
    priceHtml(item, needStyle = true) {
      const intPrice = parseInt(item.rate, 10);
      const intNum = intPrice.toString().length;
      const intClass = intNum > 3 ? 'text-16' : '';
      let pointPrice = `${Number(item.rate).toFixed(2).split('.')[1]}`;
      pointPrice = `${Number(pointPrice) === 0 ? '' : (`.${pointPrice}`)}`;
      const typeText = item.type === 'discount' ? '折' : '元';
      if (!needStyle) {
        return intPrice + pointPrice + typeText;
      }
      return `<span class="${intClass}">${intPrice}</span><span class="text-14">${pointPrice + typeText}</span>`;
    },
    scopeFilter(item) {
      const { targetType, target } = item;

      if (targetType === 'classroom') {
        return target ? target.title : '全部班级';
      }
      if (targetType === 'course' && !target) {
        return target ? target.title : '全部课程';
      }
      if (targetType === 'vip') {
        return '会员';
      }
      return '全部商品';
    },
    handleClick(coupon) {
      this.$emit('buttonClick', coupon);
    }
  }
};
