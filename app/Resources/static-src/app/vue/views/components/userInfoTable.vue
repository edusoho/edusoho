<template>
  <table class="table table-striped table-condenseda table-bordered">
    <tbody>
      <tr>
        <th width="30%">用户名</th>
        <td width="70%">
          <a class="pull-right" href="javascript:;" @click="toPersonalHomepage(user.id)">个人主页</a>
          {{ user.nickname }}
        </td>
      </tr>

      <tr>
        <th>Email</th>
        <td>{{ user.email }}</td>
      </tr>

      <tr>
        <th>用户组</th>
        <td>{{ user.roleNames.join(' ') }}</td>
      </tr>

      <tr>
        <th>注册时间/IP</th>
        <td>{{ formatTimeIp(user.createdTime, user.createdIp) }}</td>
      </tr>

      <tr>
        <th>最近登录时间/IP</th>
        <td>{{ formatTimeIp(user.loginTime, user.loginIp) }}</td>
      </tr>

      <tr>
        <th>姓名</th>
        <td>{{ formatStr(user.truename) }}</td>
      </tr>

      <tr>
        <th>性别</th>
        <td>{{ formatGender() }}</td>
      </tr>
      <tr>
        <th>身份证号</th>
        <td>{{ formatStr(user.idcard) }}</td>
      </tr>

      <tr>
        <th>手机号码</th>
        <td>{{ formatStr(user.mobile) }}</td>
      </tr>
      <tr>
        <th>公司</th>
        <td>{{ formatStr(user.company) }}</td>
      </tr>

      <tr>
        <th>职业</th>
        <td>{{ formatStr(user.job) }}</td>
      </tr>

      <tr>
        <th>头衔</th>
        <td>{{ formatStr(user.title) }}</td>
      </tr>

      <tr>
        <th>个人签名</th>
        <td>{{ formatStr(user.signature) }}</td>
      </tr>

      <tr>
        <th>自我介绍</th>
        <td>{{ formatStr(user.about) }}</td>
      </tr>

      <tr>
        <th>个人网站</th>
        <td>{{ formatStr(user.site) }}</td>
      </tr>

      <tr>
        <th>微博</th>
        <td>{{ formatStr(user.weibo) }}</td>
      </tr>

      <tr>
        <th>微信</th>
        <td>{{ formatStr(user.weixin) }}</td>
      </tr>

      <tr>
        <th>QQ</th>
        <td>{{ formatStr(user.qq) }}</td>
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
  methods: {
    toPersonalHomepage(id) {
      window.open('/user/' + id + '/about', '_blank');
    },

    formatTimeIp(time, ip) {
      let formatedStr = '';
      if (time != 0) {
        formatedStr += time;
      } else {
        formatedStr += ' -- ';
      }

      if (ip != '') {
        formatedStr += ' / ' + ip + ' 本机IP';
      }

      return formatedStr;
    },

    formatStr(str) {
      return (typeof str == 'undefined' || str == '') ? '暂无' : str;
    },

    formatGender() {
      let allGenders = {'male': '男性', 'female': '女性', 'secret': '秘密'};

      return typeof allGenders['secret'] == 'undefined' ? '秘密' : allGenders[this.user.gender];
    },
  }
};
</script>