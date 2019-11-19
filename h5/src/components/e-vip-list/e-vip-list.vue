<template>
  <div :class="items.length <= 1 ? 'single' : ''" class="e-vip-list">
    <div v-if="showTitle === 'show'" class="e-vip-list__title text-18 mb20">会员专区</div>
    <van-swipe v-if="items.length > 1" :width="200" :show-indicators="false" :loop="false" :touchable="true">
      <van-swipe-item v-for="(item, index) in items" :key="index">
        <vip-item :item="item" :feedback="feedback"/>
      </van-swipe-item>
    </van-swipe>
    <!-- 单个会员 -->
    <vip-item
      v-for="(item, index) in items"
      v-else
      :item="item"
      :key="index"
      :feedback="feedback"
      class="single" />
  </div>
</template>

<script>
import vipItem from './item'

export default {
  name: 'EVipList',
  components: {
    vipItem
  },
  props: ['items', 'feedback', 'sort', 'showTitle']
}
</script>
