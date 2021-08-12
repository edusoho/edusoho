<template>
  <aside-layout :breadcrumbs="[{ name: '班课巡检' }]">
    <a-spin class="multi-class-inspection" :spinning="getListLoading">
      <div class="inspection-title">今日课程</div>
      <a-row :gutter="[24,24]">
        <a-col :sm="24" :lg="12" :xl="8" :xxl="6" v-for="inspection in inspectionList" :key="inspection.id">
          <inspection-card :inspection="inspection" />
        </a-col>
      </a-row>
      <empty v-if="!getListLoading && !inspectionList.length" />
    </a-spin>
  </aside-layout>
</template>

<script>
import AsideLayout from "app/vue/views/layouts/aside.vue";
import Empty from "app/vue/views/components/Empty.vue";
import InspectionCard from "./InspectionCard.vue";
import { MultiClassInspection } from "common/vue/service/index.js";

export default {
  name: "index",
  components: {
    AsideLayout,
    Empty,
    InspectionCard,
  },

  data() {
    return {
      inspectionList: [
        {
          id: "90",
          courseId: "123",
          multiClassId: "0",
          seq: "23",
          categoryId: "103",
          activityId: "90",
          title: "直播1",
          isFree: "0",
          isOptional: "0",
          startTime: "1628581500",
          endTime: "1628581800",
          mode: "lesson",
          isLesson: "1",
          status: "published",
          number: "10",
          type: "live",
          mediaSource: "",
          maxOnlineNum: "0",
          fromCourseSetId: "122",
          length: "5",
          copyId: "0",
          createdUserId: "2",
          createdTime: "1628581290",
          updatedTime: "1628581361",
          syncId: "0",
          multiClass: {
            id: "4",
            title: "新建班课1",
            courseId: "123",
            productId: "2",
            maxStudentNum: "10",
            isReplayShow: "1",
            liveRemindTime: "0",
            start_time: null,
            end_time: null,
            status: "",
            type: "normal",
            service_setting_type: "default",
            service_num: "0",
            group_limit_num: "0",
            copyId: "0",
            creator: "0",
            createdTime: "1617782100",
            updatedTime: "1628585229",
          },
          studentNum: "5",
          teacherInfo: {
            id: "226",
            nickname: "教师2",
            destroyed: "0",
            title: "",
            weChatQrCode: "",
            uuid: "6cb985d1a0603bd3a36af51a0df5399155ab3965",
            avatar: {
              small: "http://es.dev.cn/assets/img/default/avatar.png",
              middle: "http://es.dev.cn/assets/img/default/avatar.png",
              large: "http://es.dev.cn/assets/img/default/avatar.png",
            },
          },
          assistantInfo: [
            {
              id: "227",
              nickname: "教师3",
              destroyed: "0",
              title: "",
              weChatQrCode: "",
              uuid: "f9430fe37d20fee22b2fe5436b1cb9dc6f7b0693",
              avatar: {
                small: "http://es.dev.cn/assets/img/default/avatar.png",
                middle: "http://es.dev.cn/assets/img/default/avatar.png",
                large: "http://es.dev.cn/assets/img/default/avatar.png",
              },
            },
            {
              id: "225",
              nickname: "教师1",
              destroyed: "0",
              title: "副教授",
              weChatQrCode: "",
              uuid: "58e4a5ffc89ac7365c87026bdec46d464c065d1b",
              avatar: {
                small:
                  "http://es.dev.cn/files/user/2021/05-06/1614135126fe523784.jpg",
                middle:
                  "http://es.dev.cn/files/user/2021/05-06/161413511336660950.jpg",
                large:
                  "http://es.dev.cn/files/user/2021/05-06/16141350ff81824873.jpg",
              },
            },
          ],
        },
      ],
      getListLoading: false,
    };
  },

  computed: {},

  created() {
    this.getMultiClassInspectionList();
  },

  methods: {
    async getMultiClassInspectionList() {
      this.getListLoading = true;
      try {
        this.inspectionList = await MultiClassInspection.search();
      } finally {
        this.getListLoading = false;
      }
    },
  },
};
</script>
<style lang='less' scoped>
.multi-class-inspection {
  .inspection-title {
    font-size: 16px;
    color: #333333;
    letter-spacing: 0;
    line-height: 24px;
    font-weight: 400;
  }
}
</style>