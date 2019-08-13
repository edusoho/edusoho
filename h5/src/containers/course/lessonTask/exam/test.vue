<template>
  <div>测试组件</div>
</template>

<script>
let s="<p>但是我奋斗的是[[我其实对方认为|v 奋斗色个人]]dwqfdwqer[[dewqad2w|dfwqe ]]</p>";
let index=0
export default {
    name:'',
    methods:{
        a(rr){
            const reg=/\[\[.+?\]\]/;
            if(!reg.test(rr)){
                return false
            }else{
                // s=s.macth(reg,`<span>${index}</span>`);
                // a(s)
                s=rr.replace(reg,() => {
                    return `<span>${++index}</span>`
                });
                a(s)
            }
        }
    }
}
</script>

<style>

</style>