<template>
  <div class="app history-learn">
    <e-navbar title="历史学习" />
    <div class="history-learn-list" style="">
      <van-list v-model="loading" :finished="finished" finished-text="没有更多了" @load="onLoad">
        <e-card
        v-if="isRequestComplete"
        v-for="(item,index) in course"
        :key="index"
        :course="item"
      />
      </van-list>
    </div>
  </div>
</template>

<script>
import ENavbar from "&/components/e-navbar/e-navbar.vue";
import ECard from "&/components/e-course-card/e-course-task";
import empty from "&/components/e-empty/e-empty.vue";
import Api from "@/api";
export default {
  name: "history-learn",
  components: {
    ENavbar,
    ECard,
    empty
  },
  data() {
    return {
      course: [],
      isRequestComplete:false,
      loading: false,
      finished: false,
      query: {
        limit: 10,
        offset: 0,
        type: "task"
      }
    };
  },
  created() {
    this.getHistoryLearn();
  },
  methods: {
    getHistoryLearn() {
      Api.myhistoryLearn({ params: this.query }).then(res => {
        this.formateData(res);
        this.isRequestComplete=true
      });
    },
    formateData(res) {
      this.course = this.course.concat(res.data);
      this.$set(this.query, "offset", this.course.length);
      this.loading = false;
      console.log(this.course.length)
      console.log(res.paging.total)
      if (this.course.length >= res.paging.total) {
        this.finished = true;
      }
    },
    onLoad() {
      if( this.finished ){
          return
      }
      this.getHistoryLearn();
    }
  }
};
</script>

<style>
</style>