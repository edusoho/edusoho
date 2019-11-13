<template>
  <div class="home">
    <van-tabbar v-model="active">
      <van-tabbar-item v-for="item in items" :key="item.type">
        <span>{{ item.type }}</span>
        <template slot="icon" slot-scope="props">
          <img :src="props.active ? item.active : item.normal" />
        </template>
      </van-tabbar-item>
    </van-tabbar>

    <find-view :feedback="false"></find-view>
  </div>
</template>

<script>
import items from '@/utils/footer-config'
import findView from '@/containers/find/index'

export default {
  components: {
    findView,
  },
  data() {
    return {
      active: 0,
      items,
    }
  },
}
</script>


