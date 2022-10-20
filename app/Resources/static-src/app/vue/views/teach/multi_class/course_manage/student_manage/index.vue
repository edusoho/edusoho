<template>
  <div class="student-manage">
    <div class="clearfix" style="margin-bottom: 24px;">
      <a-input-search class="pull-left" placeholder="请输入姓名或手机号搜索" style="width: 200px" @search="onSearch" />
      <a-space class="pull-left cd-ml16" size="middle">
        <a-button
          icon="plus"
          type="primary"
          @click="addStudent()"
        >
          添加学员
        </a-button>
        <a-button
          type="primary"
          data-toggle="modal"
          data-target="#modal"
          data-backdrop="static"
          data-keyboard="false"
          :data-url="`/importer/course-member/index?courseId=${multiClass.course.id}`"
        >
          <a-space>
            <svg-icon icon="icon-import" />
            批量导入
          </a-space>
        </a-button>

        <a-button
          v-if="multiClass.type === 'normal'"
          @click="clickBatchUpdateAssistantModal()"
          type="primary"
        >
          <a-space>
            <svg-icon icon="icon-edit" />
            修改助教
          </a-space>
        </a-button>

        <a-button
          type="primary"
          @click="onBatchRemoveStudent"
        >
          <a-space>
            <svg-icon icon="icon-remove" />
            批量移除
          </a-space>
        </a-button>

        <a-button
          v-if="selectedRowKeys.length === 0"
          type="primary"
          @click="onSelectEmpty"
        >
          <a-space>
            <svg-icon icon="icon-edit" />
            批量修改有效期
          </a-space>
        </a-button>

        <a-button
          v-if="selectedRowKeys.length > 0"
          type="primary"
          data-toggle="modal"
          data-target="#modal"
          data-backdrop="static"
          data-keyboard="false"
          :data-url="`/course_set/${multiClass.course.courseSetId}/manage/course/${multiClass.course.id}/student/deadline?${selectedRowKeysStr}`"
        >
          <a-space>
            <svg-icon icon="icon-edit" />
            批量修改有效期
          </a-space>
        </a-button>

        <a-button
          type="primary"
          @click="clickBatchStudentGroupModal"
          v-if="multiClass.type === 'group'"
        >
          <a-space>
            <svg-icon icon="icon-change" />
            变更分组
          </a-space>
        </a-button>
      </a-space>

      <a-space class="right-export" size="middle">
        <a-button
          type="primary"
          class="js-export-btn"
          href="javascript:;"
          data-try-url="/try/export/course-students"
          data-url="/export/course-students"
          data-pre-url="/pre/export/course-students"
          data-loading-text="正在导出..."
          data-target-form="#course-students-export"
          data-file-names='["course-students"]'
        >

          <a-space>
            <svg-icon icon="icon-export" />
            批量导出
          </a-space>
        </a-button>
      </a-space>
    </div>
    <a-modal title="学员详细信息" :visible="viewStudentInfoVisible" @cancel="close">
      <userInfoTable :user="modalShowUser" />
      <template slot="footer">
        <a-button key="back" @click="close"> 关闭 </a-button>
      </template>
    </a-modal>
  <div>
    <a-row>
      <a-col :span="3" v-if="false">
       <div class="student-group">学员分布</div>
        <a-menu mode="inline" @select="onGroupClick">
          <a-menu-item key="">
            <span>全部学员</span>
          </a-menu-item>
          <a-menu-item class="menu-group" v-for="Group in groupList" :key="Group.id">
             <a-button
              class="edit-group-assistant"
              type="link"
              @click="clickAssistantGroupModal"
              style="width:0px"
            >
              <a-space>
              <svg-icon icon="icon-edit" style="color:#46C37B"/>
              </a-space>
            </a-button>
            <span>{{Group.name}}</span>
            <span>({{Group.student_num}})</span>
            <span style="margin-left: 4px;">{{Group.assistant.nickname}}</span>
          </a-menu-item>
        </a-menu>
      </a-col>
      <a-col :span="24">
        <a-table
          :row-selection="{ selectedRowKeys: selectedRowKeys, onChange: onSelectChange }"
          :columns="columns"
          :row-key="record => record.id"
          :pagination="paging"
          :data-source="students"
          @change="handleStudentTableChange"
        >
          <a slot="name" slot-scope="name, record" @click="viewStudentInfo(record.user)">{{ record.user.nickname }}<span v-if="record.user.truename">({{ record.user.truename }})</span></a>

          <template slot="phone" slot-scope="phone, record">{{ record.user.verifiedMobile || '--' }}</template>

          <a slot="learningProgressPercent" data-toggle="modal" data-target="#modal" :data-url="`/course_set/${multiClass.course.courseSetId}/manage/course/${multiClass.course.id}/students/${record.user.id}/process`" slot-scope="value, record">{{ value }}%</a>

          <assistant slot="assistant" slot-scope="assistant" :assistant="assistant" />

          <template slot="threadCount" slot-scope="threadCount">{{ threadCount }}</template>

          <a slot="finishedHomeworkCount" @click="onClickHomeworkModal(record.user)" slot-scope="value, record">{{ value }}/{{ record.homeworkCount }}</a>

          <a slot="finishedTestpaperCount" @click="onClickTestpaperModal(record.user)" slot-scope="value, record">{{ value }}/{{ record.testpaperCount }}</a>

          <template slot="deadline" slot-scope="deadline">{{ $dateFormat(deadline, 'YYYY-MM-DD HH:mm') || '--' }}</template>

          <template slot="createdTime" slot-scope="createdTime">{{ $dateFormat(createdTime, 'YYYY-MM-DD HH:mm')|| '--' }}</template>

          <template slot="actions" slot-scope="actions, record">
            <a-space size="middle">
              <a class="ant-dropdown-link" @click="viewStudentInfo(record.user)">查看</a>
              <a-popconfirm
                title="确定移除?"
                ok-text="确定"
                cancel-text="取消"
                @confirm="confirm(record.user.id)"
              >
                <span style="color: #fe4040; cursor: pointer;">移除</span>
              </a-popconfirm>
            </a-space>
          </template>
        </a-table>
      </a-col>
   </a-row>
  </div>
    <assistant-list-modal :visible="assistantListModalVisible" :multi-class-id="id" :multi-class="multiClass" :selected-student-ids="selectedStudentIds" @handle-cancel="assistantListModalVisible = false;" />
    <add-student-modal :visible="addStudentVisible" :multi-class="multiClass" @handle-cancel="addStudentVisible = false;" />
    <change-group-modal :visible="changeGroupVisible" :groupList="groupList" :multi-class-id="id" :multi-class="multiClass" :selected-student-ids="selectedStudentIds" @handle-cancel="updateStudentList"></change-group-modal>
    <edit-assistant-modal :visible="editAssistantVisible" :multi-class-id="id" :multi-class="multiClass" :groupId="groupId" @handle-cancel="updateStudentList"></edit-assistant-modal>

    <form id="course-students-export" class="hide">
      <input type="hidden" name="courseSetId" :value="multiClass.course.courseSetId">
      <input type="hidden" name="courseId" :value="multiClass.course.id">
    </form>

    <a-modal
      :visible="homeworkModalVisible"
      :footer="null"
      :title="`${selectedUser.nickname} - 作业`"
      :width="920"
      @cancel="onHomeworkModalCancel"
    >
      <a-tabs v-model="currentHomeworkTab">
        <a-tab-pane :key="0" tab="全部"></a-tab-pane>
        <a-tab-pane :key="1" tab="未批阅"></a-tab-pane>
        <a-tab-pane :key="2" tab="进行中"></a-tab-pane>
        <a-tab-pane :key="3" tab="已批阅"></a-tab-pane>
      </a-tabs>
      <a-table
        v-if="homeworkResults"
        :columns="resultColumns"
        :data-source="homeworkResults.data"
        :pagination="homeworkPaging[status[currentHomeworkTab]]"
        @change="handleHomeworkTableChange"
      >
        <template slot="lesson" slot-scope="activity, record">{{ activity.title || '--' }}</template>
        <template slot="exam" slot-scope="answerScene, record">{{ answerScene.name || '--' }}</template>
        <template slot="teacherInfo" slot-scope="teacherInfo, record">{{ record.teacherInfo.nickname || '--' }}</template>
        <template slot="status" slot-scope="status">
          {{ statusMap[status] }}
        </template>
        <template slot="end_time" slot-scope="end_time">
          {{ $dateFormat(end_time, 'YYYY-MM-DD HH:mm') }}
        </template>
        <template slot="action" slot-scope="text, record">
          <a v-if="record.status === 'reviewing'"
             :href="record.activity.mediaType === 'testpaper' ? `/course/${multiClass.course.id}/manage/testpaper/${record.id}/check?action=check` : `/course/${multiClass.course.id}/manage/homework/${record.id}/check?action=check`"
             target="_blank">去批阅</a>
          <a v-else-if="record.status === 'finished'"
             :href="`/testpaper/result/${record.id}/show?action=check`"
             target="_blank">查看结果</a>
          <span v-else-if="['doing', 'paused'].includes(record.status)"
          >--</span>
        </template>
      </a-table>
    </a-modal>
    <a-modal
      :visible="testpaperModalVisible"
      :footer="null"
      :title="`${selectedUser.nickname} - 试卷`"
      :width="920"
      @cancel="onTestpaperModalCancel"
    >
      <a-tabs v-model="currentTestpaperTab">
        <a-tab-pane :key="0" tab="全部"></a-tab-pane>
        <a-tab-pane :key="1" tab="未批阅"></a-tab-pane>
        <a-tab-pane :key="2" tab="进行中"></a-tab-pane>
        <a-tab-pane :key="3" tab="已批阅"></a-tab-pane>
      </a-tabs>
      <!-- TODO 翻页未做 -->
      <a-table
        v-if="testpaperResults"
        :columns="resultColumns"
        :data-source="testpaperResults.data"
        :pagination="testpaperPaging[status[currentTestpaperTab]]"
        @change="handleTestpaperTableChange"
      >
        <template slot="lesson" slot-scope="activity, record">{{ activity.title || '--' }}</template>
        <template slot="exam" slot-scope="answerScene, record">{{ answerScene.name || '--'  }}</template>
        <template slot="teacherInfo" slot-scope="teacherInfo, record">{{ record.teacherInfo.nickname || '--' }}</template>
        <template slot="status" slot-scope="status">
          {{ statusMap[status] }}
        </template>
        <template slot="end_time" slot-scope="end_time">
          {{ $dateFormat(end_time, 'YYYY-MM-DD HH:mm') }}
        </template>
        <template slot="action" slot-scope="text, record">
          <a v-if="record.status === 'reviewing'"
             :href="`/course/${multiClass.course.id}/manage/testpaper/${record.id}/check?action=check`"
             target="_blank">去批阅</a>
          <a v-else-if="record.status === 'finished'"
             :href="`/testpaper/result/${record.id}/show?action=check`"
             target="_blank">查看结果</a>
          <span v-else-if="['doing', 'paused'].includes(record.status)"
             >--</span>
        </template>
      </a-table>
    </a-modal>
  </div>

</template>

<script>
import AddStudentModal from './AddStudentModal.vue';
import StudentInfoModal from './StudentInfoModal.vue';
import AssistantListModal from 'app/vue/views/teach/assistant/components/AssistantListModal';
import userInfoTable from "app/vue/views/components/userInfoTable";
import { MultiClassStudent, MultiClass, UserProfiles, MultiClassStudentExam } from 'common/vue/service';
import ChangeGroupModal from './ChangeGroupModal.vue';
import EditAssistantModal from './EditAssistantModal.vue';
import Assistant from '../components/Assistant';

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
    title: '学习进度',
    dataIndex: 'learningProgressPercent',
    scopedSlots: { customRender: 'learningProgressPercent' }
  },
  {
    title: '助教老师',
    dataIndex: 'assistant',
    width: '10%',
    ellipsis: true,
    scopedSlots: { customRender: 'assistant' }
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
    title: '分组',
    dataIndex: 'group.name',
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

const resultColumns = [
  {
    title: '课时',
    dataIndex: 'activity',
    scopedSlots: { customRender: "lesson" },
    width: '15%',
    ellipsis: true,
  },
  {
    title: '作业/考试',
    dataIndex: 'answerScene',
    scopedSlots: { customRender: "exam" },
    width: '15%',
    ellipsis: true,
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
];

const defaultExamPaging = {
  all: {
    total: 0,
    offset: 0,
    pageSize: 5,
  },
  reviewing: {
    total: 0,
    offset: 0,
    pageSize: 5,
  },
  doing: {
    total: 0,
    offset: 0,
    pageSize: 5,
  },
  finished: {
    total: 0,
    offset: 0,
    pageSize: 5,
  }
};
export default {
  components: {
    AddStudentModal,
    StudentInfoModal,
    AssistantListModal,
    userInfoTable,
    ChangeGroupModal,
    EditAssistantModal,
    Assistant,
  },
  data() {
    return {
      groupList:[],
      resultColumns,
      students: [],
      modalShowUser: {},
      selectedUser: {},
      multiClass: {
        course: {
          id: 0
        }
      },
      columns,
      selectedRowKeys: [],
      selectedRowKeysStr: '',
      selectedUserIds: [],
      loading: false,
      addStudentVisible: false,
      viewStudentInfoVisible: false,
      changeGroupVisible: false,
      assistantListModalVisible: false,
      editAssistantVisible: false,
      selectedStudentIds: [],
      id: this.$route.params.id,
      getListLoading: false,
      keyword: '',
      groupId: '',
      paging: {
        total: 0,
        offset: 0,
        pageSize: 10,
      },
      testpaperPaging: defaultExamPaging,
      homeworkPaging: defaultExamPaging,
      status: ['all', 'reviewing', 'doing',  'finished'],
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
      },
      homeworkResultList: {
        type: Object,
        required: true,
        default: {}
      },
      testpaperResultList: {
        type: Object,
        required: true,
        default: {}
      },
      currentHomeworkTab: 0,
      currentTestpaperTab: 0,
      homeworkModalVisible: false,
      testpaperModalVisible: false,
    };
  },

  computed: {
    hasSelected() {
      return this.selectedRowKeys.length > 0;
    },
    testpaperResults() {
      const key = this.status[this.currentTestpaperTab];

      return this.testpaperResultList[key]
    },
    homeworkResults() {
      const key = this.status[this.currentHomeworkTab];
      return this.homeworkResultList[key];
    }
  },

  watch: {
    currentTestpaperTab(currentTestpaperTab) {
      this.getTestpaperResults(currentTestpaperTab);
    },
    currentHomeworkTab(currentHomeworkTab) {
      this.getHomeworkResults(currentHomeworkTab);
    }
  },

  async created() {
    this.getMultiClassStudents();

    await this.getMultiClass();
    await this.getMultiClassStudentsGroup();
  },

  befeoreRouteUpdate(to, from, next) {
    this.id = to.params.id;
    next();
  },

  methods: {
    onHomeworkModalCancel() {
      this.homeworkModalVisible = false;
      this.currentHomeworkTab = 0;
      this.homeworkPaging = defaultExamPaging;
      this.homeworkResultList = {};
    },

    onTestpaperModalCancel() {
      this.testpaperModalVisible = false;
      this.currentTestpaperTab = 0;
      this.testpaperPaging = defaultExamPaging;
      this.testpaperResultList = {};
    },
    async getMultiClassStudentsGroup(){
     this.groupList = await MultiClassStudent.getGroup(this.multiClass.id);
    },
    updateStudentList(){
      this.editAssistantVisible = false
      this.changeGroupVisible = false;
      this.getMultiClassStudents();
      this.getMultiClassStudentsGroup();
    },
    async getMultiClassStudents(params = {}) {
      const { data, paging } = await MultiClassStudent.search({
        id: this.id,
        keyword: params.keyword || this.keyword || '',
        groupId: params.groupId || this.groupId || '',
        offset: params.offset || this.paging.offset || 0,
        limit: params.limit || this.paging.pageSize || 10,
      });
      this.students = data;
      paging.page = (paging.offset / paging.limit) + 1;
      this.paging = Object.assign(this.paging, paging);
    },

    handleTestpaperTableChange(pagination) {
      const status = this.status[this.currentTestpaperTab];
      const pager = { ...this.testpaperPaging[status] };
      pager.current = pagination.current;
      pager.offset = (pagination.current - 1) * pagination.pageSize;
      this.testpaperPaging[status] = pager;

      const params = {
        limit: pagination.pageSize,
        offset: (pagination.current - 1) * pagination.pageSize
      };

      this.getTestpaperResults(this.currentTestpaperTab, params);
    },

    handleHomeworkTableChange(pagination) {
      const status = this.status[this.currentHomeworkTab];
      const pager = { ...this.homeworkPaging[status] };
      pager.current = pagination.current;
      pager.offset = (pagination.current - 1) * pagination.pageSize;
      this.homeworkPaging[status] = pager;


      const params = {
        limit: pagination.pageSize,
        offset: (pagination.current - 1) * pagination.pageSize
      };
      console.log(this.homeworkPaging);
      console.log(params)

      this.getHomeworkResults(this.currentHomeworkTab, params);
    },

    handleStudentTableChange(pagination) {
      const pager = { ...this.paging };
      pager.current = pagination.current;
      pager.offset = (pagination.current - 1) * pagination.pageSize
      this.paging = pager;

      const params = {
        limit: pagination.pageSize,
        offset: (pagination.current - 1) * pagination.pageSize
      };

      this.getMultiClassStudents(params);
    },

    async getMultiClass() {
      this.multiClass = await MultiClass.get(this.id);
    },

    onRemoveStudent(userId) {
      MultiClassStudent.deleteMultiClassMember(this.multiClass.id, userId).then(res => {
        this.getMultiClassStudents();
        this.getMultiClassStudentsGroup();
        this.$message.success('移除学员成功！');
      }).catch(err => {
        this.$message.warning('移除学员失败！');
      })
    },
    onSearch(keyword) {
      this.keyword = keyword;
      this.getMultiClassStudents({ keyword })
    },
    onClickHomeworkModal(user) {
      this.selectedUser = user;
      this.getHomeworkResults();
      this.homeworkModalVisible = true;
    },
    onClickTestpaperModal(user) {
      this.selectedUser = user;
      this.getTestpaperResults();
      this.testpaperModalVisible = true;
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
      this.selectedRowKeys = selectedRowKeys;
      this.getSelectedRowKeysQueryStr();
    },
    getHomeworkResults(currentHomeworkTab = 0, params = {}) {
      const status = this.status[currentHomeworkTab]

      MultiClassStudentExam.searchStudentExamResults(this.$route.params.id, this.selectedUser.id, {
        status,
        type: 'homework',
        offset: params.offset || this.homeworkPaging[status].offset || defaultExamPaging[status].offset,
        limit: params.limit || this.homeworkPaging[status].pageSize || defaultExamPaging[status].pageSize,
      }).then(res => {
        res.paging.page = (res.paging.offset / res.paging.limit) + 1;
        this.homeworkPaging[status] = Object.assign(this.homeworkPaging[status], res.paging);
        console.log(this.homeworkPaging[status]);
        this.$set(this.homeworkResultList, status, res);
      });

    },
    getTestpaperResults(currentTestpaperTab = 0, params = {}) {
      const status = this.status[currentTestpaperTab]

      MultiClassStudentExam.searchStudentExamResults(this.$route.params.id, this.selectedUser.id, {
        status,
        type: 'testpaper',
        offset: params.offset || this.testpaperPaging[status].offset || defaultExamPaging[status].offset,
        limit: params.limit || this.testpaperPaging[status].pageSize || defaultExamPaging[status].pageSize,
      }).then(res => {
        res.paging.page = (res.paging.offset / res.paging.limit) + 1;
        this.testpaperPaging[status] = Object.assign(this.testpaperPaging[status], res.paging);
        this.$set(this.testpaperResultList, status, res);
      })
    },
    addStudent() {
      this.addStudentVisible = true;
    },
    close() {
      this.viewStudentInfoVisible = false;
    },

    async viewStudentInfo(user) {
      this.modalShowUser = await UserProfiles.get(user.id);;
      this.viewStudentInfoVisible = true;
    },

    clickBatchUpdateAssistantModal() {
      if (this.selectedRowKeys.length === 0) {
        this.$message.error('请至少选中一项后修改', 1);
        return;
      }

      this.assistantListModalVisible = true;
      this.selectedStudentIds = this.selectedUserIds
    },
    clickBatchStudentGroupModal() {
      if (this.selectedRowKeys.length === 0) {
        this.$message.error('请至少选中一项后修改', 1);
        return;
      }

      this.changeGroupVisible = true;
      this.selectedStudentIds = this.selectedUserIds
    },
    clickAssistantGroupModal() {
      this.editAssistantVisible = true;
    },
    onBatchRemoveStudent() {
      if (this.selectedRowKeys.length === 0) {
        this.$message.error('请至少选中一项后移除', 1);
        return;
      }
      let self = this;
      this.$confirm({
        title: '是否移除这些学员？',
        // content: '删除后，学员将不能学习课程内的所有内容。',
        okText: '确定',
        okType: 'danger',
        cancelText: '取消',
        onOk() {
          MultiClassStudent.batchDeleteClassMember(self.multiClass.id, {
            userIds: self.selectedUserIds,
          }).then(res => {
            self.getMultiClassStudents();
            self.getMultiClassStudentsGroup();
            self.$message.success('移除学员成功！');
            self.selectedRowKeys = [];
          }).catch(err => {
            self.$message.warning('移除学员失败！');
          })
        },
        onCancel() {
          console.log('Cancel');
        },
      });

    },
    onGroupClick(res) {
      const groupId = res.key;
      this.groupId = groupId;
      this.getMultiClassStudents({ groupId });
    },
    onSelectEmpty() {
      this.$message.error('请至少选中一项后进行修改！', 1);
    },
    confirm(userId) {
      this.onRemoveStudent(userId);
    },
    getSelectedRowKeysQueryStr() {
      let str = '';
      let userIds = [];
      if (this.selectedRowKeys) {
        this.selectedRowKeys.forEach((item, index) => {
          this.students.forEach((item1, index1) => {
            if (item1.id == item) {
              str =  `${str}&ids[]=${item1.user.id}`;
              userIds.push(item1.user.id);
            }
          })
        });
      }
      this.selectedRowKeysStr = str;
      this.selectedUserIds = userIds;
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

.edit-group-assistant{
  visibility:hidden
}
.menu-group:hover .edit-group-assistant{
  visibility:visible
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

 .ant-menu-vertical .ant-menu-item::before,
    .ant-menu-vertical-left .ant-menu-item::before,
    .ant-menu-vertical-right .ant-menu-item::before,
    .ant-menu-inline .ant-menu-item::before {
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        border-left: 4px solid @brand-primary;
        transform: scaleY(0.0001);
        opacity: 0;
        transition: transform 0.15s cubic-bezier(0.215, 0.61, 0.355, 1), opacity 0.15s cubic-bezier(0.215, 0.61, 0.355, 1);
        content: '';
    }

    .ant-menu-inline .ant-menu-selected::before,
    .ant-menu-inline .ant-menu-item-selected::before {
        transform: scaleY(1);
        opacity: 1;
        transition: transform 0.15s cubic-bezier(0.645, 0.045, 0.355, 1), opacity 0.15s cubic-bezier(0.645, 0.045, 0.355, 1);
    }

    .ant-menu-vertical .ant-menu-item::after,
    .ant-menu-vertical-left .ant-menu-item::after,
    .ant-menu-vertical-right .ant-menu-item::after,
    .ant-menu-inline .ant-menu-item::after {
        border-right: none;
    }
    .student-group{
      padding: 14px 24px;
      border-right: 1px solid #ebebeb;
      border-bottom: 1px solid #ebebeb;
      font-size: 16px;
      color: #333333;
      letter-spacing: 0;
      line-height: 24px;
      font-weight: 500;
    }
    .ant-menu-vertical .ant-menu-item, .ant-menu-vertical-left .ant-menu-item, .ant-menu-vertical-right .ant-menu-item, .ant-menu-inline .ant-menu-item, .ant-menu-vertical .ant-menu-submenu-title, .ant-menu-vertical-left .ant-menu-submenu-title, .ant-menu-vertical-right .ant-menu-submenu-title, .ant-menu-inline .ant-menu-submenu-title{
      margin-top: unset;
    }
    .ant-menu-vertical .ant-menu-item:not(:last-child), .ant-menu-vertical-left .ant-menu-item:not(:last-child), .ant-menu-vertical-right .ant-menu-item:not(:last-child), .ant-menu-inline .ant-menu-item:not(:last-child){
      margin-bottom: unset;
    }
    .ant-menu-vertical .ant-menu-item:not(:first-child), .ant-menu-vertical-left .ant-menu-item:not(:first-child), .ant-menu-vertical-right .ant-menu-item:not(:first-child), .ant-menu-inline .ant-menu-item:not(:first-child){
      padding-left: unset!important;
    }
    @media only screen and (max-width: 1400px) {
    /* For mobile phones: */
    .right-export {
        margin: 24px 0 016px;
    }

    }
    @media only screen and (min-width: 1400px) {
        /* For mobile phones: */
        .right-export {
            float: right;
        }
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
