<template>
  <div>
    <e-panel :title="details.courseSet.title">
      <div class="course-detail__plan-price">
        <span :class="{isFree: isFree}">{{ filterPrice() }}</span>
        <span v-if="showStudent" class="plan-price__student-num">{{ details.studentNum }}人在学</span>
      </div>
    </e-panel>

    <ul class="course-detail__plan" v-if="!defaultPlan">
      <li v-if="item.title" v-for="(item, index) in items"
        @click="handleClick(item, index)"
        :class="{ active: item.active }">{{item.title}}</li>
    </ul>

    <div class="course-detail__validity">
      <div>
        <span class="mr20">学习有效期</span>
        <span class="dark" v-html="learnExpiryHtml"></span>
      </div>
      <div v-if="details.buyExpiryTime != 0" class="mt5">
        <span class="mr20">购买截止日期</span>
        <span class="validity orange">{{ details.buyExpiryTime | filterTime }}</span>
      </div>
    </div>
  </div>
</template>
<script>
import * as types from '@/store/mutation-types';
import { mapMutations, mapState, mapActions } from 'vuex';

export default {
  data() {
    return {
      items: [],
      isFree: false
    }
  },
  filters:{
    filterTime(time) {
      const fullDate = new Date(time);
      const year = fullDate.getFullYear();
      const month = `0${fullDate.getMonth()}`.slice(-2);
      const date = `0${fullDate.getDate()}`.slice(-2);
      return `${year}-${month}-${date}`;
    }
  },
  watch: {
    selectedPlanId: {
      immediate: true,
      handler(v) {
        this.items = this.details.courses.map((item, index) => {
          this.$set(item, 'active', false);

          if(item.id === this.details.id) {
            item.active = true;
          }

          return item;
        });
      }
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
          endDateStr
        });
      }
    }
  },
  computed: {
    ...mapState('course', {
      details: state => state.details,
      selectedPlanId: state => state.selectedPlanId
    }),
    ...mapState(['courseSettings']),
    learnExpiryHtml() {
      const memberInfo = this.details.member;
      const learnExpiryData = this.details.learningExpiryDate;
      const expiryMode = learnExpiryData.expiryMode;

      if (!memberInfo) {
        switch (expiryMode) {
          case 'forever':
            return ('永久有效');
            break;
          case 'end_date':
            return (learnExpiryData.expiryEndDate.slice(0, 10) + '之前可学习');
            break;
          case 'days':
            return (learnExpiryData.expiryDays + '天内可学习');
            break;
          case 'date':
            const startDateStr = learnExpiryData.expiryStartDate.slice(0, 10);
            const endDateStr = learnExpiryData.expiryEndDate.slice(0, 10);
            return (
              '<div class = "mt5">' + '开课日期：' + startDateStr
              + '&nbsp;&nbsp;&nbsp;' + '截止日期：' + endDateStr + '</div>');
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
    defaultPlan() {
      return this.items.length === 1 && !this.items[0].title;
    }
  },
  methods: {
    ...mapActions ('course', [
      'getCourseDetail'
    ]),
    handleClick (item, index){
      this.items.map(item => item.active = false);
      item.active = true;

      this.getCourseDetail({
        courseId: item.id
      })
    },
    filterPrice () {
      const details = this.details;

      if (Number(details.isFree) || details.price === '0.00') {
        this.isFree = true;
        return '免费';
      }

      this.isFree = false
      return `¥${details.price}`;
    }
  }
}
</script>
