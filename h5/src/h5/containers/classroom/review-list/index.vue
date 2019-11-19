<template>
  <e-panel :title="title" :need-flex="false" :defaul-value="defaulValue">
    <moreMask v-if="reviews.length" :max-height="400" @maskLoadMore="loadMore">
      <!-- <moreMask v-if="reviews.length" @maskLoadMore="loadMore" :disabled="diableMask" :forceShow="!diableMask"> -->
      <template v-for="item in reviews">
        <review :review="item" :is-class="isClass" :course="item.course"/>
      </template>
    </moreMask>
  </e-panel>
</template>
<script>
import review from '&/components/e-review'
import moreMask from '@/components/more-mask'

export default {
  name: 'ReviewList',
  components: {
    review,
    moreMask
  },
  props: ['reviews', 'title', 'targetId', 'defaulValue', 'type'],
  data() {
    return {
      maxShowNum: 5
    }
  },
  computed: {
    isClass() {
      return this.type === 'classroom'
    }
    // diableMask() {
    //   return this.reviews.length < this.maxShowNum;
    // }
  },
  methods: {
    loadMore() {
      this.$router.push({
        path: `/comment/${this.targetId}`,
        query: {
          type: this.type
        }
      })
    }
  }
}
</script>

