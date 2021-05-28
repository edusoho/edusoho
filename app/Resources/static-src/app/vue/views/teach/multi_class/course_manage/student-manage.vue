<template>
  <div class="student-manage">
    <div class="clearfix" style="margin-bottom: 24px;">
      <a-space class="pull-left" size="large">
        <a-input-search placeholder="请输入课时或老师关键字搜索" style="width: 260px" @search="onSearch" />
        <a-button type="primary" icon="upload">
          批量导出
        </a-button>
      </a-space>

      <a-space class="pull-right" size="middle">
        <a-button type="primary" @click="addStudent()">
          添加学员
        </a-button>
        <a-button type="primary" icon="download">
          批量导入
        </a-button>
        <a-button type="primary" icon="upload">
          批量移除
        </a-button>
        <a-button type="primary">
          批量修改有效期
        </a-button>
      </a-space>
    </div>

    <a-table
      :row-selection="{ selectedRowKeys: selectedRowKeys, onChange: onSelectChange }"
      :columns="columns"
      :row-key="record => record.id"
      :data-source="students"
    >
      <a slot="name" slot-scope="name, record" @click="viewStudentInfo(record.user.id)">{{ record.user.nickname }}</a>

      <template slot="phone" slot-scope="phone, record">{{ record.user.verifiedMobile }}</template>

      <template slot="wechat" slot-scope="wechat, record">{{ record.user.weixin }}</template>

      <a slot="learningProgressPercent" slot-scope="value">{{ value }}%</a>

      <template slot="assistants" slot-scope="assistants">{{ assistants[0].truename }}</template>

      <a slot="threadCount" slot-scope="threadCount">{{ threadCount }}</a>

      <a slot="finishedHomeworkCount" slot-scope="value, record">{{ value }}/{{ record.homeworkCount }}</a>

      <a slot="finishedTestpaperCount" slot-scope="value, record">{{ value }}/{{ record.testpaperCount }}</a>

      <template slot="deadline" slot-scope="deadline">{{ deadline }}</template>

      <template slot="createdTime" slot-scope="createdTime">{{ createdTime }}</template>

      <template slot="actions" slot-scope="actions, record">
        <a-space size="middle">
          <a class="ant-dropdown-link">查看</a>
          <a-popconfirm
            title="确定移除?"
            ok-text="确定"
            cancel-text="取消"
            @confirm="confirm"
          >
            <a href="#">移除</a>
          </a-popconfirm>
        </a-space>
      </template>
    </a-table>



    <add-student-modal :visible="addStudentVisible" @handle-cancel="addStudentVisible = false;" />
    <student-info-modal :visible="viewStudentInfoVisible" @handle-cancel="viewStudentInfoVisible = false;" />
  </div>
</template>

<script>
import AddStudentModal from './AddStudentModal.vue';
import StudentInfoModal from './StudentInfoModal.vue';
import { MultiClassStudent } from 'common/vue/service';

const columns = [
  {
    title: '学员',
    dataIndex: 'name',
    scopedSlots: { customRender: 'name' }
  },
  {
    title: '手机号',
    dataIndex: 'phone',
    scopedSlots: { customRender: 'phone' }
  },
  {
    title: '微信号',
    dataIndex: 'wechat',
    scopedSlots: { customRender: 'wechat' }
  },
  {
    title: '学习进度',
    dataIndex: 'learningProgressPercent',
    scopedSlots: { customRender: 'learningProgressPercent' }
  },
  {
    title: '助教老师',
    dataIndex: 'assistants',
    scopedSlots: { customRender: 'assistants' }
  },
  {
    title: '提问',
    dataIndex: 'threadCount',
    scopedSlots: { customRender: 'threadCount' }
  },
  {
    title: '作业提交',
    dataIndex: 'finishedHomeworkCount',
    scopedSlots: { customRender: 'finishedHomeworkCount' }
  },
  {
    title: '试卷提交',
    dataIndex: 'finishedTestpaperCount',
    scopedSlots: { customRender: 'finishedTestpaperCount' }
  },
  {
    title: '有效期',
    dataIndex: 'deadline',
    scopedSlots: { customRender: 'deadline' }
  },
  {
    title: '报名时间',
    dataIndex: 'createdTime',
    scopedSlots: { customRender: 'createdTime' }
  },
  {
    title: '操作',
    dataIndex: 'actions',
    scopedSlots: { customRender: 'actions' }
  }
];

const data = [
  {
      "id": "3",  //courseMemberId
      "createdTime": "1621251872",
      "learningProgressPercent": 33,
      "threadCount": "1",
      "finishedHomeworkCount": 2,
      "homeworkCount": 3,
      "finishedTestpaperCount": 0,
      "testpaperCount": 2,
      "user": {
          "id": "1",
          "nickname": "用户名",
          "weixin": "dddsss",
          "verifiedMobile": "16755221122",
      },
      "learningProgressPercent": "25",  // 0~ 100, 表示相应的百分比
      "assistants": [{
          "id": "2", //用户id
          "truename": "李老师",
      }],
      "deadline": "1622115872", //unix_time, 没有此属性表示永不过期
      "createdTime": "1620912792" // 加入时间
  }
]


export default {
  components: {
    AddStudentModal,
    StudentInfoModal
  },

  data() {
    return {
      students: [],
      columns,
      selectedRowKeys: [],
      loading: false,
      addStudentVisible: false,
      viewStudentInfoVisible: false,
      id: this.$route.params.id,
      getListLoading: false,
      keywords: '',
      paging: {
        offset: 0,
        limit: 10,
      },
    };
  },

  computed: {
    hasSelected() {
      return this.selectedRowKeys.length > 0;
    }
  },

  created() {
    this.getMultiClassStudents()
  },

  befeoreRouteUpdate(to, from, next) {
    this.id = to.params.id;
    next();
  },

  methods: {
    async getMultiClassStudents(params = {}) {
      this.students = await MultiClassStudent.search({ 
        id: this.id, 
        keyword: params.keyword || '',
        offset: params.offset || this.paging.offset || 0,
        limit: params.limit || this.paging.limit || 10,
      })
    },

    onSearch(keyword) {
      this.getMultiClassStudents({ keyword })
    },

    start() {
      this.loading = true;
      // ajax request after empty completing
      setTimeout(() => {
        this.loading = false;
        this.selectedRowKeys = [];
      }, 1000);
    },

    onSelectChange(selectedRowKeys) {
      console.log('selectedRowKeys changed: ', selectedRowKeys);
      this.selectedRowKeys = selectedRowKeys;
    },

    addStudent() {
      this.addStudentVisible = true;
    },

    viewStudentInfo(id) {
      this.viewStudentInfoVisible = true;
    },

    confirm() {
      this.$message.success('Click on Yes');
    },
  }
}
</script>
