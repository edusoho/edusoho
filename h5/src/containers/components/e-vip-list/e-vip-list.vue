<template>
  <div class="e-vip-list">
    <van-swipe v-if="items.length > 1" :width="200" :show-indicators="false" :loop="false" :touchable="true">
      <van-swipe-item v-for="(item, index) in items" :key="index">
        <vip-item :item="item" :feedback="feedback"/>
     </van-swipe-item>
    </van-swipe>
    <!-- 单个会员 -->
    <vip-item v-else :item="item" v-for="(item, index) in items" :key="index"
      :feedback="feedback" />
  </div>
</template>

<script>
import vipItem from './item';

export default {
  name: 'e-vip-list',
  props: ['items', 'feedback', 'sort'],
  components: {
    vipItem,
  },
  data () {
    return {

    }
  },
  methods: {
  }
}
</script>
