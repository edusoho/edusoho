<template>
  <table class="table table-striped table-condenseda table-bordered">
    <tbody>
      <tr>
        <th width="30%">用户名</th>
        <td width="70%">
          <a class="pull-right" href="javascript:;" @click="toPersonalHomepage(user.user.uuid)">个人主页</a>
          {{ user.user.nickname }}
        </td>
      </tr>

      <tr>
        <th>Email</th>
        <td>{{ user.profile.email || '- -' }}</td>
      </tr>

      <tr>
        <th>用户组</th>
        <td>{{ getUserRoles }}</td>
      </tr>

      <tr>
        <th>注册时间/IP</th>
        <td>{{ formatTimeIp(user.user.createdTime, user.user.createdIp) }}</td>
      </tr>

      <tr>
        <th>最近登录时间/IP</th>
        <td>{{ formatTimeIp(user.user.loginTime, user.user.loginIp) }}</td>
      </tr>

      <tr>
        <th>姓名</th>
        <td>{{ formatStr(user.profile.truename) }}</td>
      </tr>

      <tr>
        <th>性别</th>
        <td>{{ formatGender() }}</td>
      </tr>
      <tr>
        <th>身份证号</th>
        <td>{{ formatStr(user.profile.idcard) }}</td>
      </tr>

      <tr>
        <th>手机号码</th>
        <td>{{ formatStr(user.profile.mobile) }}</td>
      </tr>
      <tr>
        <th>公司</th>
        <td>{{ formatStr(user.profile.company) }}</td>
      </tr>

      <tr>
        <th>职业</th>
        <td>{{ formatStr(user.profile.job) }}</td>
      </tr>

      <tr>
        <th>头衔</th>
        <td>{{ formatStr(user.user.title) }}</td>
      </tr>

      <tr>
        <th>个人签名</th>
        <td>{{ formatStr(user.profile.signature) }}</td>
      </tr>

      <tr>
        <th>自我介绍</th>
        <td class="editor-text" v-html="user.profile.about || '暂无'"></td>
      </tr>

      <tr>
        <th>个人网站</th>
        <td>{{ formatStr(user.profile.site) }}</td>
      </tr>

      <tr>
        <th>微博</th>
        <td>{{ formatStr(user.profile.weibo) }}</td>
      </tr>

      <tr>
        <th>微信</th>
        <td>{{ formatStr(user.profile.weixin) }}</td>
      </tr>

      <tr>
        <th>QQ</th>
        <td>{{ formatStr(user.profile.qq) }}</td>
      </tr>
    </tbody>
  </table>
</template>

<script>

export default {
  props: {
    user: {
      type: Object,
      default: {},
    },
  },

  computed: {
    getUserRoles() {
      return _.join(this.user.user.roles, ' ');
    }
  },

  methods: {
    toPersonalHomepage(uuid) {
      window.open('/user/' + uuid + '/about', '_blank');
    },

    formatTimeIp(time, ip) {
      let formatedStr = '';
      if (time != 0) {
        formatedStr += this.$dateFormat(time, 'YYYY-MM-DD HH:mm');
      } else {
        formatedStr += ' -- ';
      }

      if (ip != '') {
        formatedStr += ' / ' + ip + ' 本机IP';
      }

      return formatedStr;
    },

    formatStr(str) {
      return (typeof str == 'undefined' || str == '' || str == null) ? '暂无' : str;
    },

    formatGender() {
      const allGenders = { male: '男性', female: '女性', secret: '秘密' };

      return allGenders[this.user.profile.gender];
    },
  }
};
</script>
