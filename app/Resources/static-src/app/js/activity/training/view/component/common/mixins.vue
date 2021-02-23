<template>
    <div></div>
</template>
<script>
    import labApi from '../api/api.js'
    export default{
        name:"mixins",
        data(){
            return {
                reqUrl:'https://mockapi.eolinker.com/3WtGzhnc4d14d4a6c5d8dc1dc7f9d292367d48477a2bf67',
                text:"",
                count:0,
                del_lab_id:0,
                percentage:0,
                lab_id:""|sessionStorage.getItem("lab_id"),
            }
        },
        methods:{
            // 创建
            getLabApply(course_id,subsec_id) {
                let _this = this;
                labApi.labApply(this.reqUrl, {
                    "course_id":course_id,
                    "subsec_id": subsec_id,
                }).then(res => {
                    if (res.data.status.code == 2000000) {
                        this.percentage += Math.floor(Math.random() * 10) + 1;
                        this.text = '请求中';
                        _this.lab_id = (res.data.body.lab_id).toString();
                        sessionStorage.setItem("lab_id", res.data.body.lab_id);

                        this.getLabQuery(course_id,subsec_id)
                    } else {
                        // _this.apiErrModal(res.data.status.message)
                    }
                }).catch(err => {
                    // if (err) {
                    //     this.apiErrModal('请求异常，请稍后再试！')
                    // }
                })
            },
            // 查询容器
            getLabQuery() {
                let _this = this;
                let labId = '';
                labId = sessionStorage.getItem('lab_id');

                let timer = setTimeout(() => {
                    // console.log("del_lab_id:"+this.$store.state.lab.del_lab_id + "---labId:"+labId);
                    // if(this.$store.state.lab.del_lab_id >= labId || labId == null){
                    //     clearTimeout();
                    //     return;
                    // }
                    labApi.labQuery(this.reqUrl,{"lab_id": labId}).then(res => {
                        let data = res.data;
                        if (data.status.code == 2000000) {
                            _this.percentage = 95;
                            _this.text = '创建中';
                            let formatDate = data.body.end_at;
                            let nowTime = new Date().getTime();
                            let endTime = new Date(nowTime + 2000 * 60).getTime();
                            _this.expiredIn = data.body.expired_in;
                            _this.end_time = formatDate;
                            _this.checkUrl(`${data.body.address}`, endTime, 'lab_id');
                            sessionStorage.setItem("endTime", data.body.end_at);
                            clearTimeout(timer)
                        } else {
                            if (data.status.code == 5000108) {
                                _this.errorModal = true;
                                _this.errorMsg = data.status.message;
                                clearTimeout(timer)
                            } else if (data.status.code == 5000111) {
                                if (_this.percentage > 90) {
                                    _this.percentage = 95
                                } else {
                                    _this.percentage += Math.floor(Math.random() * 10) + 1
                                }
                                _this.text = '审核中';
                                _this.getLabQuery()
                            } else {
                                _this.apiErrModal(data.status.message);
                                clearTimeout(timer)
                            }
                        }

                    }).catch(err => {
                        if (err) {
                            // _this.apiErrModal('请求异常，请稍后再试！');
                            clearTimeout(timer)
                        }

                    })
                }, 2000);
            },
            //检测状态
            checkUrl(url, endTime, sessionIdKey) {
                let _this = this;
                let checkUrlTimer = setTimeout(function () {
                    _this.percent = 100;
                    _this.text = '启动中';
                    let nowTime = new Date().getTime();
                    let t = endTime - nowTime;
                    
                    if (t > 0 && this.count < 20) {
                        labApi.labStatus(url).then(res => {
                            let data = res.data;
                            if (data.code !== 500) {
                                _this.lab_url = url;
                                clearTimeout(checkUrlTimer)
                            }
                        }).catch(function (error) {
                            this.count++;
                            _this.checkUrl(url, endTime, sessionIdKey)
                        });
                    } else {
                        _this.labDelete(_this.lab_id);
                        sessionStorage.removeItem(sessionIdKey);
                        clearTimeout(checkUrlTimer)
                    }
                }, 3000);
            },
            //删除容器
            labDelete(labId) {
                labApi.labDelete(this.reqUrl,{lab_id:labId});
            },
            // 心跳
            heartbeatInterval(lab_id){
                let _this = this;
                console.log("心跳~~~~");
                console.log("del_lab_id_default"+_this.del_lab_id);
                if(lab_id){
                    let fixedTime = (new Date()).getTime();
                    let timer = setInterval(function(){
                        let nowTime = new Date();
                        let t = (fixedTime + (_this.expiredIn * 1000)) - nowTime.getTime();
                        if(_this.end_time && lab_id != _this.del_lab_id ){
                            if (t > 0) {
                                console.log("砰~");
                                labApi.labHeartbeat(_this.reqUrl,{"lab_id": lab_id}).then(data => {
                                    if(data.data.status.code != 2000000){
                                        sessionStorage.removeItem('lab_id');
                                        if(data.data.status.code != 5000108) {
                                            _this.$alert(data.data.status.message,"请求错误",{
                                                confirmButtonText:'确定',
                                                callback:action=>{
                                                    this.$emit('hideView');
                                                }
                                            });
                                        }
                                        clearInterval(timer);
                                    }
                                }).catch(function (error) {
                                    // clearInterval(timer);
                                });
                            } else {
                                sessionStorage.removeItem('lab_id');
                                _this.envModal = true;
                                clearInterval(timer);
                            }
                        }
                    },10000)

                }
            },
        },
        mounted(){
            let _this = this;
        }
    }
</script>