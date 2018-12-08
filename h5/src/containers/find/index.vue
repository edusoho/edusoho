<template>
  <div class="find-page">
    <e-loading v-if="isLoading"></e-loading>
    <div class="find-page__part" v-for="(part, index) in parts" :key="index">
      <e-swipe v-if="part.type == 'slide_show'" :slides="part.data"></e-swipe>
      <e-course-list
        v-if="['classroom_list', 'course_list'].includes(part.type)"
        :courseList="part.data"
        :typeList="part.type"
        :feedback="feedback"
        :index="index"
        @fetchCourse="fetchCourse"></e-course-list>
      <e-poster
        v-if="part.type == 'poster'"
        :class="imageMode[part.data.responsive]"
        :poster="part.data"
        :feedback="feedback"></e-poster>
     <e-coupon-list
        v-if="part.type == 'coupon'"
        :coupons="part.data"
        :couponIndex="index"
        :showTitle="part.titleShow"
        @couponHandle="couponHandle"
        :feedback="true"></e-coupon-list>
    </div>
  </div>
</template>

<script>
  import courseList from '../components/e-course-list/e-course-list.vue';
  import poster from '../components/e-poster/e-poster.vue';
  import swipe from '../components/e-swipe/e-swipe.vue';
  import couponList from '../components/e-coupon-list/e-coupon-list.vue';
  import * as types from '@/store/mutation-types';
  import Api from '@/api';
  import { mapState } from 'vuex';
  import { Toast } from 'vant';

  export default {
    components: {
      'e-course-list': courseList,
      'e-swipe': swipe,
      'e-poster': poster,
      'e-coupon-list': couponList
    },
    props: {
      feedback: {
        type: Boolean,
        default: true,
      },
    },
    data() {
      return {
        parts: [],
        imageMode: [
          'responsive',
          'size-fit',
        ],
      };
    },
    computed: {
      ...mapState({
        isLoading: state => state.isLoading
      })
    },
    created() {
      const {preview, token} = this.$route.query

      if (preview == 1) {
        Api.discoveries({
          params: {
            mode: 'draft',
            preview: 1,
            token,
          },
        })
          .then((res) => {
            this.parts = Object.values(res);
          })
          .catch((err) => {
            Toast.fail(err.message);
          });
        return;
      }

      Api.discoveries()
        .then((res) => {
          this.parts = Object.values(res);
        })
        .catch((err) => {
          Toast.fail(err.message);
        });
    },
    methods: {
      fetchCourse({params, index, typeList}) {
        if (typeList === 'classroom_list') {
          Api.getClassList({params}).then(res => {
            if (this.sourceType === 'custom') return;

            this.parts[index].data.items = res.data;
          })
          return;
        }
        Api.getCourseList({params}).then(res => {
          if (this.sourceType === 'custom') return;

          this.parts[index].data.items = res.data;
        })
      },
      couponHandle(value) {
        const data = value.item;
        const itemIndex = value.itemIndex;
        const couponIndex = value.couponIndex;
        const token = data.token;
        const item = this.parts[couponIndex].data[itemIndex];

        if (data.currentUserCoupon) {
          const couponType = data.targetType;
          if (data.target) {
            const id = data.target.id;
            this.$router.push({
              path: `${couponType}/${id}`
            })
            return;
          }
          if (couponType === 'vip') {
            Toast.warning('你可以在电脑端或App上购买会员');
            return;
          }
          if (couponType === 'classroom') {
            this.$router.push({
              path: 'classroom/explore'
            })
            return;
          }
          this.$router.push({
            path: 'course/explore'
          })
          return;
        }

        Api.receiveCoupon({
          query: { token }
        }).then(res => {
          Toast.success('领取成功');
          item.targetType = res.targetType;
          item.currentUserCoupon = true;
          if (res.targetId != 0) {
            item.target = {
              id: res.targetId
            }
          }
        }).catch(err => {
          Toast.fail(err.message);
        });
      }
    },
  }
</script>
