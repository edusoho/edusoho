<template>
  <div v-if="items.length>0" class="find-footer">
    <div
      v-for="item in items"
      :class="getClassName(item)"
      :style="footerItemStyle"
      :key="item.type"
      class="find-footer-item"
    >
      <div class="find-footer-item__icon " >
        <i :class="item.icon"/>
      </div>
      <!-- <img class="find-footer-item__icon" :src="item.name === 'find' ? item.active : item.normal" /> -->
      <span class="find-footer-item__text">{{ item.type }}</span>
    </div>
  </div>
</template>

<script>
const appItems = [
  {
    name: 'dynamic',
    type: '动态',
    icon: 'iconfont icon-dongtai'
  },
  {
    name: 'find',
    type: '发现',
    icon: 'iconfont icon-faxian11'
  },
  {
    name: 'my',
    type: '我',
    icon: 'iconfont icon-wo'
  }
]
const h5Items = [ // footer
  {
    name: 'find',
    type: '发现',
    icon: 'iconfont icon-faxian'
  },
  {
    name: 'learning',
    type: '学习',
    icon: 'iconfont icon-xuexi'
  },
  {
    name: 'my',
    type: '我的',
    icon: 'iconfont icon-faxian'
  }
]
export default {
  name: 'FindFooter',
  props: {
    // 当前终端 apps、miniprogram、h5
    portal: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      items: []
    }
  },
  computed: {
    footerItemStyle() {
      return { width: `${100 / this.items.length}%` }
    }
  },
  watch: {
    portal: {
      handler: function(val) {
        this.items = (val === 'apps') ? appItems : h5Items
      },
      immediate: true
    }
  },
  methods: {
    // 区别app和小程序、微网校的高亮颜色
    getClassName(item) {
      if (item.name === 'find' && this.portal === 'apps') {
        return 'app-active'
      }
      if (item.name === 'find') {
        return 'active'
      }
      return ''
    }
  }
}
</script>
