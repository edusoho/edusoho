<template>
  <div>
    <e-loading v-if="isLoading"></e-loading>
    <user></user>
    <orders></orders>
  </div>
</template>
<script>
import Orders from '../order/orders.vue';
import User from './user.vue';
import { mapState } from 'vuex';

export default {
  components: {
    Orders,
    User
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading
    })
  },
}
</script>

