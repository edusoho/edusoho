<template>
  <div class="find-footer">
    <div
      v-for="item in items"
      :class="{ active: item.name === 'find' }"
      :style="footerItemStyle"
      :key="item.type"
      class="find-footer-item">
      <img :src="item.name === 'find' ? item.active : item.normal" class="find-footer-item__icon" >
      <span class="find-footer-item__text">{{ item.type }}</span>
    </div>
  </div>
</template>

<script>
import items from '@/utils/footer-config'

export default {
  name: 'FindFooter',
  data() {
    return {
      items
    }
  },
  computed: {
    footerItemStyle() {
      return { width: `${100 / items.length}%` }
    }
  }
}
</script>
