<template>
  <div>
    <user></user>
    <orders></orders>
  </div>
</template>
<script>
import Orders from '../order/orders.vue';
import User from './user.vue';
import { mapState } from 'vuex';
import preloginMixin from '@/mixins/preLogin';

export default {
  mixins: [preloginMixin],
  components: {
    Orders,
    User
  },
  computed: {
  },
}
</script>

