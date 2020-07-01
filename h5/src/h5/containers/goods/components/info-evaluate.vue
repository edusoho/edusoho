<template>
  <div class="info-evaluate">
    <template v-if="reviews.length">
      <div class="info-evaluate__item clearfix" v-for="review in reviews" :key="review.id">
        <div class="pull-left evaluate-img">
          <img src="http://try6.edusoho.cn/files/user/2020/06-15/1741586dd258161303.jpg" alt="">
        </div>
        <div class="pull-left evaluate-content">
          <div class="evaluate-content__name content-name clearfix">
            <span class="content-name__nickname pull-left">{{ review.user.nickname }}</span>
            <span class="content-name__time pull-right">{{ review.createdTime | createdTime }}</span>
          </div>
          <div class="evaluate-content__plan">
            {{ review.targetName }}
            <van-rate class="plan-rate" readonly :value="review.rating * 1" gutter="2" />
          </div>
          <div class="evaluate-content__text">{{ review.content }}</div></div>
      </div>
    </template>
    <div v-else class="info-evaluate__item">
      暂无评价~
    </div>
  </div>
</template>

<script>
export default {
  props: {
    reviews: {
      type: Array,
      default: () => []
    }
  },
  filters: {
    createdTime(date) {
      // const date = '2020-03-11T18:11:16+08:00';
      const reg = new RegExp('-', 'g');
      let time = date.replace(reg, '/'); // 2020/03/11T18:11:16+08:00
      time = time.slice(0, -9) // 2020/03/11T18:11
      let hour = time.slice(11, 13);
      let str = '';
      if (0 <= hour && hour < 6) {
        str = '凌晨';
      } else if (6 <= hour && hour < 12) {
        str = '上午';
      } else if (12 <= hour && hour < 18) {
        str = '下午';
      } else if (18 <= hour && hour < 24) {
        str = '晚上';
      }
      const reg2 = new RegExp('T', 'g');
      time = time.replace(reg2, ' ' + str);
      return time;
    }
  }
}
</script>