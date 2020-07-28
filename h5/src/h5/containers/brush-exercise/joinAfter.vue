<template>
  <div class="">
    <img :src="cover.large" style="width:100%;vertical-align: middle;" />
    <directory :exerciseId="Number(id)" />
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
    return {};
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
