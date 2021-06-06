<template>
  <aside-layout :breadcrumbs="[{ name: '班课' }, { name: multiClass.title }]" class="course-manage">
    <div class="clearfix" style="margin-bottom: 24px;">
      <a-menu class="manage-menu pull-left" v-model="current" mode="horizontal">
        <a-menu-item class="manage-menu-item" key="class-info">
          <router-link :to="{ name: 'MultiClassCourseManage' }">课时管理</router-link>
        </a-menu-item>
        <a-menu-item class="manage-menu-item" key="student-manage">
          <router-link :to="{ name: 'MultiClassStudentManage' }">学员管理</router-link>
        </a-menu-item>
        <a-menu-item class="manage-menu-item" key="homework-review">
          <router-link :to="{ name: 'MultiClassHomewordReview' }">作业批阅</router-link>
        </a-menu-item>
        <a-menu-item class="manage-menu-item manage-menu-item--space" key="data-preview">
          <router-link :to="{ name: 'MultiClassDataPreview'}">数据预览</router-link>
        </a-menu-item>
      </a-menu>

      <a-menu v-if="multiClass.course" class="manage-menu manage-menu-blank pull-right" :selectable="false" mode="horizontal">
        <a-menu-item class="manage-menu-item">
          <a :href="`/announcement/course/${multiClass.course.id}/list`" target="_blank">公告管理</a>
        </a-menu-item>
        <a-menu-item class="manage-menu-item">
          <a :href="`/course_set/${multiClass.course.courseSetId}/manage/course/${multiClass.course.id}/replay`" target="_blank">录播管理</a>
        </a-menu-item>
        <a-menu-item class="manage-menu-item manage-menu-item--space">
          <a :href="`/course_set/${multiClass.course.courseSetId}/manage/course/${multiClass.course.id}/orders`" target="_blank">订单管理</a>
        </a-menu-item>
      </a-menu>
    </div>

    <router-view />
  </aside-layout>
</template>

<script>
import { Course, MultiClass } from 'common/vue/service';
import AsideLayout from 'app/vue/views/layouts/aside.vue';

export default {
  name: 'MultiClassCourseManage',

  components: {
    AsideLayout
  },

  data() {
    return {
      current: ['class-info'],
      id: this.$route.params.id,
      multiClass: {}
    }
  },

  befeoreRouteUpdate(to, from, next) {
    this.id = to.params.id
    next()
  },

  created() {
    this.current = [this.$route.meta.current];
    this.getMultiClass();
  },

  methods: {
    async getMultiClass() {
      this.multiClass = await MultiClass.get(this.id);
    }
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
