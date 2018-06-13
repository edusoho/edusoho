<template>
  <div>
    <user></user>
    <orders></orders>
  </div>
</template>
<script>
import Orders from '../order/orders.vue';
import User from './user.vue';

export default {
  components: {
    Orders,
    User
  }
}
</script>

