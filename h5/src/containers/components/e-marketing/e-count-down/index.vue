<template>
  <div :class="['seckill-countdown-container clearfix', seckillClass]">
    <span class="pull-left status-title">秒杀{{activity.status==='ongoing' && seckilling ? '中' : ''}}</span>
    <div class="pull-right text-12" v-html="statusTitle"></div>
  </div>
</template>
<script>
import { dateTimeDown } from '@/utils/date-toolkit';

  export default {
    props: {
      activity: {
        type: Object,
        default: {}
      }
    },
    data () {
      return {
        timer: null,
        counting: true,
        seckillClass: 'seckill-unstart',
        seckilling: false,
        buyCountDownText: '',
        endCountDownText: '',
        endStamp: new Date(this.activity.endTime).getTime(),
        startStamp: new Date(this.activity.startTime).getTime()
      }
    },
    created() {
      this.countDownTime()
    },
    beforeDestroy() {
      this.clearInterval()
    },
    computed: {
      statusTitle: {
        get() {
          const status = this.activity.status;
          if(status === 'unstart') {
            return '秒杀未开始';
          }
          if(status === 'closed') {
            this.seckillClass = 'seckill-closed';
            return '秒杀已结束';
          }
          if(status === 'ongoing') {
            if (!this.counting) return '秒杀已结束';
            if (this.activity.productRemaind == 0) {
              this.seckillClass = 'seckill-closed';
              return '商品已售空';
            }
            const nowStamp = new Date().getTime();
            if ((this.startStamp < nowStamp) && (nowStamp < this.endStamp)) {
              this.seckilling = true;
              this.seckillClass = 'seckill-ongoing';
              return `距离结束仅剩<span class="ml10 mlm">${this.endCountDownText}</span>`;
            };
            if (this.startStamp > nowStamp) {
              this.seckilling = false;
              this.seckillClass = 'seckill-unstart';
              return `距离开抢<span class="ml10 mlm">${this.buyCountDownText}</span>`;
            };
          }
        },
        set() {}
      },
    },
    methods: {
      countDownTime() {
        this.timer = setInterval(() => {
          this.endCountDownText = dateTimeDown(this.endStamp);
          this.buyCountDownText = dateTimeDown(this.startStamp);
          if (this.endCountDownText == '已到期') {
            this.seckillClass = 'seckill-closed';
            this.counting = false;
            this.clearInterval();
            this.$emit('timesUp')
          }
        }, 1000);
      },
      clearInterval() {
        clearInterval(this.timer);
        this.timer = null;
      }
    }
  }
</script>
<style>
  .seckill-countdown-container {
    padding-left: 10px;
    padding-right: 10px;
    line-height: 36px;
    color: #fff;
  }
  .seckill-countdown-container .status-title {
    font-size: 16px;
    font-weight: 600;
  }
  .seckill-countdown-container.seckill-ongoing {
    background-color: #FF5353;
  }
  .seckill-countdown-container.seckill-unstart {
    background-color: #FFAA00;
  }
  .seckill-countdown-container.seckill-closed {
    background-color: #B0BDC9;
  }
</style>