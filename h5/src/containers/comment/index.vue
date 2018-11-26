<template>
  <div class="comment">
    <template v-for="item in reviews">
      <review :review="item" :disableMask="false"></review>
    </template>
  </div>
</template>

<script>
import Api from '@/api';
import review from '@/containers/components/e-review';
import { Toast } from 'vant';

export default {
  name: 'comment',
  components: {
    review,
  },
  data () {
    return {
      reviews: [],
    };
  },
  created() {
    const id = this.$route.params.id
    const type = this.$route.query.type

    const ApiType = {
      course: 'getCourseReviews',
      classroom: 'getClassroomReviews',
    }
    Api[ApiType[type]]({
      query: { id }
    }).then(({ data }) => {
      this.reviews = data;
    }).catch(err => {
      Toast.fail(err.message);
    });
  }

}
</script>
