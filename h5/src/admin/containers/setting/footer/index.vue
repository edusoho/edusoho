<template>
  <div v-if="items.length > 0" class="find-footer">
    <div
      v-for="item in items"
      :class="getClassName(item)"
      :style="footerItemStyle"
      :key="item.type"
      class="find-footer-item"
    >
      <div class="find-footer-item__icon">
        <i v-if="item.icon" :class="item.icon" />
        <img v-else class="find-footer-item__icon" :src="item.iconUrl" />
      </div>
      <span class="find-footer-item__text">{{ $t(item.type) }}</span>
    </div>
  </div>
</template>

<script>
const appItems = [
  {
    name: 'find',
    type: 'footer.discover',
    iconUrl: 'static/images/apptab1.png',
  },
  {
    name: 'dynamic',
    type: 'footer.learn',
    iconUrl: 'static/images/apptab2.png',
  },
  {
    name: 'learn',
    type: 'footer.message',
    iconUrl: 'static/images/apptab3.png',
  },
  {
    name: 'my',
    type: 'footer.me',
    iconUrl: 'static/images/apptab4.png',
  },
];
const h5Items = [
  // footer
  {
    name: 'find',
    type: 'footer.discover',
    icon: 'iconfont icon-faxian',
  },
  {
    name: 'learning',
    type: 'footer.learn',
    icon: 'iconfont icon-xuexi',
  },
  {
    name: 'my',
    type: 'footer.me',
    icon: 'iconfont icon-faxian',
  },
];
export default {
  name: 'FindFooter',
  props: {
    // 当前终端 apps、miniprogram、h5
    portal: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      items: [],
    };
  },
  computed: {
    footerItemStyle() {
      return { width: `${100 / this.items.length}%` };
    },
  },
  watch: {
    portal: {
      handler: function(val) {
        this.items = val === 'apps' ? appItems : h5Items;
      },
      immediate: true,
    },
  },
  methods: {
    // 区别app和小程序、微网校的高亮颜色
    getClassName(item) {
      if (item.name === 'find' && this.portal === 'apps') {
        return 'app-active';
      }
      if (item.name === 'find') {
        return 'active';
      }
      return '';
    },
  },
};
</script>
