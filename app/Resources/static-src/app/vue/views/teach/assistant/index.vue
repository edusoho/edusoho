<template>
  <aside-layout :breadcrumbs="[{ name: '助教管理' }]">
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
      rowKey="id"
      :pagination="pagination"
      :loading="loading"
      @change="handleTableChange"
    >
      <template slot="nickname" slot-scope="text, item">
        <div class="avatar-name">
          <a-avatar :size="48" :src="item.avatar.middle" icon="user"></a-avatar>
          <a class="ml8" @click="check(item.id)">{{ text }}</a>
        </div>
      </template>

      <div slot="loginInfo" slot-scope="item">
        <div>{{ $dateFormat(item.loginTime, 'YYYY-MM-DD HH:mm') }}</div>
        <div class="color-gray text-sm">{{ item.loginIp }}</div>
      </div>

      <template slot="action" slot-scope="item">
        <a-button
        type="link"
        @click="check(item.id)"
        >
          查看
        </a-button>
        <a-dropdown>
          <a class="ant-dropdown-link" style="margin-left: -6px;" @click="e => e.preventDefault()">
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
          </a-menu>
        </a-dropdown>
      </template>

    </a-table>

    <a-modal title="助教详细信息" :visible="visible" @cancel="close">
      <userInfoTable :user="user" />

      <template slot="footer">
        <a-button key="back" @click="close"> 关闭 </a-button>
      </template>
    </a-modal>
  </aside-layout>
</template>


<script>
import AsideLayout from 'app/vue/views/layouts/aside.vue';
import { Assistant, UserProfiles } from "common/vue/service/index.js";
import userInfoTable from "../../components/userInfoTable";

const columns = [
  {
    title: "用户名",
    ellipsis: true,
    dataIndex: "nickname",
    scopedSlots: { customRender: "nickname" },

  },
  {
    title: "是否绑定销客助手",
    dataIndex: 'isScrmBind',
    ellipsis: true,
    scopedSlots: { customRender: "isScrmBind" },
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
    title: "已结班班课总数",
    dataIndex: 'endMultiClassNum',
    ellipsis: true,
  },
  {
    title: "已结班班课学员总数",
    dataIndex: 'endMultiClassStudentNum',
    ellipsis: true,
  },
  {
    title: "最近登录",
    ellipsis: true,
    scopedSlots: { customRender: "loginInfo" },
  },
  {
    title: "操作",
    scopedSlots: { customRender: "action" },
  },
];

export default {
  name: "assistants",
  components: {
    userInfoTable,
    AsideLayout,
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
    };
  },
  created() {
    this.fetchAssistant();
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

      this.fetchAssistant(params);
    },
    async fetchAssistant(params) {
      this.loading = true;
      const { data, paging } = await Assistant.search({
        limit: 10,
        nickname: this.keyWord,
        ...params
      });
      const pagination = { ...this.pagination };
      pagination.total = paging.total;

      this.loading = false;
      this.pageData = data;
      this.pagination = pagination;
    },
    async onSearch(nickname) {
      this.keyWord = nickname;
      this.pagination.current = 1;
      this.fetchAssistant();
    },
    async check(id) {
      this.user = await UserProfiles.get(id);
      this.visible = true;
    },
    close() {
      this.visible = false;
    },
  },
};
</script>
