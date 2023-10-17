<template>
  <div class="e-groupon" @click="getMarketUrl(activity.status)">
    <div v-if="showTitle === 'show'" class="e-coupon__title">
      {{ activityTitle }}
    </div>
    <div :class="{ 'e-groupon__image-empty': !activity.cover }" class="e-groupon__image-container">
      <img v-if="activity.cover" v-lazy="activity.cover" class="e-groupon__image" />

      <div v-if="tag.length" class="e-groupon__tag">{{ tag }}</div>

      <div
        v-if="type === 'cut'"
        class="absolute bottom-0 left-0 right-0 flex items-end p-8 text-12 text-text-1"
        style="background: linear-gradient(90.57deg, #3BC77B 0%, #63DB91 52.03%, #3AC269 99.9%);border-radius: 6px 6px 0px 0px;height: 32px;"
      >
        <span>砍价享</span>
        <span class="ml-4">￥</span>
        <span class="font-bold text-16" style="line-height: 20px;">{{ activityPrice }}</span>
        <s v-if="activity.originPrice" class="ml4" style="transform: scale(0.83);margin-bottom: -1px;">
          ￥{{ activity.originPrice }}
        </s>
      </div>

      <div
        v-else-if="type === 'groupon'"
        class="absolute bottom-0 left-0 right-0 text-text-1"
        style="background: linear-gradient(90.57deg, #3BC77B 0%, #63DB91 52.03%, #3AC269 99.9%);border-radius: 6px 6px 0px 0px;height: 32px;"
      >
        <div class="flex items-center justify-between h-full px-8">
          <div class="flex items-end">
            <div class="font-bold">
              <span class="mr-4 text-12">拼团价</span>
              <span class="text-12">￥</span>
              <span class="text-16">{{ activityPrice }}</span>
            </div>
            <s v-if="activity.originPrice" class="ml4 text-12" style="transform: scale(0.83);margin-bottom: 1px;">
              ￥{{ activity.originPrice }}
            </s>
          </div>
          <div class="text-12">
            <span class="text-16">{{ this.groupon.grouponNum }}</span>
            人正在拼 / 已团{{ this.groupon.groupTime || 0 }}次
          </div>
        </div>
      </div>

      <div
        v-else-if="type === 'seckill'"
        class="absolute bottom-0 left-0 right-0 text-text-1"
        style="background: linear-gradient(90.57deg, #3BC77B 0%, #63DB91 52.03%, #3AC269 99.9%);border-radius: 6px 6px 0px 0px;height: 32px;"
      >
        <div class="flex items-center justify-between h-full px-8">
          <div class="flex items-end">
            <div class="font-bold">
              <span class="mr-4" style="font-size: 12px;line-height: 16px;">秒杀价</span>
              <span style="font-size: 12px;line-height: 16px;">￥</span>
              <span style="font-size: 16px;line-height: 16px;">{{ activityPrice }}</span>
            </div>
            <s v-if="activity.originPrice" class="ml4 text-12" style="transform: scale(0.83);margin-bottom: 1px;">
              ￥{{ activity.originPrice }}
            </s>
          </div>
          <div class="flex text-12" v-if="activityData && counting && !isEmpty && activity.status === 'ongoing'">
            <div class="mr-4">倒计时</div>
            <div class="time-block">{{ endTimeDown.day }}</div> 天
            <div class="time-block">{{ endTimeDown.hour }}</div> 时
            <div class="time-block">{{ endTimeDown.minute }}</div> 分
            <div class="time-block">{{ endTimeDown.second }}</div> 秒
          </div>
        </div>
      </div>
    </div>

    <div class="flex items-center justify-between e-groupon__context">
      <div class="font-bold text-overflow text-14 text-text-5">
        {{ activity.name || '' }}
      </div>
      <a
        :class="[activity.status, { 'bg-grey': isEmpty || bgGrey }]"
        class="context-sale__shopping"
        href="javascript:;"
      >
        {{ grouponStatus }}
      </a>
    </div>
  </div>
</template>

<script>
import { getTimeData } from '@/utils/date-toolkit';
import Api from '@/api';

export default {
  name: 'EGroupon',
  props: {
    activity: {
      type: Object,
      default: () => {},
    },
    tag: {
      type: String,
      default: '',
    },
    showTitle: {
      type: String,
      default: 'show',
    },
    type: {
      type: String,
      default: 'groupon',
    },
    feedback: {
      type: Boolean,
      default: true,
    },
  },
  data() {
    return {
      counting: true,
      isEmpty: false,
      bgGrey: false,
      endTimeDown: { day: 0, hour: 0, minute: 0, second: 0 },
      buyTimeDown: { day: 0, hour: 0, minute: 0, second: 0 },
      groupon: {}
    };
  },
  created() {
    this.countDownTime();

    if (this.type === 'groupon') {
      Api.groupon({
        query: { activityId: this.activity.id }
      }).then(res => {
        this.groupon = res;
      })
    }
  },
  computed: {
    activityData() {
      return !!Object.values(this.activity).length;
    },
    activityId() {
      return Number(this.activity.id);
    },
    activityTitle() {
      if (this.type === 'seckill') return '秒杀';
      if (this.type === 'cut') return '砍价';
      return '拼团';
    },
    activityPrice() {
      if (this.type === 'cut') return this.activity.rule.lowestPrice;

      if (this.type === 'groupon') return this.activity.rule.ownerPrice;

      if (this.type === 'seckill') return this.activity.rule.seckillPrice;

      return '00.00'
    },
    grouponStatus() {
      const status = this.activity.status;

      switch (this.type) {
        case 'groupon':
          if (status === 'unstart') return '拼团未开始';
          if (status === 'ongoing') return '去拼团';
          if (status === 'closed') return '拼团已结束';
          break;
        case 'seckill':
          if (status === 'unstart') return '秒杀未开始';
          if (status === 'closed') return '秒杀已结束';
          if (status === 'ongoing') {
            if (!this.counting){
              this.activity.status = 'closed';
              return '秒杀已结束';
            }
            if (this.activity.productRemaind == 0) return '商品已售空';
            const nowStamp = new Date().getTime();
            const startStamp = new Date(this.activity.startTime).getTime();
            const endStamp = new Date(this.activity.endTime).getTime();
            if (startStamp < nowStamp && nowStamp < endStamp) return '马上秒';
            if (startStamp > nowStamp) {
              this.bgGrey = true;
              return '秒杀未开始';
            }
          }
          break;
        case 'cut':
          if (status === 'unstart') return '砍价未开始';
          if (status === 'ongoing') {
            if (!this.counting){
              this.activity.status = 'closed';
              return '砍价已结束';
            }
            return '发起砍价';
          }
          if (status === 'closed') return '砍价已结束';
          break;
      }

      return '';
    },
  },
  methods: {
    getMarketUrl(status) {
      if (!this.feedback) return;
      this.$emit('activityHandle', this.activityId);
    },
    expire() {
      this.counting = false;
    },
    sellOut() {
      this.isEmpty = true;
    },
    countDownTime() {
      this.timer = setInterval(() => {
        this.endTimeDown = getTimeData(new Date(this.activity.endTime).getTime());
        this.buyTimeDown = getTimeData(new Date(this.activity.startTime).getTime());

        if (this.endTimeDown === '已到期') {
          this.seckillClass = 'seckill-closed';
          clearInterval(this.timer);
          this.expire();
        }
      }, 1000);
    },
  },
};
</script>

<style lang="scss" scoped>
  .time-block {
    min-width: 20px;
    height: 16px;
    margin: 0 2px;
    padding: 0 2px;
    color: #34c562;
    text-align: center;
    line-height: 16px;
    background: #fff;
    border-radius: 2px;
  }
</style>
