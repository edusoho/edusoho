<template>
  <div>
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
      rowKey="id"
    >
      <div slot="loginInfo" slot-scope="item">
        <div>{{ item.loginIp }}</div>
        <div class="color-gray text-sm">{{ item.loginTime }}</div>
      </div>

      <a slot="action" slot-scope="item" @click="edit(item)"> 查看 </a>
    </a-table>

    <a-modal
      title="助教详细信息"
      :visible="visible"
      @cancel="close"
    >

      <template slot="footer">
        <a-button key="back" @click="close">
          关闭
        </a-button>
      </template>
    </a-modal>
  
  </div>
</template>


<script>
import { Assistant } from "common/vue/service/index.js";

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
  data() {
    return {
      visible: false,
      editInfo: {},
      columns,
      pageData: [],
    };
  },
  created() {
    this.pageData = this.onSearch();
    // console.log(Assistant.search());
  },
  methods: {
    onSearch() {
      return [
        {
          id: "1",
          nickname: "teacher",
          loginTime: "1621328400",
          loginIp: "136.7.5.14",
        },
      ];
    },
    edit(item) {
      this.visible = true;
      this.editInfo = item;
    },
    close() {
      console.log('Clicked cancel button');
      this.visible = false;
    },
  },
};
</script>
