<template>
  <div class="home">
    <van-tabbar v-model="active" @change="onChange">
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

          item.name === ( redirect || to.name ) && ( this.active = index );
        })
      }
    }
  },
  created() {
    const {preview, token} = this.$route.query
    console.error(preview);
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
      this.$router.push({
        name: this.items[index].name
      })
    }
  },
}
</script>


