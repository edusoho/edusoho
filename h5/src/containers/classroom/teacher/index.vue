<template>
  <e-panel :title="title" :defaulValue="defaulValue">
    <div v-if="teacherInfo.length" v-for="item in teacherInfo">
      <div class="teacher-content">
        <img :src="item.avatar && item.avatar.large"
        class="avatar-img">
        <div class="teacher-cell">
          <span class="dark font-family-PFM">{{ item.nickname }}</span>
          <span class="text-content" v-show="item.title">{{ item.title }}</span>
        </div>
      </div>
    </div>
  </e-panel>
</template>
<script>
export default {
  name: 'teacher',
  props: ['teacherInfo', 'title', 'defaulValue'],
}
</script>

