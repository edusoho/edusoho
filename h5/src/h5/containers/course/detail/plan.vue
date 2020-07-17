<template>
  <div>
    <e-panel :title="details.courseSet.title">
      <div class="course-detail__plan-price">
        <span :class="{ isFree: isFree }"
          >{{ filterPrice() }}
          <span v-if="isDiscount" class="original-price ml10"
            >原价：￥{{ details.originPrice }}</span
          >
        </span>
        <span v-if="showStudent" class="plan-price__student-num"
          >{{ details.studentNum }}人在学</span
        >
      </div>
    </e-panel>

    <ul v-if="!defaultPlan" class="course-detail__plan">
      <template v-for="(item, index) in items">
        <li
          v-if="item.title"
          :key="index"
          :class="{ active: item.active }"
          @click="handleClick(item, index)"
        >
          {{ item.title }}
        </li>
      </template>
    </ul>

    <div class="course-detail__validity">
      <div v-if="details.vipLevel && vipSwitch" class="mb15">
        <span class="mr20">会员免费</span>
        <img :src="details.vipLevel.icon" class="vipIcon" />
        <router-link
          :to="{ path: '/vip', query: { id: details.vipLevel.id } }"
          class="color-primary"
        >
          {{ details.vipLevel.name }}免费学</router-link
        >
      </div>
      <service v-if="details.services.length" :services="details.services" />
      <div>
        <span class="mr20">学习有效期</span>
        <span class="dark" v-html="learnExpiryHtml" />
      </div>
      <div v-if="details.buyExpiryTime != 0" class="mt5">
        <span class="mr20">购买截止日期</span>
        <span class="validity orange">{{ buyExpiryTime }}</span>
      </div>
    </div>
  </div>
</template>
<script>
import service from '@/containers/classroom/service';
import { mapState, mapActions } from 'vuex';
import { formatFullTime } from '@/utils/date-toolkit';

export default {
  components: {
    service,
  },
  data() {
    return {
      items: [],
      isFree: false,
    };
  },
  watch: {
    selectedPlanId: {
      immediate: true,
      handler(v) {
        this.items = this.details.courses.map((item, index) => {
          this.$set(item, 'active', false);

          if (item.id === this.details.id) {
            item.active = true;
          }

          return item;
        });
      },
    },
    learnExpiryHtml: {
      immediate: true,
      handler(val) {
        const learnExpiryData = this.details.learningExpiryDate;
        const startDateStr = learnExpiryData.expiryStartDate.slice(0, 10);
        const endDateStr = learnExpiryData.expiryEndDate.slice(0, 10);

        this.$emit('getLearnExpiry', {
          val,
          startDateStr,
          endDateStr,
        });
      },
    },
  },
  computed: {
    ...mapState('course', {
      details: state => state.details,
      selectedPlanId: state => state.selectedPlanId,
      joinStatus: state => state.joinStatus,
    }),
    ...mapState(['courseSettings', 'vipSwitch']),
    learnExpiryHtml() {
      const memberInfo = this.details.member;
      const learnExpiryData = this.details.learningExpiryDate;
      const expiryMode = learnExpiryData.expiryMode;
      const startDateStr = learnExpiryData.expiryStartDate.slice(0, 10);
      const endDateStr = learnExpiryData.expiryEndDate.slice(0, 10);
      if (!memberInfo) {
        switch (expiryMode) {
          case 'forever':
            return '永久有效';
          case 'end_date':
            return endDateStr + '之前可学习';
          case 'days':
            return learnExpiryData.expiryDays + '天内可学习';
          case 'date':
            return (
              '<div class = "mt5">' +
              '开课日期：' +
              startDateStr +
              '&nbsp;&nbsp;&nbsp;' +
              '截止日期：' +
              endDateStr +
              '</div>'
            );
        }
      } else {
        if (expiryMode == 'forever') {
          return '永久有效';
        }
        return memberInfo.deadline != 0
          ? memberInfo.deadline.slice(0, 10) + '之前可学习'
          : '永久有效';
      }
      return '';
    },
    buyExpiryTime() {
      const fullDate = new Date(this.details.buyExpiryTime);
      return formatFullTime(fullDate);
    },
    showStudent() {
      return this.courseSettings
        ? Number(this.courseSettings.show_student_num_enabled)
        : true;
    },
    defaultPlan() {
      return this.items.length === 1 && !this.items[0].title;
    },
    isDiscount() {
      if (!this.details.courseSet) return false;
      const isDiscount = this.details.courseSet.discount;
      if (isDiscount !== '') {
        const discountNum = Number(isDiscount);
        if (discountNum === 10) return false;
        if (discountNum === 0) return true;
        return discountNum;
      }
      return '';
    },
  },
  methods: {
    ...mapActions('course', ['getCourseLessons']),
    handleClick(item, index) {
      this.$router.push({ path: `/course/${item.id}` });
    },
    filterPrice() {
      const details = this.details;

      if (Number(details.isFree) || details.price === '0.00') {
        this.isFree = true;
        return '免费';
      }

      this.isFree = false;
      return `¥${details.price}`;
    },
  },
};
</script>
