<template>
  <div/>
</template>

<script>
import activityMixin from '@/mixins/activity'
import redirectMixin from '@/mixins/saveRedirect'

export default {
  name: 'ShareRedirect',
  mixins: [activityMixin, redirectMixin],
  data() {
    return {

    }
  },
  mounted() {
    this.afterLogin()
  }
}
</script>
