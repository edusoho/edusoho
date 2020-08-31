<template>
  <div class="comment">
    <van-list v-model="loading" :finished="finished" style="margin-top: 0;" @load="onLoad">
      <template v-for="item in reviews">
        <review :is-class="isClass" :review="item" :disable-mask="false" :course="item.course" time-format="complete"/>
      </template>
    </van-list>
  </div>
</template>

<script>
import Api from '@/api'
import review from '&/components/e-review'
import { Toast } from 'vant'

export default {
  name: 'Comment',
  components: {
    review
  },
  data() {
    return {
      reviews: [],
      loading: false,
      finished: false,
      offset: 0,
      ApiType: {
        course: 'getCourseReviews',
        classroom: 'getClassroomReviews',
        item_bank_exercise: 'getBankReviews',
      }
    }
  },
  computed: {
    isClass() {
      return this.type === 'classroom'
    }
  },
  created() {
  },
  methods: {
    onLoad() {
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
      })
    }
  }
}
</script>
