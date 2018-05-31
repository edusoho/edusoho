<template>
  <div class="home">
    <van-tabbar v-model="active" @change="onChange">
      <van-tabbar-item>
        <span>发现</span>
        <template slot="icon" slot-scope="props">
          <img :src="props.active ? findIcon.active : findIcon.normal" />
        </template>
      </van-tabbar-item>
      <van-tabbar-item>
        <span>学习</span>
        <template slot="icon" slot-scope="props">
          <img :src="props.active ? learnIcon.active : learnIcon.normal" />
        </template>
      </van-tabbar-item>
      <van-tabbar-item>
        <span>我的</span>
        <template slot="icon" slot-scope="props">
          <img :src="props.active ? myIcon.active : myIcon.normal" />
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
      findIcon: {
        normal: '/static/images/explore.png',
        active: '/static/images/exploreHL.png'
      },
      learnIcon: {
        normal: '/static/images/learning.png',
        active: '/static/images/learningHL.png'
      },
      myIcon: {
        normal: '/static/images/me.png',
        active: '/static/images/meHL.png'
      },
    }
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


