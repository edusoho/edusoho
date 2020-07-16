<template>
  <div class="brush-exercise-directory-swipertab">
    <van-tabs :border="false" @click="checkedTab" :ellipsis="false">
      <van-tab
        v-for="(item, index) in tabs"
        :title="item"
        :key="index"
      ></van-tab>
    </van-tabs>
  </div>
</template>

<script>
export default {
  components: {},
  data() {
    return {
      tabs: ['章节测试', '模拟卷', '测试卷', '章节练习1', '章节练习2'],
    };
  },
  computed: {},
  watch: {},
  created() {},
  methods: {
    checkedTab(e) {
      console.log(e);
    },
  },
};
</script>
