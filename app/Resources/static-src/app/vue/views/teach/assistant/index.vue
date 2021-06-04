<template>
  <aside-layout :breadcrumbs="[{ name: '助教管理' }]">
    <div class="clearfix cd-mb24">
      <a-input-search
        placeholder="请输入用户名搜索"
        style="width: 224px"
        @search="onSearch"
      />
      <a-button class="pull-right" type="primary" @click="setAssistantRoles">助教权限设置</a-button>
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

    <div class="text-center">
      <a-pagination class="mt6"
        v-if="paging"
        v-model="paging.page"
        :total="paging.total"
        show-less-items
      />
    </div>

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
      console.log("Clicked cancel button");
      this.visible = false;
    },
    setAssistantRoles() {
     console.log('set assistant roles')
    },
  },
};
</script>
