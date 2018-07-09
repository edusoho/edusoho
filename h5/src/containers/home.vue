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
          normal: '/static/images/explore.png',
          active: '/static/images/exploreHL.png'
        },
        {
          name: 'learning',
          type: '学习',
          normal: '/static/images/learning.png',
          active: '/static/images/learningHL.png'
        },
        {
          name: 'my',
          type: '我的',
          normal: '/static/images/me.png',
          active: '/static/images/meHL.png'
        },
      ],
    }
  },
  watch: {
    $route() {
      this.active = this.judgeIndex(this.$route.name);
    }
  },
  created() {
    this.items.some((item, index) => {
      const redirect = this.$route.query.redirect || '';

      item.name === ( redirect || this.$route.name ) && ( this.active = index );
    })
  },
  methods: {
    onChange(index) {
      this.$router.push({
        name: this.items[index].name
      })
    },
    judgeIndex(routerName) {
      const items = this.items;
      const result = items.map((item, index) => {
        return item.name == routerName;
      });
      return result.indexOf(true)
    }
  },
}
</script>


