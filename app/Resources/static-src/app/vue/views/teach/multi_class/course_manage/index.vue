<template>
  <div class="course-manage">
    <div class="clearfix" style="margin-bottom: 24px;">
      <a-menu class="manage-menu pull-left" v-model="current" mode="horizontal">
        <a-menu-item class="manage-menu-item" key="class-info">
          <router-link :to="`/course_manage/${multiClassId}`">课时信息</router-link>
        </a-menu-item>
        <a-menu-item class="manage-menu-item" key="student-manage">
          <router-link :to="`/course_manage/${multiClassId}/student_manage`">学员管理</router-link>
        </a-menu-item>
        <a-menu-item class="manage-menu-item" key="homework-review">
          <router-link :to="`/course_manage/${multiClassId}/homework_review`">作业批阅</router-link>
        </a-menu-item>
        <a-menu-item class="manage-menu-item manage-menu-item--space" key="data-preview">
          <router-link :to="`/course_manage/${multiClassId}/data_preview`">数据预览</router-link>
        </a-menu-item>
      </a-menu>

      <a-menu class="manage-menu manage-menu-blank pull-right" :selectable="false" mode="horizontal">
        <a-menu-item class="manage-menu-item">
          公告管理
        </a-menu-item>
        <a-menu-item class="manage-menu-item">
          录播管理
        </a-menu-item>
        <a-menu-item class="manage-menu-item manage-menu-item--space">
          订单管理
        </a-menu-item>
      </a-menu>
    </div>

    <router-view />
  </div>
</template>

<script>
export default {
  name: 'MultiClassCourseManage',

  data() {
    return {
      current: ['class-info'],
      multiClassId: 0
    }
  },

  created() {
    this.multiClassId = this.$route.params.id;
    this.current = [this.$route.meta.current];
  }
}
</script>

<style lang="less">
.course-manage {
  .manage-menu {
    border-bottom: none;

    .manage-menu-item {
      padding: 0;
      margin-right: 48px;

      a {
        text-decoration: none;
      }
    }

    .manage-menu-item--space {
      margin-right: 0;
    }
  }

  .manage-menu-blank {
    .manage-menu-item:hover {
      border-bottom: 2px solid transparent;
    }
  }
}
</style>
