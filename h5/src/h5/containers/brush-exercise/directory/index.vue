<template>
  <div class="brush-exercise-directory" style="background:#F5F5F5">
    <swiperTab @changModule="changModule" />
    <exam
      v-if="currentType === 'assessment'"
      :exercise="list[moduleId].data"
      :isLoading="isLoading"
      @loadMore="loadMore"
      :finished="list[moduleId].finished"
    >
    </exam>

    <exercise
      v-if="currentType === 'chapter'"
      :exercise="list[moduleId].data"
      :isLoading="isLoading"
    ></exercise>
  </div>
</template>

<script>
// import { throttle } from '@/utils/utils.js';
import { mapState, mapActions } from 'vuex';
import swiperTab from './components/swiper-tab.vue';
import exam from './components/exam.vue';
import exercise from './components/exercise.vue';
import Api from '@/api';
const CHAPTER = 'chapter'; // 章节
// eslint-disable-next-line no-unused-vars
const ASSESSMENT = 'assessment'; // 模拟卷
const defaultData = {
  data: [],
  paging: { limit: 5, offset: 0, total: 0 },
  finished: false,
};
export default {
  components: {
    swiperTab,
    exam,
    exercise,
  },
  data() {
    return {
      currentType: '',
      isLoading: true,
      moduleId: '',
      list: {},
      timer: null,
    };
  },
  props: {
    exerciseId: {
      type: Number,
      default: -1,
    },
  },
  computed: {
    ...mapState('ItemBank', {
      module: state => state.ItemBankModules,
    }),
  },
  watch: {},
  created() {
    this.getData();
  },
  methods: {
    ...mapActions('ItemBank', ['getDirectoryModules']),
    getData() {
      this.getDirectoryModules(this.exerciseId).then(res => {
        if (res.length) {
          this.currentType = res[0].type;
          this.list[res[0].id] = JSON.parse(JSON.stringify(defaultData));
          this.moduleId = res[0].id;
          this.changeData(res[0].id);
        }
      });
    },
    judegIsAll(ItemBankInfomation) {
      return (
        this.list[this.moduleId].data.length >= ItemBankInfomation.paging.total
      );
    },
    getItemBankAssessments(more = false) {
      const moduleId = this.moduleId;
      this.isLoading = true;
      const query = {
        exerciseId: Number(this.exerciseId),
        moduleId: Number(moduleId),
      };
      const params = {
        offset: this.list[moduleId].paging.offset,
        limit: this.list[moduleId].paging.limit,
      };
      // debugger;
      Api.getItemBankAssessments({ query, params }).then(res => {
        if (more) {
          this.list[moduleId].data = this.list[moduleId].data.concat(res.data);
        } else {
          this.list[moduleId].data = res.data;
        }
        // this.list[moduleId].data = this.list[moduleId].data.concat(res.data);
        this.list[moduleId].finished = this.judegIsAll(res);
        if (!this.list[moduleId].finished) {
          this.list[moduleId].paging.offset = this.list[moduleId].data.length;
        }
        this.isLoading = false;
        this.$forceUpdate();
      });
    },
    gettemBankCategories() {
      const moduleId = this.moduleId;
      this.isLoading = true;
      const query = {
        exerciseId: Number(this.exerciseId),
        moduleId: Number(moduleId),
      };
      Api.gettemBankCategories({ query }).then(res => {
        this.list[moduleId].data = res;
        this.isLoading = false;
      });
    },
    changeData() {
      this.exercise = [];
      this.currentType === CHAPTER
        ? this.gettemBankCategories()
        : this.getItemBankAssessments();
    },
    changModule(data) {
      this.currentType = data.type;
      this.moduleId = data.id;
      this.list[data.id] = JSON.parse(JSON.stringify(defaultData));
      this.changeData(data.id);
    },
    loadMore() {
      if (this.timer) {
        clearTimeout(this.timer);
      }
      this.timer = setTimeout(() => {
        if (!this.list[this.moduleId].finished && !this.isLoading) {
          this.getItemBankAssessments(true);
        }
      }, 1000);
    },
  },
};
</script>
