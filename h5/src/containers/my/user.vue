<template>
  <div class="user">
    <div class="user-section">
      <img class='user-img' :src="user.avatar.small" />
      <span class='user-name'>{{user.nickname}}</span>
      <router-link to="/my/setting">
        <img class='user-setting' src='/static/images/setting.png'>
      </router-link>
    </div>
  </div>
</template>
<script>
import { mapState } from 'vuex';

export default {
  computed: {
    ...mapState({
      user: state => state.user
    })
  },
  created() {
    console.log(this.user);
  }
}
</script>
