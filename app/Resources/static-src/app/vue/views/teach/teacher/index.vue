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
      rowKey="id"
      :pagination="false"
      rowClassName="teacher-manage-row"
    >
      <div slot="loginInfo" slot-scope="item">
        <div>{{ item.loginIp }}</div>
        <div class="color-gray text-sm">{{ $dateFormat(item.loginTime, 'YYYY-MM-DD HH:mm') }}</div>
      </div>

      <div slot="promoteInfo" slot-scope="item">
        <a-checkbox :name="item.id" v-model="item.isPromoted"></a-checkbox>
        <span class="color-gray text-sm">{{ item.promotedSeq }}</span>
        <a class="set-number" href="javascript:;" @click="modalVisible = true">序号设置</a>
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
      :width="920"
      :visible="modalVisible"
      @cancel="closeModal"
    >
      <a-form :form="form" :label-col="{ span: 3 }" :wrapper-col="{ span: 21 }">
        <a-form-item label="序号">
          <a-input
            v-decorator="['title', { rules: [
              { required: true, message: '序号不能为空' },
              { max: 5, message: '序号不能超过5个字符' },
              { validator: validatorTitle }
            ] }]"
          />
          请输入0-10000的整数
        </a-form-item>
      </a-form>
    </a-modal>

  </aside-layout>
</template>


<script>
import AsideLayout from 'app/vue/views/layouts/aside.vue';
import { Teacher, User } from "common/vue/service/index.js";
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
  name: "teachers",
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
      modalVisible: false,
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
    edit(id) {
      this.visible = true;
      // this.user = User.get(id);
      this.user = {
        id: 1,
        nickname: "nickname",
        email: "email@edusoho.com",
        roleNames: ['学员', '教师'],
        createdTime: "1621328200",
        createdIp: "127.0.0.1",
        loginTime: "1621328400",
        loginIp: "136.7.5.14",
        truename: "张三",
        gender: "secret",
        idcard: "",
        mobile: "13765442211",
        company: "杭州阔知网络科技有限公司",
        job: "高级工程师",
        title: "架构师",
        signature: "我的签名",
        site: "http://kd.edusoho.cn",
        weibo: "http://kd.edusoho.cn",
        weixin: "13765442211",
        qq: "11001",
      };
    },
    close() {
      console.log("Clicked cancel button");
      this.visible = false;
    },
  },
};
</script>

<style lang="less">
.teacher-manage-row {
  .set-number {
    display: none;
  }

  &:hover .set-number {
    display: inline-block;
  }
}
</style>
