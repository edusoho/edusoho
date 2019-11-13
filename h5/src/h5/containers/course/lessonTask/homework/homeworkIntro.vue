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

import homeworkMixin from '@/mixins/lessonTask/homework.js';

export default {
    name:'homework-intro',
    mixins: [homeworkMixin],
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
        ...mapActions('course', [
                'handHomeworkdo',
            ]),
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

                this.interruption()
            });
        },
        //异常中断
        interruption(){
            this.canDoing(this.homework.latestHomeworkResult,this.user.id).then(()=>{
                this.startHomework();
            }).catch(({answer})=>{
                this.submitHomework(answer)
            })
        },
        //跳转到结果页
        showResult() {
            this.$router.push({
                name: 'homeworkResult',
                query: {
                    homeworkId: this.homework.id,
                    homeworkResultId:this.homework.latestHomeworkResult.id,
                    courseId: this.$route.query.courseId,
                    taskId:this.taskId
                }
            })
        },
        //开始作业
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
        },
        //交作业
        submitHomework(answer){
            let datas={
                answer,
                homeworkId:this.homework.id,
                userId:this.user.id,
                homeworkResultId:this.homework.latestHomeworkResult.id
            }
            //提交作业+跳转到结果页
            this.handHomeworkdo(datas).then(res=>{
                this.showResult()
            }).catch((err)=>{
                Toast.fail(err.message);
            });
        },
    }
}
</script>

<style>

</style>