<template>
  <div class="student-manage">
    <div class="clearfix" style="margin-bottom: 24px;">
      <a-space class="pull-left" size="large">
        <a-input-search placeholder="请输入姓名或手机号搜索" style="width: 260px" @search="onSearch" />
        <a-button type="primary" icon="upload">
          批量导出
        </a-button>
      </a-space>

      <a-space class="pull-right" size="middle">
        <a-button type="primary" @click="addStudent()">
          添加学员
        </a-button>
        <a-button type="primary"
                  icon="download"
                  data-toggle="modal"
                  data-target="#modal"
                  data-backdrop="static"
                  data-keyboard="false"
                  data-url="/importer/course-member/index?courseId=5"
        >
          批量导入
        </a-button>
        <a-button type="primary" icon="upload" @click="onBatchRemoveStudent">
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
      :pagination="paging"
      :data-source="students"
    >
      <a slot="name" slot-scope="name, record" @click="viewStudentInfo(record.user.id)">{{ record.user.nickname }}</a>

      <template slot="phone" slot-scope="phone, record">{{ record.user.verifiedMobile }}</template>

<!--      <template slot="wechat" slot-scope="wechat, record">{{ record.user.weixin }}</template>-->

      <a slot="learningProgressPercent" data-toggle="modal" data-target="#modal" :data-url="`/course_set/${multiClass.course.courseSetId}/manage/course/${multiClass.course.id}/students/${record.user.id}/process`" slot-scope="value, record">{{ value }}%</a>

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
            @confirm="confirm(record.user.id)"
          >
            <a href="#" >移除</a>
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
import { MultiClassStudent, MultiClass } from 'common/vue/service';

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

export default {
  components: {
    AddStudentModal,
    StudentInfoModal
  },
  data() {
    return {
      students: [],
      multiClass: {},
      columns,
      selectedRowKeys: [],
      loading: false,
      addStudentVisible: false,
      viewStudentInfoVisible: false,
      id: this.$route.params.id,
      getListLoading: false,
      keyword: '',
      paging: {
        total: 0,
        offset: 0,
        pageSize: 10,
      },
    };
  },

  computed: {
    hasSelected() {
      return this.selectedRowKeys.length > 0;
    }
  },

  created() {
    this.getMultiClassStudents();
    this.getMultiClass();
  },

  befeoreRouteUpdate(to, from, next) {
    this.id = to.params.id;
    next();
  },

  methods: {
    async getMultiClassStudents(params = {}) {
      const { data, paging } = await MultiClassStudent.search({
        id: this.id,
        keyword: params.keyword ||this.keyword || '',
        offset: params.offset || this.paging.offset || 0,
        limit: params.limit || this.paging.limit || 10,
      });
      this.students = data;
      paging.page = (paging.offset / paging.limit) + 1;
      this.paging = paging;
    },

    async getMultiClass() {
      await MultiClass.get(this.id).then(res => {
        this.multiClass = res;
      }).catch(err => {

      });
    },
    onRemoveStudent(userId) {
      MultiClassStudent.deleteMultiClassMember(this.multiClass.id, userId).then(res => {
        this.getMultiClassStudents();
      }).catch(err => {
        this.$message.warning('移除学员失败！');
      })
    },
    onSearch(keyword) {
      this.keyword = keyword;
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
    onBatchRemoveStudent() {
      if (this.selectedRowKeys.length === 0) {
        this.$message.error('请至少选中一项后移除', 3);
        return;
      }
      this.$confirm({
        title: '是否移除这些学员？',
        // content: '删除后，学员将不能学习课程内的所有内容。',
        okText: '确定',
        okType: 'danger',
        cancelText: '取消',
        onOk() {

        },
        onCancel() {
          console.log('Cancel');
        },
      });

    },

    confirm(userId) {
      this.onRemoveStudent(userId);
      this.$message.success('移除学员成功！', 2);
    },
  }
}
</script>


<style lang="less">
.border-radius(@border-radius:4px) {
  -webkit-border-radius: @border-radius;
  -moz-border-radius: @border-radius;
  border-radius: @border-radius;
}

.es-box-shadow {
  -webkit-box-shadow: 0 1px 2px 0 rgba(0,0,0,0.1);
  -moz-box-shadow: 0 1px 2px 0 rgba(0,0,0,0.1);
  box-shadow: 0 1px 2px 0 rgba(0,0,0,0.1);
}

.box-shadow(@box-shadow:none) {
  -webkit-box-shadow: @box-shadow;
  -moz-box-shadow: @box-shadow;
  box-shadow: @box-shadow;
}

.es-transition(@property:all,@time:.3s) {
  -webkit-transition: @property @time ease;
  -moz-transition: @property @time ease;
  -o-transition: @property @time ease;
  transition: @property @time ease;
}
.img-responsive(@display: block) {
  display: @display;
  max-width: 100%;
  height: auto;
}
.border-top-left-radius(@border-radius:4px) {
  border-top-left-radius: @border-radius;
}
.border-top-right-radius(@border-radius:4px) {
  border-top-right-radius: @border-radius;
}
.border-bottom-left-radius(@border-radius:4px) {
  border-bottom-left-radius: @border-radius;
}
.border-bottom-right-radius(@border-radius:4px) {
  border-bottom-right-radius: @border-radius;
}
.opacity(@opacity) {
  opacity: @opacity;
  // IE8 filter
  @opacity-ie: (@opacity * 100);
  filter: ~"alpha(opacity=@{opacity-ie})";
}
.text-overflow() {
  display: block;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  word-wrap: normal;
}
@screen-xs-min:              480px;
@screen-sm-min:              768px;
@screen-md-min:              992px;
@screen-lg-min:              1200px;
@screen-xs-max:              (@screen-sm-min - 1);
@screen-sm-max:              (@screen-md-min - 1);
@screen-md-max:              (@screen-lg-min - 1);
@tip-color:             #adadad;
@gray-dark:             #666;
@gray-darker:           #333;
@gray:                  #999;
@gray-lighter:          #f5f5f5;
@bg-color:              #fafafa;
@brand-primary:         #46c37B;
@gray-light:            #e1e1e1;
@brand-danger:          #ed3e3e;
@import "~app/less/page/course-manage/students.less";
@import "~app/less/page/class/class-detail.less";
</style>
