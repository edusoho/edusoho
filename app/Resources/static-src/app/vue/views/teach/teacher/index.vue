<template>
  <aside-layout :breadcrumbs="[{ name: '教师管理' }]">
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
      :pagination="paging"
      :row-class-name="record => 'teacher-manage-row'"
    >
      <div slot="promoteInfo" slot-scope="item">
        <a-checkbox :checked="item.isPromoted" @change="(e) => changePromoted(e.target.checked, item.id)"></a-checkbox>
        <span class="color-gray text-sm">{{ item.promotedSeq }}</span>
        <a v-if="item.isPromoted" class="set-number" href="javascript:;" @click="clickSetNumberModal(item.id)">序号设置</a>
      </div>

      <div slot="loginInfo" slot-scope="item">
        <div>{{ item.loginIp }}</div>
        <div class="color-gray text-sm">{{ $dateFormat(item.loginTime, 'YYYY-MM-DD HH:mm') }}</div>
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
              { required: true, message: '请输入序号' }
            ]}]"
            :min="0"
            :max="10000"
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
    width: '25%'
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
      paging: {
        offset: 0,
        limit: 10,
        total: 0,
      },
      setNumId: 0,
      modalVisible: false,
      form: this.$form.createForm(this, { name: 'set_number' }),
    };
  },

  created() {
    this.onSearch();
  },

  methods: {
    async onSearch(nickname) {
      const { data, paging } = await Teacher.search({
        nickname: nickname,
        offset: this.paging.offset || 0,
        limit: this.paging.limit || 10,
      });
      paging.page = (paging.offset / paging.limit) + 1;

      data.forEach(element => {
        element.isPromoted = element.promoted == 1;
      });

      this.pageData = data;
      this.paging = paging;
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

    handleCancel(e) {
      this.modalVisible = false;
    },

    async changePromoted(checked, id) {
      let { success } = checked ? await Teacher.promotion(id) : await Teacher.cancelPromotion(id);
      if (success) {
        _.forEach(this.pageData, item => {
          if (item.id == id) {
            item.isPromoted = checked;
            return false;
          }
        });
      }
    }
  },
};
</script>

<style lang="less">
.teacher-manage-row {
  .set-number {
    display: none;
    margin-left: 8px;
  }

  &:hover .set-number {
    display: inline-block;
  }
}
</style>
