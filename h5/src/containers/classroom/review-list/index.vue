<template>
  <e-panel :title="title" :needFlex="false">
    <template v-for="item in reviews">
      <review :review="item"></review>
    </template>
  </e-panel>
</template>
<script>
import review from '@/containers/components/e-review';

export default {
  name: 'reviewList',
  components: {
    review
  },
  props: ['reviews', 'title'],
}
</script>

