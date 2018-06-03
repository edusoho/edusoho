<template>
  <div>
    <user></user>
    <order></order>
  </div>
</template>
<script>
import Order from './order.vue';
import User from './user.vue';

export default {
  components: {
    Order,
    User
  }
}
</script>

