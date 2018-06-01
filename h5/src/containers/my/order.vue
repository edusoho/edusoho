<template>
  <div class="order">
    <span class='order-title'>我的订单</span>

    <div class="order-container__empty" v-if="empty">
      <img src="/static/images/orderEmpty.png" >
      <span>暂无订单记录</span>
    </div>
   
    <template v-else>
      
    </template>
  </div>
</template>
<script>
export default {
  data() {
    return {
      empty: true
    }
  }
}
</script>
