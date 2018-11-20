<template>
  <div class="comment">
    <template v-for="item in reviews">
      <review :review="item"></review>
    </template>
  </div>
</template>

<script>
import Api from '@/api';
import review from '@/containers/components/e-review';
import moreMask from '@/components/more-mask';
import { Toast, Loading } from 'vant';

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
    const classId = this.$route.params.id
    Api.getReviews({
      query: { classroomId: classId }
    }).then(({ data }) => {
      this.reviews = data;
    }).catch(err => {
      Toast.fail(err.message);
    });
  }

}
</script>
