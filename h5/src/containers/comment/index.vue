<template>
  <div class="comment">
    <van-list style="margin-top: 0;" v-model="loading" :finished="finished" @load="onLoad">
      <template v-for="item in reviews">
        <review :isClass="isClass" :review="item" :disableMask="false" timeFormat="complete" :course="item.course"></review>
      </template>
    </van-list>
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
      loading: false,
      finished: false,
      offset: 0,
      ApiType: {
        course: 'getCourseReviews',
        classroom: 'getClassroomReviews',
      }
    };
  },
  created() {
  },
  computed: {
    isClass() {
      return this.type === 'classroom';
    }
  },
  methods: {
    onLoad() {
      console.log(8888)
      const id = this.$route.params.id
      const type = this.$route.query.type
      const ApiType = this.ApiType

      Api[ApiType[type]]({
        query: { id },
        params: {
          offset: this.offset
        }
      }).then(({ data, paging }) => {
        const reviews = this.reviews
        const total = paging.total

        this.reviews = [...reviews, ...data]
        this.offset = this.reviews.length
        if (this.reviews.length == total) {
          this.finished = true
        }
        this.loading = false
      }).catch(err => {
        Toast.fail(err.message)
        this.loading = false
      });
    }
  }
}
</script>
