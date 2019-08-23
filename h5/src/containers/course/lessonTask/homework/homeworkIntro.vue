<template>
  
</template>

<script>
import Api from '@/api';
export default {
    name:'homework-intro',
    data(){
        return{
            courseId:null,
            taskId:null
        }
    },
    created(){
        this.getInfo()
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
                console.log(res)
            });
        }
    }
}
</script>

<style>

</style>