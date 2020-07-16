<template>
  <van-pull-refresh v-model="refreshing" @refresh="onRefresh">
    <div class="app history-learn">
      <div class="history-learn-list" style>
        <van-list v-model="loading" :finished="finished" @load="onLoad">
          <div v-for="(date, index) in sort" :key="index">
            <div class="history-learn-date van-hairline--bottom">
              {{ date }}
            </div>
            <template v-if="isRequestComplete">
              <e-card
                v-for="(item, index) in lesson[date]"
                :key="index"
                :course="item | cardDataList(item)"
                @toClassroom="toClassroom"
                @toTask="toTask"
                @toCourse="toCourse"
                @toItemBank="toItemBank"
              />
            </template>
          </div>
        </van-list>
      </div>
      <empty
        v-if="noData"
        text="空空如也，暂无内容"
        class="empty__history_learn"
      />
    </div>
  </van-pull-refresh>
</template>

<script>
import ECard from '&/components/e-card/e-course-card';
import empty from '&/components/e-empty/e-empty.vue';
import { formatchinaTime } from '@/utils/date-toolkit';
import cardDataList from '@/utils/filter-card';
import Api from '@/api';
export default {
  name: 'history-learn',
  components: {
    ECard,
    empty,
  },
  data() {
    return {
      course: [],
      lesson: {},
      sort: [],
      isRequestComplete: false,
      loading: false,
      finished: false,
      refreshing: false,
      query: {
        limit: 10,
        offset: 0,
        type: 'task',
      },
      token: '',
    };
  },
  computed: {
    noData: function() {
      return this.isRequestComplete && !this.sort.length;
    },
  },
  created() {
    this.setTitle();
    this.getUserInfo();
  },
  filters: {
    cardDataList,
  },
  methods: {
    setTitle() {
      window.postNativeMessage({
        action: 'kuozhi_native_header',
        data: { title: '历史学习' },
      });
    },
    getHistoryLearn() {
      Api.myhistoryLearn({
        params: this.query,
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Auth-Token': this.token,
        },
      })
        .then(res => {
          this.formateData(res);
          this.isRequestComplete = true;
        })
        .catch(error => {
          this.sendError(error);
        });
    },
    formateData(res) {
      const sort = this.sort;
      res.data.forEach(item => {
        const date = formatchinaTime(new Date(item.date));
        sort.push(date);
        if (!this.lesson[date]) {
          this.$set(this.lesson, date, []);
        }
        this.lesson[date].push(item);
      });
      this.sort = Array.from(new Set(sort));
      this.course = this.course.concat(res.data);
      this.$set(this.query, 'offset', this.course.length);
      this.loading = false;
      if (this.course.length >= res.paging.total) {
        this.finished = true;
      }
    },
    initData() {
      this.sort = [];
      this.course = [];
      this.lesson = {};
      this.query = { limit: 10, offset: 0, type: 'task' };
      this.refreshing = false;
      this.finished = false;
      this.isRequestComplete = false;
    },
    onLoad() {
      if (this.refreshing) {
        this.initData();
        this.getHistoryLearn();
        return;
      }
      if (this.finished || !this.isRequestComplete) {
        return;
      }
      this.getHistoryLearn();
    },
    onRefresh() {
      // 清空列表数据
      this.finished = false;
      // 重新加载数据
      // 将 loading 设置为 true，表示处于加载状态
      this.loading = true;
      this.onLoad();
    },
    getUserInfo() {
      const self = this;
      window.nativeCallback = function(res) {
        self.token = res.token;
        self.getHistoryLearn();
      };
      window.postNativeMessage({
        action: 'kuozhi_login_user',
        data: { force: 1 },
      });
    },
    toClassroom(id) {
      window.postNativeMessage({
        action: 'kuozhi_classroom',
        data: { classroomId: id },
      });
    },
    toTask(task) {
      window.postNativeMessage({
        action: 'kuozhi_learn_task',
        data: { taskId: task.id, taskType: task.type, courseId: task.courseId },
      });
    },
    toCourse(id) {
      window.postNativeMessage({
        action: 'kuozhi_course',
        data: { courseId: id },
      });
    },
    toItemBank(itemBank) {
      window.postNativeMessage({
        action: 'kuozhi_itembank',
        data: itemBank,
      });
    },
    sendError(error) {
      window.postNativeMessage({
        action: 'kuozhi_h5_error',
        data: {
          code: error.code,
          message: error.message,
        },
      });
    },
  },
};
</script>
