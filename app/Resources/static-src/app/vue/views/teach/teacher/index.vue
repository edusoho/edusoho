<template>
  <aside-layout class="teacher-manage-container" :breadcrumbs="[{ name: '教师管理' }]">
    <div class="clearfix cd-mb24">
      <a-input-search
        placeholder="请输入用户名搜索"
        style="width: 224px"
        :allowClear="true"
        @search="onSearch"
      />
    </div>

    <a-table
      :columns="columns"
      :data-source="pageData"
      :row-key="record => record.id"
      :pagination="pagination"
      :row-class-name="record => 'teacher-manage-row'"
      :loading="loading"
      @change="handleTableChange"
    >
      <template slot="nickname" slot-scope="text, record">
          <a-avatar :size="48" :src="record.avatar.middle" icon="user"></a-avatar>
          <a class="ml8" @click="edit(record.id)">{{ text }}</a>
      </template>


      <div slot="promoteInfo" slot-scope="item">
        <a-checkbox :checked="item.isPromoted" @change="(e) => changePromoted(e.target.checked, item.id)"></a-checkbox>
        <span v-if="item.isPromoted" class="color-gray text-sm">{{ item.promotedSeq }}</span>
        <a v-if="item.isPromoted" class="set-number" href="javascript:;" @click="clickSetNumberModal(item.id)">序号设置</a>
      </div>

      <template slot="qualification" slot-scope="qualification">
        {{ qualification.code }}
      </template>

      <div slot="loginInfo" slot-scope="item">
        <div>{{ $dateFormat(item.loginTime, 'YYYY-MM-DD HH:mm') }}</div>
        <div class="color-gray text-sm">{{ item.loginIp }}</div>
      </div>

      <template slot="action" slot-scope="item">
        <a-button type="link" @click="edit(item.id)">
          查看
        </a-button>

        <!-- v-if="showEditorSualification" 判断是否可以编辑教师资质，后续新增，把判断给编辑教师资质按钮即可 -->
        <a-dropdown>
          <a class="ant-dropdown-link" style="margin-left: -6px;" @click.prevent>
            <a-icon type="caret-down" />
          </a>
          <a-menu slot="overlay">
            <a-menu-item>
              <a
                data-toggle="modal"
                data-target="#modal"
                data-backdrop="static"
                data-keyboard="false"
                :data-url="`/admin/v2/user/${item.id}/edit`"
              >
                编辑用户信息
              </a>
            </a-menu-item>
            <a-menu-item>
              <a
                data-toggle="modal"
                data-target="#modal"
                data-backdrop="static"
                data-keyboard="false"
                :data-url="`/admin/v2/user/${item.id}/avatar`"
              >
                修改用户头像
              </a>
            </a-menu-item>
            <a-menu-item @click="handleEditorQualification(item)" v-if="showEditorSualification">
              编辑教师资质
            </a-menu-item>
          </a-menu>
        </a-dropdown>
      </template>
    </a-table>

    <a-modal title="教师详细信息" :visible="visible" @cancel="close">
      <userInfoTable :user="user" />

      <template slot="footer">
        <a-button key="back" @click="close"> 关闭 </a-button>
      </template>
    </a-modal>

    <a-modal
      title="设置推荐教师"
      okText="确认"
      cancelText="取消"
      :visible="modalVisible"
      @ok="handleOk"
      @cancel="handleCancel"
    >
    <!-- Tip: Form表单使用组件FormModel更合适，请大家使用FormModel来做表单开发 -->
      <a-form :form="form" :label-col="{ span: 3 }" :wrapper-col="{ span: 21 }">
        <a-form-item label="序号" extra="请输入0-10000的整数">
          <a-input-number
            style="width: 100%;"
            v-decorator="['number', { rules: [
              { required: true, message: '请输入序号' },
              { validator: validateRange, message: '请输入0-10000的整数' },
            ]}]"
          />
        </a-form-item>
      </a-form>
    </a-modal>

    <a-modal
      title="编辑教师资质"
      width="900px"
      :footer="null"
      :visible="qualificationVisible"
      @cancel="handleCancelEditQualification"
    >
      <editor-qualification
        :user-id="currentTeacherUserId"
        :edit-info="currentTeacherQualification"
        @handle-cancel-modal="handleCancelEditQualification"
      />
    </a-modal>
  </aside-layout>
</template>


<script>
import _ from 'lodash';
import AsideLayout from 'app/vue/views/layouts/aside.vue';
import { Teacher, UserProfiles, Setting } from "common/vue/service";
import userInfoTable from "../../components/userInfoTable";
import EditorQualification from 'app/vue/views/components/Teacher/EditorQualification.vue';

const columns = [
  {
    title: "用户名",
    dataIndex: "nickname",
    ellipsis: true,
    scopedSlots: { customRender: "nickname" },
  },
  {
    title: "现带班课总数",
    dataIndex: 'liveMultiClassNum',
    ellipsis: true,
  },
  {
    title: "现学员总数",
    dataIndex: 'liveMultiClassStudentNum',
    ellipsis: true,
  },
  {
    title: "已结课班课总数",
    dataIndex: 'endMultiClassNum',
    ellipsis: true,
  },
  {
    title: "已结课班课学员总数",
    dataIndex: 'endMultiClassStudentNum',
    ellipsis: true,
  },
  {
    title: "是否推荐",
    scopedSlots: { customRender: "promoteInfo" },
  },
  {
    title: "最近登录",
    scopedSlots: { customRender: "loginInfo" },
  },
  {
    title: "操作",
    scopedSlots: { customRender: "action" },
  },
];

const teahcerQualificationColumns =  {
  title: "教师资格证编号",
  dataIndex: "qualification",
  width: '20%',
  scopedSlots: { customRender: "qualification" }
};

export default {
  name: "Teachers",

  components: {
    userInfoTable,
    AsideLayout,
    EditorQualification,
  },

  data() {
    return {
      visible: false,
      user: {},
      columns,
      pageData: [],
      loading: false,
      pagination: {},
      keyWord: '',
      setNumId: 0,
      modalVisible: false,
      form: this.$form.createForm(this, { name: 'set_number' }),
      qualificationVisible: false, // 编辑教师资质
      currentTeacherUserId: 0, // 用于教师上传教师资质的 userId
      currentTeacherQualification: {},
      showEditorSualification: false // 后台是否开启了教师资质功能
    };
  },

  created() {
    this.getSetting();
  },

  methods: {
    async getSetting(){
      const status = await Setting.get('qualification');
      this.showEditorSualification = Boolean(status.qualification);
      if (this.showEditorSualification) {
        _.forEach(this.columns, item => {
          item.width = '20%';
        });
        this.columns.splice(1, 0, teahcerQualificationColumns);
      }
      this.fetchTeacher();
    },

    handleTableChange(pagination) {
      const pager = { ...this.pagination };
      pager.current = pagination.current;
      this.pagination = pager;

      const params = {
        limit: pagination.pageSize,
        offset: (pagination.current - 1) * pagination.pageSize
      };

      this.fetchTeacher(params);
    },

    async fetchTeacher(params) {
      this.loading = true;
      const { data, paging } = await Teacher.search({
        limit: 20,
        nickname: this.keyWord,
        ...params
      });
      const pagination = { ...this.pagination };
      pagination.total = paging.total;
      pagination.pageSize = Number(paging.limit);

      _.forEach(data, item => {
        item.isPromoted = item.promoted == 1;
      });

      this.loading = false;
      this.pageData = data;
      this.pagination = pagination;
    },

    async onSearch(nickname) {
      this.keyWord = nickname;
      this.pagination.current = 1;
      this.fetchTeacher();
    },

    async edit(id) {
      this.user = await UserProfiles.get(id);
      this.visible = true;
    },

    close() {
      this.visible = false;
    },

    clickSetNumberModal(id) {
      this.setNumId = id;
      this.modalVisible = true;
    },

    handleOk(e) {
      this.form.validateFields(async (err, values) => {
        if (!err) {
          const { success } = await Teacher.promotion(this.setNumId, values);
          if (success) {
            _.forEach(this.pageData, item => {
              if (item.id == this.setNumId) {
                item.promotedSeq = values.number;
                return false;
              }
            });
            this.handleCancel();
          }
        }
      });
    },

    handleCancel() {
      this.modalVisible = false;
      this.form.resetFields();
    },

    async changePromoted(checked, id) {
      let result = {};

      if (checked) {
        result = await Teacher.promotion(id);
        this.changePromotedCallBack(result, id, checked)

        return;
      }

      this.$confirm({
        content: '真的要取消该教师推荐吗？',
        okText: '确认',
        cancelText: '取消',
        onOk: async () => {
          result = await Teacher.cancelPromotion(id)
          this.changePromotedCallBack(result, id, checked)
        }
      })
    },

    changePromotedCallBack(result = {}, id, checked) {
      if (!result.success) return;

      _.forEach(this.pageData, item => {
        if (item.id == id) {
          item.isPromoted = checked;
          return false;
        }
      });
    },

    validateRange(rule, value, callback) {
      if (value && (_.inRange(value, 0, 10001) === false || /^\+?[0-9][0-9]*$/.test(value) === false)) {
        callback('请输入0-10000的整数')
      }

      callback()
    },

    handleEditorQualification(item) {
      this.currentTeacherUserId = item.id;
      this.currentTeacherQualification = item.qualification;
      this.qualificationVisible = true;
    },

    handleCancelEditQualification(qualification) {
      _.forEach(this.pageData, item => {
        if (item.id == qualification.user_id) {
          item.qualification = qualification;
        }
      });
      this.qualificationVisible = false;
    }
  },
};
</script>

<style scoped>
.teacher-manage-row .set-number {
    display: none;
    margin-left: 8px;
  }

.teacher-manage-row:hover .set-number {
  display: inline-block;
}

.teacher-manage-container >>> .ant-pagination {
  float: none;
  text-align: center;
}
</style>
