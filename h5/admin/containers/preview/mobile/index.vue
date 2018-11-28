<template>
  <div class="find-page">
    <div class="find-page__part" v-for="part in parts">
      <e-swipe
        v-if="part.type == 'slide_show'"
        :slides="part.data"
        :feedback="feedback"></e-swipe>
      <e-course-list
        v-if="['classroom', 'course'].includes(part.type)"
        :courseList="part.data"
        :feedback="feedback"
        :typeList="part.type"></e-course-list>
      <e-poster
        v-if="part.type == 'poster'"
        :class="imageMode[part.data.responsive]"
        :poster="part.data"
        :feedback="feedback"></e-poster>
      <e-groupon
        v-if="part.type == 'groupon'"
        :tag="part.data.tag"
        :activity="part.data.activity"></e-groupon>
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
  import groupon from '@/containers/components/e-marketing/e-groupon';
  import { mapActions } from 'vuex';

  export default {
    components: {
      'e-course-list': courseList,
      'e-swipe': swipe,
      'e-poster': poster,
      'e-groupon': groupon,
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
