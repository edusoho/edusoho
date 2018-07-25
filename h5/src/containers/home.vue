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
export default {
  data() {
    return {
      active: 0,
      items: [
        {
          name: 'find',
          type: '发现',
          normal: 'static/images/explore.png',
          active: 'static/images/exploreHL.png'
        },
        {
          name: 'learning',
          type: '学习',
          normal: 'static/images/learning.png',
          active: 'static/images/learningHL.png'
        },
        {
          name: 'my',
          type: '我的',
          normal: 'static/images/me.png',
          active: 'static/images/meHL.png'
        },
      ],
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
  methods: {
    onChange(index) {
      this.$router.push({
        name: this.items[index].name
      })
    }
  },
}
</script>


