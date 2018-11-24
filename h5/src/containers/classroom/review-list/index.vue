<template>
  <e-panel :title="title" :needFlex="false" :defaulValue="defaulValue">
    <moreMask v-if="reviews.length" @maskLoadMore="loadMore" :disabled="diableMask" :forceShow="!diableMask">
      <template v-for="item in reviews">
        <review :review="item"></review>
      </template>
    </moreMask>
  </e-panel>
</template>
<script>
import review from '@/containers/components/e-review';
import moreMask from '@/components/more-mask';

export default {
  name: 'reviewList',
  components: {
    review,
    moreMask
  },
  props: ['reviews', 'title', 'classId', 'defaulValue', 'type'],
  data() {
    return {
      maxShowNum: 5,
    };
  },
  methods: {
    loadMore() {
      this.$router.push({
        path: `/comment/${this.classId}`,
        query: {
          type: this.type,
        }
      });
    }
  },
  computed: {
    diableMask() {
      return this.reviews.length < this.maxShowNum;
    }
  }
}
</script>

