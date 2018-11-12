<template>
  <div class="join-before">
    <detail-head
      :price="details.price"
      :courseSet="details.courseSet"></detail-head>

    <detail-plan @getLearnExpiry="getLearnExpiry"></detail-plan>
    <div class="segmentation"></div>

    <van-tabs v-model="active"
      @click="onTabClick"
      :class="tabsClass" ref="tabs">
      <van-tab v-for="item in tabs" :title="item" :key="item"></van-tab>
    </van-tabs>

    <!-- 课程介绍 -->
    <e-panel title="课程介绍" ref="about" class="about">
      <div v-html="summary"></div>
    </e-panel>
    <div class="segmentation"></div>

    <!-- 教师介绍 -->
    <teacher
      ref="teacher"
      class="teacher"
      :teacherInfo="details.teachers"></teacher>
    <div class="segmentation"></div>

    <!-- 课程目录 -->
    <directory ref="directory"></directory>
    <e-footer @click.native="handleJoin">
      {{details.access.code | filterJoinStatus}}</e-footer>
  </div>
</template>
<script>
  import Teacher from './detail/teacher';
  import Directory from './detail/directory';
  import DetailHead from './detail/head';
  import DetailPlan from './detail/plan';
  import { mapMutations, mapActions, mapState } from 'vuex';
  import * as types from '@/store/mutation-types';
  import redirectMixin from '@/mixins/saveRedirect';

  export default {
    name: 'joinBefore',
    mixins: [redirectMixin],
    data() {
      return {
        teacherInfo: {},
        tabs: ['课程介绍', '教师介绍', '目录'],
        active: 0,
        tabsClass: '',
        learnExpiry: '永久有效',
        startDateStr: '',
        endDateStr: '',
        tops: {
          tabsTop: 0,
          teacherTop: 0,
          aboutTop: 0,
        }
      }
    },
    components: {
      Teacher,
      Directory,
      DetailHead,
      DetailPlan
    },
    computed: {
      ...mapState('course', {
        details: state => state.details
      }),
      summary () {
        return this.details.summary || this.details.courseSet.summary;
      }
    },
    mounted() {
      const refs = this.$refs;

      window.addEventListener('touchmove', this.handleTouch);
      window.addEventListener('scroll', this.handleScroll);
      setTimeout(() => {
        window.scrollTo(0,0);

        Object.keys(refs).forEach(item => {
          this.tops[`${item}Top`] = refs[item].$el.getBoundingClientRect().top
        })
        console.log(this.tops);
      }, 100)
    },
    methods: {
       ...mapActions('course', [
        'joinCourse'
       ]),
      onTabClick(index, title) {
        const ref = this.$refs[this.transIndex2Tab(index)];
        window.scrollTo(0, ref.$el.offsetTop - 44);
      },
      transIndex2Tab(index) {
        return index ? (index > 1 ? 'directory' : 'teacher') : 'about';
      },
      handleTouch() {
        const scrollTop = window.pageYOffset ||
          document.documentElement.scrollTop || document.body.scrollTop;

        this.active = this.activeCurrentTab(scrollTop);

        scrollTop >= this.tops.tabsTop
          ? this.tabsClass = 'van-tabs--fixed'
          : this.tabsClass = '';
      },
      activeCurrentTab(scrollTop) {
        const tops = this.tops;

        scrollTop  = scrollTop + 44;

        return (scrollTop < tops.teacherTop) ? 0
          :(scrollTop >= tops.directoryTop ? 2 : 1);
      },
      handleJoin(){
        const endDate = this.details.learningExpiryDate.expiryEndDate;
        const endDateStamp = new Date(endDate).getTime();
        const todayStamp = new Date().getTime();
        let isPast = todayStamp < endDateStamp;
        endDate == 0 ? (isPast = true) : (isPast = todayStamp < endDateStamp);

        if (!this.$store.state.token) {
          this.$router.push({
            name: 'login',
            query: {
              redirect: this.redirect
            }
          });
          return;
        }

        if (Number(this.details.buyable) && isPast) {
          if (+this.details.price) {
            this.$router.push({
              name: 'order',
              params: {
                id: this.details.id,
              },
              query: {
                expiryScope: `${this.startDateStr} — ${this.endDateStr}`
              }
            });
          } else {
            this.joinCourse({
              id: this.details.id
            });
          }
        }
      },
      getLearnExpiry(data) {
        this.learnExpiry = data.val;
        this.startDateStr = data.startDateStr;
        this.endDateStr = data.endDateStr;
      }
    },
    destroyed () {
      window.removeEventListener('touchmove', this.handleScroll);
    },
  }
</script>
