<template>
  <aside-layout :breadcrumbs="[{ name: '助教管理' }]">
    <div class="clearfix cd-mb24">
      <a-input-search
        placeholder="请输入用户名搜索"
        style="width: 224px"
        @search="onSearch"
      />
      <a-button class="pull-right" type="primary" @click="showPermissionModal">助教权限设置</a-button>
    </div>

    <a-table
      :columns="columns"
      :data-source="pageData"
      rowKey="id"
      :pagination="pagination"
      :loading="loading"
      @change="handleTableChange"
    >
      <div slot="loginInfo" slot-scope="item">
        <div>{{ item.loginIp }}</div>
        <div class="color-gray text-sm">{{ $dateFormat(item.loginTime, 'YYYY-MM-DD HH:mm') }}</div>
      </div>

      <a slot="action" slot-scope="item" @click="edit(item.id)">查看</a>
    </a-table>

    <a-modal title="助教详细信息" :visible="visible" @cancel="close">
      <userInfoTable :user="user" />

      <template slot="footer">
        <a-button key="back" @click="close"> 关闭 </a-button>
      </template>
    </a-modal>

    <permission-modal
      :treeData="treeData"
      :permissions="permissions"
      :visible="permissionModalVisible"
      @cancel-permission-modal="hidePermissionModal"
    />
  </aside-layout>
</template>


<script>
import AsideLayout from 'app/vue/views/layouts/aside.vue';
import { Assistant, UserProfiles, AssistantPermission } from "common/vue/service/index.js";
import userInfoTable from "../../components/userInfoTable";
import PermissionModal from './permissionModal.vue';

const columns = [
  {
    title: "用户名",
    dataIndex: "nickname",
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

export default {
  name: "assistants",
  components: {
    userInfoTable,
    AsideLayout,
    PermissionModal
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
      permissionModalVisible: false,
      treeData: [],
      permissions: [],
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
    async edit(id) {
      this.user = await UserProfiles.get(id);
      this.visible = true;
    },
    close() {
      this.visible = false;
    },

    getAssistantPermission() {
      AssistantPermission.search().then(res => {
        const loop = (treeData) => {
          _.forEach(treeData, item => {
            item.disabled = !!item.disabled;
            if (item.children) {
              loop(item.children);
            }
          });
        };
        loop(res.menu);
        this.treeData = res.menu;
        this.permissions = res.permissions;
      });
    },

    showPermissionModal() {
      this.getAssistantPermission();
      this.permissionModalVisible = true;
    },

    hidePermissionModal() {
      this.permissionModalVisible = false;
    }
  },
};
</script>
