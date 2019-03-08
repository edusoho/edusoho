<template>
  <div class="find-page">
    <div class="find-page__part" v-for="part in parts">
      <e-swipe
        v-if="part.type == 'slide_show'"
        :slides="part.data"
        :feedback="feedback"></e-swipe>
      <e-course-list
        v-if="['classroom_list', 'course_list'].includes(part.type)"
        :courseList="part.data"
        class="gray-border-bottom"
        :feedback="feedback"
        :typeList="part.type"></e-course-list>
      <e-poster
        v-if="part.type == 'poster'"
        :class="imageMode[part.data.responsive]"
        :poster="part.data"
        :feedback="feedback"></e-poster>
      <e-market-part
        class="gray-border-bottom"
        v-if="['groupon', 'cut', 'seckill'].includes(part.type)"
        :tag="part.data.tag"
        :type="part.type"
        :showTitle="part.data.titleShow"
        :activity="part.data.activity"></e-market-part>
      <div class="coupon-preview__container gray-border-bottom" v-if="part.type == 'coupon'">
        <e-coupon-list
          :coupons="part.data.items"
          :feedback="true"
          :showTitle="part.data.titleShow"></e-coupon-list>
      </div>
      <e-vip-list
        v-if="part.type == 'vip'"
        class="gray-border-bottom"
        :items="part.data.items"
        :feedback="true"
        :sort="part.data.sort"
        :showTitle="part.data.titleShow"></e-vip-list>
    </div>
    <!-- 垫底的 -->
    <div class="mt50"></div>
  </div>
</template>

<script>
  import pathName2Portal from '@admin/config/api-portal-config';
  import courseList from '@/containers/components/e-course-list/e-course-list.vue';
  import poster from '@/containers/components/e-poster/e-poster.vue';
  import swipe from '@/containers/components/e-swipe/e-swipe.vue';
  import marketPart from '@/containers/components/e-marketing/e-activity';
  import coupon from '@/containers/components/e-coupon-list/e-coupon-list';
  import vipList from '@/containers/components/e-vip-list/e-vip-list';
  import { mapActions } from 'vuex';

  export default {
    components: {
      'e-course-list': courseList,
      'e-swipe': swipe,
      'e-poster': poster,
      'e-market-part': marketPart,
      'e-coupon-list': coupon,
      'e-vip-list': vipList
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
        from: this.$route.query.from,
      };
    },
    created() {
      this.getDraft({
        portal: pathName2Portal[this.from],
        type: 'discovery',
        mode: 'draft',
      }).then(res => {
        this.parts = Object.values(res);
      }).catch(err => {
        console.log(err, 'error');
      });
    },
    methods: {
       ...mapActions([
        'getDraft',
      ]),
    }
  }
</script>
