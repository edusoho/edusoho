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
      ]
    }
  },
  created() {
    this.items.some((item, index) => {
      item.name === this.$route.name && (this.active = index);
    })
  },
  methods: {
    onChange(index) {
      switch (index) {
        case 0:
          this.$router.push({
            name: 'find',
          });
          break;
        case 1:
          this.$router.push({
            name: 'learning',
          });
          break;
        case 2:
          this.$router.push({
            name: 'my',
          });
          break;
      }
    }
  },
}
</script>


