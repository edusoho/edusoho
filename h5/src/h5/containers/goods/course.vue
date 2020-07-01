<template>
  <div class="goods">
    <detail :details="details" />
    <info :details="details" />
  </div>
</template>

<script>
import Detail from './detail';
import Info from './info';
import Api from '@/api';
import { Toast } from 'vant'
export default {
  data() {
    return {
      details: {}
    }
  },
  components: {
    Detail,
    Info
  },
  methods: {
    getGoodsCourse() {
      Api.getGoodsCourse({
        query: {
          id: this.$route.params.id
        }
      }).then(res => {
        this.details = res;
      }).catch(err => {
        Toast.fail(err.message);
      });
    }
  },
  created() {
    this.getGoodsCourse();
  },
  watch: {
    // 如果路由发生变化，再次执行该方法
    "$route": "getGoodsCourse"
  }
}
</script>