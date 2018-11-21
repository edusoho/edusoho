<template>
  <div class="course-detail">
    <e-loading v-if="isLoading"></e-loading>
    <join-before v-if="!details.joinStatus"
      :details="details" :planDetails="planDetails"></join-before>

    <join-after v-if="details.joinStatus"
      :details="details" :planDetails="planDetails"></join-after>
  </div>
</template>

<script>
  import joinAfter from './join-after.vue';
  import joinBefore from './join-before.vue';
  import { mapState } from 'vuex';
  import Api from '@/api';

  export default {
    components: {
      joinAfter,
      joinBefore
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
      };
    },
    computed: {
      ...mapState({
        isLoading: state => state.isLoading
      }),
    },
    created(){
      const classroomId = this.$route.params.id;
      Api.getClassroomDetail({
        query: { classroomId, }
      }).then(res => {
        this.getDetails(res);
      })
    },
    methods: {
      getDetails(res) {
        const isEmpty = Object.keys(res).length === 0;
        const summary = res.about;
        const joinStatus = res.member && !isEmpty;
        const {
          courses, teachers, assistants,
          headTeacher, access, reviews, expiryMode,
          expiryValue, title, price, studentNum, service
        } = res;
        const cover = res.cover.large;
        const classId = res.id;
        const planDetails = {
          title, service, price, studentNum,
          expiryMode, expiryValue,
        };

        this.planDetails = planDetails;
        this.details = {
          summary, joinStatus, isEmpty, courses, classId,
          teachers, assistants, headTeacher, access, cover, reviews,
        }
      },
    },
  }
</script>
