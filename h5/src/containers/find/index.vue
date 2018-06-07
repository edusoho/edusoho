<template>
  <div class="find-page">
    <div class="find-page__part" v-for="part in parts">
      <e-swiper v-if="part.class == 'slideshow'"></e-swiper>
      <e-course-list v-if="part.class == 'courselist'" :courseList="part.data"></e-course-list>
      <e-promotion v-if="part.class == 'image'"></e-promotion>
    </div>
  </div>
</template>

<script>
  import courseList from '../components/e-course-list/e-course-list.vue';
  import promotion from '../components/e-promotion/e-promotion.vue'
  import swiper from '../components/e-swiper/e-swiper.vue'
  import Api from '@/api';

  export default {
    components: {
      'e-course-list': courseList,
      'e-swiper': swiper,
      'e-promotion': promotion,
    },
    data () {
      return {
        parts: [],
        coursesList: [],
      };
    },
    methods: {
      dataFilter(data) {
        data.array.forEach(element => {
          if (element.class !== 'courselist') return;
        });
      }
    },
    created() {
      Api.discoveries()
        .then((data) => {
          this.parts = data;
          console.log(data, 'success');
        })
        .catch((err) => {
          console.log(err, 'error');
        });
    },
  }
</script>
