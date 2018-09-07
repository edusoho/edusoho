<template>
   <div class="find-footer">
    <div class="find-footer-item" v-for="item in items"
        :class="{ active: item.name === 'find' }"
        :style="footerItemStyle"
        :key="item.type">
      <img class="find-footer-item__icon" :src="item.name === 'find' ? item.active : item.normal" />
      <span class="find-footer-item__text">{{ item.type }}</span>
    </div>
  </div>
</template>

<script>
import items from '@/utils/footer-config'

export default {
  name: 'find-footer',
  data () {
    return {
      items,
    }
  },
  computed: {
    footerItemStyle() {
      return { width: `${100/items.length}%` }
    },
  },
}
</script>
