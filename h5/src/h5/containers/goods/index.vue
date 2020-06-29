<template>
  <div class="goods">
    <e-loading v-if="isLoading"/>
    <detail :details="details" />
    <info :details="details" />
  </div>
</template>

<script>
import Detail from './detail';
import Info from './info';
import { mapState, mapActions, mapMutations } from 'vuex';
export default {
  components: {
    Detail,
    Info
  },
  computed: {
    ...mapState('course', {
      selectedPlanIndex: state => state.selectedPlanIndex,
      joinStatus: state => state.joinStatus,
      details: state => state.details,
      selectedPlanId: state => state.selectedPlanId
    }),
    ...mapState({
      isLoading: state => state.isLoading
    })
  },
  methods: {
    ...mapActions('course', [
      'getCourseLessons'
    ]),
    getData(){
      this.getCourseLessons({
        // courseId: this.$route.params.id
        courseId: 567
      }).then(res => {
        // this.joinStatusChange(res.member)
        console.log(this.details);
      })
    },
  },
  created() {
    this.getData();
  }
}
</script>