<template>
  <div class="home">
    <van-tabbar v-model="active" @change="onChange" :class="{'van-tabbar--iphonex' : firstVisit}">
      <van-tabbar-item v-for="item in items" :key="item.type">
        <span>{{ item.type }}</span>
        <template slot="icon" slot-scope="props">
          <img :src="props.active ? item.active : item.normal" />
        </template>
      </van-tabbar-item>
    </van-tabbar>

    <keep-alive>
      <router-view></router-view>
    </keep-alive>
  </div>
</template>

<script>
import items from '@/utils/footer-config'

export default {
  data() {
    return {
      active: 0,
      firstVisit: 0,
      items,
    }
  },
  watch: {
    '$route': {
      deep: true,
      immediate: true,
      handler(to) {
        this.items.some((item, index) => {
          const redirect = to.query.redirect || '';
          if (item.name === to.name) {
            this.active = index
          } else if (redirect && redirect.includes('orders')) { // prelogin 页面对应tab显示不正确
            this.active = 2
          } else if (redirect && redirect.includes('learning')) { // prelogin 页面对应tab显示不正确
            this.active = 1
          }
        })
      }
    }
  },
  created() {
    // iphonex 首次进入底部间距优化
    localStorage.setItem('firstVisit', 0);
    const isFirstVisit = Number(localStorage.getItem('firstVisit'));
    if (!isFirstVisit) {
      this.firstVisit = 1;
      localStorage.setItem('firstVisit', 1);
    }
    const {preview, token} = this.$route.query
    if (!isNaN(preview) || preview == 1) { // 手机预览页面
      this.$router.push({
        name: 'preview',
        query: {
          preview,
          token
        },
      })
    }
  },
  methods: {
    onChange(index) {
      this.firstVisit = 0;
      this.$router.push({
        name: this.items[index].name
      })
    }
  },
}
</script>


