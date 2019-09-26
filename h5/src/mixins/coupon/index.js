import { formatFullTime, formatchinaTime } from '@/utils/date-toolkit';

export default {
  methods: {
    timeExpire(createdTime, deadline) {
      if (!createdTime) {
        deadline = formatFullTime(new Date(deadline));
        return `有效期截止：${deadline}`;
      }

      createdTime = formatFullTime(new Date(createdTime));
      deadline = formatFullTime(new Date(deadline));
      return `${createdTime} 至 ${deadline}`;
    },
    timeCalculation(num) {
      const date = new Date();
      let deadline = date.setDate(new Date().getDate() + Number(num)); // N天后的日期
      deadline = formatFullTime(new Date(deadline));
      return `有效期至：${deadline}`;
    },
    priceHtml({ rate, type }, needStyle = true) {
      const intPrice = parseInt(rate, 10);
      const intNum = intPrice.toString().length;
      const intClass = intNum > 3 ? 'text-16 ml-5' : '';
      let pointPrice = Number(rate).toFixed(2).split('.')[1];
      pointPrice = Number(pointPrice) === 0 ? '' : (`.${pointPrice}`);
      const typeText = type === 'discount' ? '折' : '元';
      if (!needStyle) {
        return intPrice + pointPrice + typeText;
      }
      return `<span class="${intClass}">${intPrice}</span><span class="text-14">${pointPrice + typeText}</span>`;
    },
    scopeFilter({ targetDetail }) {
      const numberType = targetDetail.numtype;
      const productType = targetDetail.product;
      let text = '';
      if (numberType === 'single') {
        switch (productType) {
          case 'course':
          case 'classroom':
            text = '指定商品';
            break;
          case 'vip':
            text = '指定会员';
            break;
          default:
            text = '';
        }
      } else if (numberType === 'all') {
        // 全部
        switch (productType) {
          case 'course':
            text = '全部课程';
            break;
          case 'classroom':
            text = '全部班级';
            break;
          case 'all':
            text = '全部商品';
            break;
          case 'vip':
            text = '全部会员';
            break;
          default:
            text = '';
        }
      } else {
        switch (productType) {
          case 'course':
          case 'classroom':
            text = '部分商品';
            break;
          default:
            text = '';
        }
      }
      return text;
    },
    handleClick(coupon) {
      this.$emit('buttonClick', coupon);
    },
    receiveTimeExpire(deadline) {
      deadline = formatchinaTime(new Date(deadline));
      return deadline;
    }
  }
};
