<template>
  <div>
      <e-loading v-if="isLoading"></e-loading>
      <div class="intro-body" v-if="homework">
        <van-panel class="panel intro-panel" title="作业名称">
            <div class="intro-panel__content intro-panel__content--title">{{ homework.name }}</div>
        </van-panel>
        <van-panel class="panel intro-panel" title="作业说明">
            <div class="intro-panel__content" v-html="homework.description"></div>
        </van-panel>
      </div>
      <div class="intro-footer" v-if="homework">
        <van-button class="intro-footer__btn" type="primary" v-if="hasResult" @click="showResult">查看结果</van-button>
        <van-button class="intro-footer__btn" type="primary" v-else @click="startHomework()">开始答题</van-button>
      </div>
  </div>
</template>

<script>
import Api from '@/api';
import { mapState,mapActions } from 'vuex';
import { Dialog,Toast } from "vant";
export default {
    name:'homework-intro',
    data(){
        return{
            courseId:null,
            taskId:null,
            homework:null
        }
    },
    computed: {
        hasResult() {
            const latestHomeworkResult=this.homework.latestHomeworkResult;
            return latestHomeworkResult ? true: false;
        },
        ...mapState({
            isLoading: state => state.isLoading,
            user:state => state.user
        }),
    },
    created(){
        this.getInfo()
    },
    beforeRouteEnter(to, from, next) {
        document.getElementById("app").style.background="#f6f6f6"
        next()
    },
    beforeRouteLeave(to, from, next)  {
        document.getElementById("app").style.background=""
        next()
    },
    methods:{
        getInfo(){
            this.courseId = this.$route.query.courseId;
            this.taskId = this.$route.query.taskId;
            Api.getHomeworkIntro({
                query: {
                    courseId: this.courseId,
                    taskId:this.taskId
                }
            }).then(res => {
                this.homework=res.homework;
                console.log(res)
            });
        },
        showResult(){

        },
        startHomework(){
            this.$router.push({
                name: 'homeworkDo',
                query: {
                    targetId: this.taskId,
                    homeworkId:this.homework.id,
                    courseId:this.$route.query.courseId
                },
                params:{
                    KeepDoing:true
                }
            })
        }
    }
}
</script>

<style>

</style>