<template>
  
</template>

<script>
import Api from '@/api';
import * as types from '@/store/mutation-types';
import { mapState, mapMutations,mapActions } from 'vuex';
import { Toast,Overlay,Popup,Dialog,Lazyload } from "vant";
export default {
    name:'homework-do',
    data(){
        return{
            info:[],
            answer:{},
            lastAnswer:null
        }
    },
    created() {
        this.getData();
    },
    methods:{
        ...mapMutations({
            setNavbarTitle: types.SET_NAVBAR_TITLE
        }),
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
                this.afterGetData(res);
            })
            .catch(err => {
                Toast.fail(err.message);
            });
        },
        //获取到数据后进行操作
        afterGetData(res){
            this.setNavbarTitle(res.paperName);
            this.formatData(res)
        },
        //遍历数据类型去做对应处理
        formatData(res){
             let paper = res.items;
             let info = [];
             let answer = [];
             paper.forEach(item=>{
                 if(item.type!= "material"){
                    this.sixType(item.type, item);
                 }
                 if(item.type== "material"){
                    let title = Object.assign({}, item, { subs: "" });
                    item.subs.forEach((sub, index) => {
                        sub.parentTitle = title;//材料题题干
                        sub.parentType = item.type;//材料题题型
                        sub.materialIndex = index+1;//材料题子题的索引值，在页面要显示
                        this.sixType(sub.type, sub);
                    });
                 }
             })
        },
        //处理六大题型数据
        sixType(type, item) {
            if (type == "single_choice") {
                //刷新页面或意外中断回来数据会丢失，因此要判断本地是否有缓存数据，如果有要把数据塞回
                if(this.lastAnswer){
                this.$set(this.answer,item.id,this.lastAnswer[item.id])
                }else{
                this.$set(this.answer,item.id,[])
                }
                this.info.push(item);
            }
            if (type == "choice" || type == "uncertain_choice") {
                if(this.lastAnswer){
                this.$set(this.answer,item.id,this.lastAnswer[item.id])
                }else{
                this.$set(this.answer,item.id,[])
                }
                this.info.push(item);
            }
            if (type == "essay") {
                if(this.lastAnswer){
                this.$set(this.answer,item.id,this.lastAnswer[item.id])
                }else{
                this.$set(this.answer,item.id,[""])
                }
                this.info.push(item);
            }

            if (type == "fill") {
                let fillstem = item.stem;
                let { stem, index } = this.fillReplce(fillstem, 0);
                item.stem = stem;
                item.fillnum = index;
                if(this.lastAnswer){
                this.$set(this.answer,item.id,this.lastAnswer[item.id])
                }else{
                this.$set(this.answer,item.id,new Array(index).fill(""))
                }
                this.info.push(item);
            }

            if (type == "determine") {
                if(this.lastAnswer){
                this.$set(this.answer,item.id,this.lastAnswer[item.id])
                }else{
                this.$set(this.answer,item.id,[])
                }
                this.info.push(item);
            }
        },
        //处理富文本，并统计填空题的空格个数
        fillReplce(stem, index) {
            const reg = /\[\[.+?\]\]/;
            while (reg.exec(stem)) {
                stem = stem.replace(reg, () => {
                return `<span class="fill-bank">（${++index}）</span>`;
                });
            }
            return { stem, index };
        },

    }
}
</script>

<style>

</style>