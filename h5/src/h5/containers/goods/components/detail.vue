<template>
  <div class="detail-info" v-if="goods.id">
    <p class="detail-info__title" :class="isShare && 'detail-info__title--pr'">
      <span class="certificate-icon" v-if="currentSku.hasCertificate">{{ $t('goods.certificate') }}</span>
      {{ goods.title }}
      <i
        v-if="isShare"
        class="iconfont icon-fenxiang goods-share"
        @click="onShare"
      ></i>
    </p>

    <div
      v-if="goods.discount && currentSku.displayPrice != 0"
      class="detail-info__price"
    >
      <div class="clearfix">
        <div class="pull-left">
          {{ $t('goods.preferentialPrice') }}
          <span
            v-if="currentSku.displayPriceObj.currency === 'RMB'"
            class="price"
          >
            {{ currentSku.displayPriceObj.amount | formatPrice }}{{ $t('goods.cny') }}
          </span>
          <span
            v-if="currentSku.displayPriceObj.currency === 'coin'"
            class="price"
          >
            {{ currentSku.displayPriceObj.coinAmount | formatPrice }}
            <span class="detail-right__price__unit">
              {{ currentSku.displayPriceObj.coinName }}
            </span>
          </span>
        </div>
        <div class="pull-right study-num">
          <i class="iconfont icon-renqi"></i>
          {{ goods.product.target.studentNum }}{{ $t('goods.person') }}
        </div>
      </div>
    </div>

    <div
      v-if="!goods.discount || currentSku.displayPrice == 0"
      class="detail-info__price"
    >
      <div class="clearfix">
        <div class="pull-left">
          {{ $t('goods.price') }}
          <span
            v-if="currentSku.displayPriceObj.currency === 'RMB'"
            class="price"
          >
            {{ currentSku.displayPriceObj.amount | formatPrice }}{{ $t('goods.cny') }}
          </span>
          <span
            v-if="currentSku.displayPriceObj.currency === 'coin'"
            class="price"
          >
            {{ currentSku.displayPriceObj.coinAmount | formatPrice }}
            <span class="detail-right__price__unit">
              {{ currentSku.displayPriceObj.coinName }}
            </span>
          </span>
        </div>
        <div
          v-if="goodsSetting.show_number_data === 'join'"
          class="pull-right study-num"
        >
          <i class="iconfont icon-people"></i>
          {{ goods.product.target.studentNum }}
        </div>
        <div
          v-else-if="goodsSetting.show_number_data === 'visitor'"
          class="pull-right study-num"
        >
          <i class="iconfont icon-visibility"></i>
          {{ goods.hitNum }}
        </div>
      </div>
    </div>

    <!-- 学习有效期 -->
    <div class="detail-info__validity">
      {{ $t('goods.validity') }}
      <span
        class="detail-info__validity__content"
        v-html="buyableModeHtml"
      ></span>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    goods: {
      type: Object,
      default: () => {},
    },
    currentSku: {
      type: Object,
      default: () => {},
    },
    goodsSetting: {
      type: Object,
      default: () => {},
    },
  },
  data() {
    return {
      isShare: false, // 是否显示分享按钮
    };
  },
  filters: {
    formatPrice(input) {
      return (Math.round(input * 100) / 100).toFixed(2);
    },
  },
  computed: {
    buyableModeHtml() {
      const memberInfo = this.goods.member;
      const {
        usageMode,
        usageEndTime,
        usageDays,
        usageStartTime,
      } = this.currentSku;

      if (!memberInfo) {
        switch (usageMode) {
          case 'forever':
            return this.$t('goods.longTermEffective');
          case 'end_date':
            return (
              this.formatDate(usageEndTime.slice(0, 10)) + `&nbsp;${this.$t('goods.canLearnBefore')}`
            );
          case 'days':
            return this.$t('goods.studyWithinDay', { number: usageDays });
          case 'date':
            return (
              this.formatDate(usageStartTime.slice(0, 10)) +
              '&nbsp;~&nbsp;' +
              this.formatDate(usageEndTime.slice(0, 10))
            );
          default:
            return '';
        }
      } else {
        if (usageMode == 'forever' || memberInfo.deadline == 0) {
          return this.$t('goods.longTermEffective');
        }
        return memberInfo.deadline.slice(0, 10) + this.$t('goods.canLearnBefore');
      }
    },
  },
  methods: {
    formatDate(time, fmt = 'yyyy-MM-dd') {
      time = time * 1000;
      const date = new Date(time);
      if (/(y+)/.test(fmt)) {
        fmt = fmt.replace(
          RegExp.$1,
          (date.getFullYear() + '').substr(4 - RegExp.$1.length),
        );
      }
      const o = {
        'M+': date.getMonth() + 1,
        'd+': date.getDate(),
        'h+': date.getHours(),
        'm+': date.getMinutes(),
        's+': date.getSeconds(),
      };
      for (const k in o) {
        if (new RegExp(`(${k})`).test(fmt)) {
          const str = o[k] + '';
          fmt = fmt.replace(
            RegExp.$1,
            RegExp.$1.length === 1 ? str : ('00' + str).substr(str.length),
          );
        }
      }
      return fmt;
    },

    onShare() {
      // 分享
    },
  },
};
</script>
