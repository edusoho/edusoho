<template>
  <e-panel :title="title" :defaul-value="defaulValue">
    <template v-for="item in teacherInfo" v-if="teacherInfo.length">
      <div class="teacher-content">
        <img
          :src="item.avatar && item.avatar.large"
          class="avatar-img">
        <div class="teacher-cell">
          <span class="dark font-family-PFM">{{ item.nickname }}</span>
          <span v-show="item.title" class="text-content">{{ item.title }}</span>
        </div>
      </div>
    </template>
  </e-panel>
</template>
<script>
export default {
  name: 'Teacher',
  props: ['teacherInfo', 'title', 'defaulValue']
}
</script>

