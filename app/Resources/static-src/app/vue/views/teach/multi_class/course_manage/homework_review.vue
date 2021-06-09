<template>
  <div>
    <a-spin :spinning="getListLoading">
      <a-table
        :columns="columns"
        :data-source="homeworksList"
        :pagination="paging"
        :rowKey="record => record.id"
        @change="change"
      >
        <class-name slot="lesson" slot-scope="lesson, record" :record="record" />
        <template slot="name" slot-scope="name, record">{{ record.tasks.assessment.name }}</template>
        <template slot="item_count" slot-scope="item_count, record">{{ record.tasks.assessment.item_count }}</template>
        <a slot="finished" slot-scope="finished, record" @click="showResultListModal('finished', record.tasks)">
          {{ record.tasks.assessmentStatusNum.finished || 0 }}
        </a>
        <a slot="reviewing" slot-scope="reviewing, record" @click="showResultListModal('reviewing', record.tasks)">
          {{ record.tasks.assessmentStatusNum.reviewing || 0 }}
        </a>
        <a slot="doing" slot-scope="doing, record" @click="showResultListModal('doing', record.tasks)">
          {{ record.tasks.assessmentStatusNum.doing || 0 }}
        </a>
        <template slot="startTime" slot-scope="startTime, record">
          {{ $dateFormat(record.tasks.createdTime, 'YYYY-MM-DD HH:mm') }}
        </template>
        <template slot="action" slot-scope="text, record">
          <a-space size="large">
            <a-button type="link">
              <a class="ant-dropdown-link"
                :href="`/course/${record.tasks.courseId}/manage/exam/activity/${record.tasks.activityId}/analysis`"
                target="_blank">
                答题分布
              </a>
            </a-button>
            <a-button type="link"
              data-target="#modal"
              data-toggle="modal"
              :data-url="`/course/${record.tasks.courseId}/activity/${record.tasks.activityId}/testpaper/graph`">
              成绩分布
            </a-button>
          </a-space>

        </template>
      </a-table>
    </a-spin>

    <a-modal
      :visible="modalVisible"
      :footer="null"
      :title="currentTask.assessment ? currentTask.assessment.name : ''"
      :width="920"
      @cancel="modalVisible = false"
    >
      <a-tabs v-model="currentTab">
        <a-tab-pane :key="0" tab="全部"></a-tab-pane>
        <a-tab-pane :key="1" tab="未批阅"></a-tab-pane>
        <a-tab-pane :key="2" tab="进行中"></a-tab-pane>
        <a-tab-pane :key="3" tab="已批阅"></a-tab-pane>
      </a-tabs>
      <!-- TODO 翻页未做 -->
      <a-table
        v-if="examResults"
        :columns="resultColumns"
        :data-source="examResults.data"
        :pagination="examResults.paging"
      >
        <template slot="nickname" slot-scope="nickname, record">{{ record.userInfo.nickname }}</template>
        <template slot="grade" slot-scope="grade, record">{{ record.status === 'reviewing' ? '--' : gradeMap[record.answerReportInfo.grade] }}</template>
        <template slot="teacherInfo" slot-scope="teacherInfo, record">{{ record.teacherInfo.nickname || '--' }}</template>
        <template slot="status" slot-scope="status">
          {{ statusMap[status] }}
        </template>
        <template slot="end_time" slot-scope="end_time">
          {{ $dateFormat(end_time, 'YYYY-MM-DD HH:mm') }}
        </template>
        <template slot="action" slot-scope="text, record">
          <!-- TODO 这里要判断是不是老师 -->
          <!-- TODO 这里要判断来源是classroom还是course -->
          <a v-if="record.status === 'reviewing'"
            :href="currentTask.type === 'testpaper' ? `/course/${currentTask.courseId}/manage/testpaper/${record.id}/check?action=check` : `/course/${currentTask.courseId}/manage/homework/${record.id}/check?action=check`"
            target="_blank">去批阅</a>
          <a v-else-if="record.status === 'finished'"
            :href="`/homework/result/${record.id}/show?action=check`"
            target="_blank">查看结果</a>
        </template>
      </a-table>
    </a-modal>
  </div>
</template>

<script>
import { MultiClassExam } from 'common/vue/service';
import ClassName from './ClassName.vue';
import _ from '@codeages/utils';

const columns = [
  {
    title: "课时",
    dataIndex: "lesson",
    scopedSlots: { customRender: "lesson" },
  },
  {
    title: "作业/考试",
    dataIndex: "name",
    scopedSlots: { customRender: "name" },
    filters: [
      { text: "作业", value: "homework" },
      { text: "考试", value: "testpaper" },
    ],
  },
  {
    title: "题量",
    dataIndex: "item_count",
    scopedSlots: { customRender: "item_count" },
  },
  {
    title: "已批",
    dataIndex: "finished",
    scopedSlots: { customRender: "finished" },
  },
  {
    title: "未批",
    dataIndex: "reviewing",
    scopedSlots: { customRender: "reviewing" },
  },
  {
    title: "未交",
    dataIndex: "doing",
    scopedSlots: { customRender: "doing" },
  },
  {
    title: "开始时间",
    dataIndex: "startTime",
    scopedSlots: { customRender: "startTime" },
  },
  {
    title: "操作",
    dataIndex: "action",
    scopedSlots: { customRender: "action" },
  },
];

const resultColumns = [
  {
    title: '姓名',
    dataIndex: 'nickname',
    scopedSlots: { customRender: "nickname" },
  },
  {
    title: '成绩',
    dataIndex: 'grade',
    scopedSlots: { customRender: "grade" },
  },
  {
    title: '提交时间',
    dataIndex: 'end_time',
    scopedSlots: { customRender: "end_time" },
  },
  {
    title: '状态',
    dataIndex: 'status',
    scopedSlots: { customRender: "status" },
  },
  {
    title: '批阅人',
    dataIndex: 'teacherInfo',
    scopedSlots: { customRender: "teacherInfo" },
  },
  {
    title: '操作',
    dataIndex: "action",
    scopedSlots: { customRender: "action" },
  }
]

export default {
  name: "HomeWorkReview",
  components: {
    ClassName
  },
  data() {
    return {
      columns,
      resultColumns,
      getListLoading: false,
      homeworksList: [],
      examResultList: {},
      paging: {
        total: 0,
        offset: 0,
        pageSize: 10,
      },
      modalVisible: false,
      currentTab: 0,
      currentTask: {},
      status: ['all', 'reviewing', 'doing', 'finished'],
      statusMap: {
        doing: '进行中',
        paused: '暂停',
        reviewing: '未批阅',
        finished: '已批阅',
      },
      gradeMap: {
        excellent: '优秀',
        good: '良好',
        passed: '合格',
        unpassed: '不合格'
      }
    };
  },
  watch: {
    currentTab(currentTab) {
      this.getExamResults(currentTab)
    },
  },
  computed: {
    examResults() {
      const key = this.status[this.currentTab];

      return this.examResultList[key]
    }
  },
  created() {
    this.getHomeworkList()
  },
  methods: {
    async getHomeworkList(params = {}) {
      this.getListLoading = true;
      try {
       const { data, paging } = await MultiClassExam.getExams({
          multiClassId: this.$route.params.id,
          types: params.types || [],
          offset: params.offset || 0,
          limit: params.pageSize || 10,
        })

        this.homeworksList = data
        this.paging = _.assign(paging, {
          pageSize: Number(paging.limit)
        })
      } finally {
        this.getListLoading = false;
      }
    },
    showResultListModal(type, record) {
      const currentTab = this.status.indexOf(type);
      this.currentTask = record;
      this.currentTab = currentTab;
      if (this.currentTab == currentTab) {
        this.getExamResults(currentTab);
      }
      this.modalVisible = true;
    },
    getExamResults(currentTab = 0) {
      const status = this.status[currentTab]

      MultiClassExam.getExamResults({
        status,
        multiClassId: this.$route.params.id,
        taskId: this.currentTask.id,
      }).then(res => {
        res.paging.pageSize = res.paging.limit
        this.$set(this.examResultList, status, res);
      })
    },
    change(pagination, filters, sorter, { currentDataSource }) {
      const params = {}

      if (filters && _.isArray(filters.name)) {
        params.types = filters.name
      }

      if (pagination) {
        params.offset = pagination.pageSize * (pagination.current - 1)
        params.pageSize = pagination.pageSize
      }

      if (params) {
        this.getHomeworkList(params)
      }
    }
  }
};
</script>

<style lang="less">
.es-transition(@property:all,@time:.3s) {
  -webkit-transition: @property @time ease;
     -moz-transition: @property @time ease;
       -o-transition: @property @time ease;
          transition: @property @time ease;
}

.es-transition {
  .es-transition()
}

.border-radius(@radius) {
  border-radius: @radius;
}

@bg-color: #fafafa;

@import "~app/less/admin-v2/variables.less";
@import "~app/less/page/course-manage/testpaper-mark.less";
</style>
