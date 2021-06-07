<template>
  <aside-layout class="teacher-manage-container" :breadcrumbs="[{ name: '教师管理' }]">
    <div class="clearfix cd-mb24">
      <a-input-search
        placeholder="请输入用户名搜索"
        style="width: 224px"
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
      <a slot="nickname" slot-scope="text, item" @click="edit(item.id)">{{ text }}</a>

      <div slot="promoteInfo" slot-scope="item">
        <a-checkbox :checked="item.isPromoted" @change="(e) => changePromoted(e.target.checked, item.id)"></a-checkbox>
        <span v-if="item.isPromoted" class="color-gray text-sm">{{ item.promotedSeq }}</span>
        <a v-if="item.isPromoted" class="set-number" href="javascript:;" @click="clickSetNumberModal(item.id)">序号设置</a>
      </div>

      <div slot="loginInfo" slot-scope="item">
        <div>{{ $dateFormat(item.loginTime, 'YYYY-MM-DD HH:mm') }}</div>
        <div class="color-gray text-sm">{{ item.loginIp }}</div>
      </div>

      <a slot="action" slot-scope="item" @click="edit(item.id)">查看</a>
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
      <a-form :form="form" :label-col="{ span: 3 }" :wrapper-col="{ span: 21 }">
        <a-form-item label="序号" extra="请输入0-10000的整数">
          <a-input-number
            style="width: 100%;"
            v-decorator="['number', { rules: [
              { required: true, message: '请输入序号' },
              { type: 'integer', message: '请输入整数' },
              { validator: validateRange, message: '请输入0-10000的整数' },
            ]}]"
          />
        </a-form-item>
      </a-form>
    </a-modal>

  </aside-layout>
</template>


<script>
import _ from '@codeages/utils';
import AsideLayout from 'app/vue/views/layouts/aside.vue';
import { Teacher, UserProfiles } from "common/vue/service/index.js";
import userInfoTable from "../../components/userInfoTable";

const columns = [
  {
    title: "用户名",
    dataIndex: "nickname",
    width: '25%',
    scopedSlots: { customRender: "nickname" },
  },
  {
    title: "是否推荐",
    width: '25%',
    scopedSlots: { customRender: "promoteInfo" },
  },
  {
    title: "最近登录",
    width: '25%',
    scopedSlots: { customRender: "loginInfo" },
  },
  {
    title: "操作",
    width: '25%',
    scopedSlots: { customRender: "action" },
  },
];

export default {
  name: "Teachers",

  components: {
    userInfoTable,
    AsideLayout
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
    };
  },

  created() {
    this.fetchTeacher();
  },

  methods: {
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
      this.pagination = paging.total < Number(paging.limit) ? false : pagination;
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
            this.form.resetFields();
          }
        }
      });
    },

    handleCancel() {
      this.modalVisible = false;
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
      if (_.inRange(value, 0, 10000) === false) {
        callback('请输入0-10000的整数')
      }

      callback()
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
