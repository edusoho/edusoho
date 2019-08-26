<template>
  
</template>

<script>
import Api from '@/api';
export default {
    name:'homework-do',
    created() {
        this.getData();
    },
    methods:{
        //请求接口获取数据
        getData() {
            let homeworkId=this.$route.query.homeworkId;
            let targetId=this.$route.query.targetId;
            let action=this.$route.query.action;
            Api.getHomeworkInfo({
                query: {
                    homeworkId
                },
                data: {
                    targetId,
                    targetType: "task"
                }
            })
            .then(res => {
                console.log(res)
                //this.afterGetData(res);
            })
            .catch(err => {
                Toast.fail(err.message);
            });
        },

    }
}
</script>

<style>

</style>