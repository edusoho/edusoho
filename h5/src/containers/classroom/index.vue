<template>
  <div class="course-detail">
    <e-loading v-if="isLoading"></e-loading>
    <component :is="currentComp" :details="details" :planDetails="planDetails"></component>
  </div>
</template>

<script>
  import joinAfter from './join-after.vue';
  import joinBefore from './join-before.vue';
  import { mapState } from 'vuex';
  import Api from '@/api';
  import { Toast } from 'vant';

  export default {
    components: {
    },
    data() {
      return {
        details: {
          isEmpty: true,
          summary: '',
          joinStatus: false,
          courses: [],
          teachers: [],
          assistants: [],
          headTeacher: {},
          access: {
            code: '加载中'
          },
          cover: '',
          reviews: [],
          classId: 0,
        },
        planDetails: {
          title: '',
          service: [],
          price: '0',
          studentNum: 0,
          expiryMode: 'forever',
          expiryValue: '0',
        },
        currentComp: '',
      };
    },
    computed: {
      ...mapState({
        isLoading: state => state.isLoading
      }),
    },
    watch: {
      'details.joinStatus': {
        handler(status) {
          this.getComponent(status)
        }
      }
    },
    created(){
      const classroomId = this.$route.params.id;
      Api.getClassroomDetail({
        query: { classroomId, }
      }).then(res => {
        this.getComponent(res.member);
        this.getDetails(res);
      }).catch(err => {
        Toast.fail(err.message)
      })
    },
    methods: {
      getDetails(res) {
        const isEmpty = Object.keys(res).length === 0;
        const summary = res.about;
        const joinStatus = !isEmpty && res.member;
        const {
          courses, teachers, assistants, buyable,
          headTeacher, access, reviews, expiryMode,
          expiryValue, title, price, studentNum, service,
        } = res;
        const cover = res.cover.large;
        const classId = res.id;
        const planDetails = {
          title, service, price, studentNum,
          expiryMode, expiryValue,
        };

        this.planDetails = planDetails;
        this.details = {
          summary, joinStatus, isEmpty, courses, classId, buyable,
          teachers, assistants, headTeacher, access, cover, reviews,
        }
      },
      getComponent(status) {
        this.currentComp = status ? joinAfter : joinBefore;
      },
    },
  }
</script>
