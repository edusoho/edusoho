<template>
  <div class="">
    <img :src="cover.large" style="width:100%;vertical-align: middle;" />
    <van-tabs v-model="active" sticky>
      <van-tab title="课程目录">
        <directory :exerciseId="Number(id)" />
      </van-tab>
      <van-tab title="学员评价"> </van-tab>
    </van-tabs>
  </div>
</template>

<script>
import { createNamespacedHelpers } from 'vuex';
import directory from './directory';
const { mapState } = createNamespacedHelpers('ItemBank');
export default {
  components: {
    directory,
  },
  data() {
    return {
      active: 1,
    };
  },
  computed: {
    ...mapState({
      cover: state => state.ItemBankExercise.cover,
      id: state => state.ItemBankExercise.id,
    }),
  },
  watch: {},
  created() {},
  methods: {},
};
</script>
