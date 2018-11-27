<template>
  <div class="classroom-plan">
    <e-panel :title="details.title">
      <div class="course-detail__plan-price">
        <span :class="{ isFree: Number(details.price) === 0 }">{{ details.price | filterPrice }}</span>
        <span v-if="showStudent" class="plan-price__student-num">{{ details.studentNum }}人在学</span>
      </div>
    </e-panel>

    <div class="course-detail__validity">
      <service v-if="details.service.length" :services="details.service" ></service>
      <div>
        <span>学习有效期：</span>
        <span v-html="learnExpiry"></span>
      </div>
    </div>

  </div>
</template>
<script>
import service from '../service';
import { formatFullTime } from '@/utils/date-toolkit.js';
import { mapState } from 'vuex';

export default {
  props: {
    details: {
      default: {},
    },
    joinStatus: {
      default: false,
    },
  },
  components: {
    service,
  },
  filters: {
    filterPrice(price) {
      const isFree = Number(price) === 0;
      return isFree ? '免费' : `¥${price}`;
    },
  },
  computed: {
    ...mapState(['courseSettings']),
    learnExpiry() {
      const memberInfo = this.joinStatus;
      const learnExpiryData = this.details.expiryValue;
      const expiryMode = this.details.expiryMode;

      if (!memberInfo) {
        switch (expiryMode) {
          case 'forever':
            return ('永久有效');
            break;
          case 'date':
            const time = new Date(learnExpiryData * 1000);
            return (formatFullTime(time).slice(0, 10) + '之前可学习');
            break;
          case 'days':
            return (learnExpiryData + '天内可学习');
            break;
        }
      } else {
        if (expiryMode == 'forever') {
          return '永久有效'
        }
        return(
          (memberInfo.deadline != 0) ? (memberInfo.deadline.slice(0, 10) + '之前可学习')
          : '永久有效'
        );
      }
    },
    showStudent() {
      return this.courseSettings ? Number(this.courseSettings.show_student_num_enabled) : true;
    },
  }
}
</script>
