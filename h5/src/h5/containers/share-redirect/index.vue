<template>
  <div></div>
</template>

<script>
import activityMixin from '@/mixins/activity';
import redirectMixin from '@/mixins/saveRedirect';

export default {
  name: 'share-redirect',
  mixins: [activityMixin, redirectMixin],
  data () {
    return {

    }
  },
  mounted() {
    this.afterLogin();
  }
}
</script>
