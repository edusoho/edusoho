<template>
  <div class="find-search">
    <form action="/">
      <van-search
        v-model="selectedData.courseSetTitle"
        shape="round"
        show-action
        placeholder="搜索课程、班级"
        @search="onSearch"
        @cancel="onCancel"
      />
    </form>
    <van-tabs
      v-if="isSearch"
      v-model="active"
      color="#408FFB"
      line-height="1"
      line-width="30"
      title-active-color="#408FFB"
    >
      <van-tab title="课程">
        <lazyLoading
          :course-list="courseList"
          :is-all-data="isAllCourse"
          :vip-tag-show="true"
          course-item-type="price"
          :is-request-compile="course.isRequestCompile"
          :type-list="'course_list'"
          @needRequest="sendRequest"
        />
        <emptyCourse
          v-if="isEmptyCourse && course.isRequestCompile"
          :has-button="false"
           text="抱歉，没有找到相关内容"
          :type="'course_list'"
        />
      </van-tab>
      <van-tab title="班级">
        <div v-if="active === 1">
          <lazyLoading
            :course-list="classroomList"
            :is-all-data="isAllClassroom"
            :normal-tag-show="false"
            :vip-tag-show="true"
            course-item-type="price"
            :is-request-compile="classroom.isRequestCompile"
            :type-list="'classroom_list'"
            @needRequest="sendRequest"
          />
          <emptyCourse
            v-if="isEmptyClassroom && classroom.isRequestCompile"
            :has-button="false"
            text="抱歉，没有找到相关内容"
            :type="'classroom_list'"
          />
        </div>
      </van-tab>
      <van-tab title=""> </van-tab>
      <van-tab title=""> </van-tab>
    </van-tabs>
  </div>
</template>
<script>
import lazyLoading from "&/components/e-lazy-loading/e-lazy-loading.vue";
import emptyCourse from "@/containers/learning/emptyCourse/emptyCourse.vue";
import Api from "@/api";
export default {
  nama: "search",
  components: {
    lazyLoading,
    emptyCourse
  },
  data() {
    return {
      active: 0,
      selectedData: {
        courseSetTitle: ""
      },
      isSearch:false,
      classroomList: [],
      isEmptyClassroom: false,
      isAllClassroom: false,
      classroom: {
        isRequestCompile: false,
        offset: 0,
        limit: 10
      },
      courseList: [],
      isEmptyCourse: false,
      isAllCourse: false,
      course: {
        isRequestCompile: false,
        offset: 0,
        limit: 10
      }
    };
  },
  created() {

  },
  methods: {
    onSearch() {
      this.isSearch=true;
      this.initCourseList();
      this.requestCourses();
      this.initClassroomList();
      this.requestClassroom();
    },
    onCancel() {
      this.isSearch=false;
      this.$router.push({ path:'/'  })
    },
    initClassroomList() {
      this.classroom.isRequestCompile = false;
      this.isAllClassroom = false;
      this.classroomList = [];
      this.classroom.offset = 0;
    },

    judegIsAllClassroom(paging) {
      return this.classroomList.length >= paging.total;
    },

    requestClassroom() {
      this.classroom.isRequestCompile = false;
      const setting = {
        offset: this.classroom.offset,
        limit: this.classroom.limit
      };
      const config = Object.assign({}, this.selectedData, setting);
      return Api.getClassList({
        params: config
      })
        .then(({ data, paging }) => {
          data.forEach(element => {
            this.classroomList.push(element);
          });
          this.requestClassRoomSuccess(paging);
        })
        .catch(err => {
          console.log(err, "error");
        });
    },

    requestClassRoomSuccess(paging = {}) {
      this.isAllClassroom = this.judegIsAllClassroom(paging);
      if (!this.isAllClassroom) {
        this.classroom.offset = this.classroomList.length;
      }
      this.classroom.isRequestCompile = true;
      this.isEmptyClassroom = this.classroomList.length === 0;
    },

    sendRequest() {
      if (!this.isAllClassroom) this.requestClassroom();
    },

    initCourseList() {
      this.course.isRequestCompile = false;
      this.isAllCourse = false;
      this.courseList = [];
      this.course.offset = 0;
    },

    judegIsAllCourse(paging) {
      return this.courseList.length >= paging.total;
    },

    requestCourses() {
      this.course.isRequestCompile = false;
      const setting = {
        offset: this.course.offset,
        limit: this.course.limit
      };
      const config = Object.assign({}, this.selectedData, setting);
      return Api.getCourseList({
        params: config
      })
        .then(({ data, paging }) => {
          data.forEach(element => {
            this.courseList.push(element);
          });
          this.requestCoursesSuccess(paging);
        })
        .catch(err => {
          console.log(err, "error");
        });
    },

    requestCoursesSuccess(paging = {}) {
      this.isAllCourse = this.judegIsAllCourse(paging);
      if (!this.isAllCourse) {
        this.offset = this.courseList.length;
      }
      this.course.isRequestCompile = true;
      this.isEmptyCourse = this.courseList.length === 0;
    },

    sendRequest() {
      if (!this.isAllCourse) this.requestCourses();
    }
  }
};
</script>
