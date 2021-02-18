<template>
    <div>
        <el-row>
            <div class="course-manage-subltitle cd-mb40 ml0">数据集</div>
            <el-col :span="12">
                <el-form :model="form" :rules="formRule"
                        ref="baseInfoForm"
                        label-position="right"
                        label-width="150px">
                    <el-form-item label="数据集标题">
                        <el-col>
                            <el-input ref="title" v-model="form.title" auto-complete="off"></el-input>
                        </el-col>
                    </el-form-item>
                    <el-form-item label="描述信息">
                        <el-col>
                            <el-input ref="subtitle" v-model="form.subtitle" type="textarea" rows="3"></el-input>
                        </el-col>
                    </el-form-item>
                    <el-form-item label="挂载目录列表">
                        <el-col>
                            <el-table
                                :data="tableData"
                                stripe>
                                <el-table-column prop="name" label="名称"></el-table-column>
                                <el-table-column prop="path" label="路径"></el-table-column>
                                <el-table-column prop="action" label="操作">
                                    <template slot-scope="scope">
                                        <el-button
                                        size="mini"
                                        @click="deleteDirectory(scope.row)">删除</el-button>
                                    </template>
                                </el-table-column>
                            </el-table>
                        </el-col>
                    </el-form-item>
                </el-form>
            </el-col>
            <el-col :span="12">
                <el-form :model="form" :rules="formRule">
                    <el-form-item label="挂载目录列表">
                        <el-col>
                            <v-tree ref='tree' :data='treeData' :multiple="false" :tpl="tpl" :selectAlone='true' :halfcheck='true' :topMustExpand="false"/>
                        </el-col>
                    </el-form-item>
                </el-form>
            </el-col>
        </el-row>
    </div>

</template>

<script>
    import * as validation from 'common/element-validation';


    export default {
        name: "base-info",
        methods: {
            getTree(path){
                let _this = this;
                let data = {
                    url:_this.mcp_domain_name,
                }
                if(path !==undefined && path !== ''){
                    data.path = path;
                }
                return this.getTreeList({
                    data:data,
                    callback:res=>{}
                })
            },
            tpl (...args) {
                let {0: node, 2: parent, 3: index} = args
                let titleClass = node.selected ? 'node-title node-selected' : 'node-title'
                let dis = node.isChildren;
                    return <span>
                        <span class={titleClass} domPropsInnerHTML={node.name} onClick={() => {
                        this.$refs.tree.nodeSelected(node)
                        }}></span>
                        <el-button type="primary" onClick={() => this.asyncLoad(node)} disabled={dis} size="mini">刷新</el-button>
                    </span>
            },
            async asyncLoad (node) {
                if(node.children !== undefined) return;
                    this.$set(node, 'loading', true)
                
                if(!this.is_req){
                    this.is_req = true;
                    let pro = this.getTree(node.path);
                    pro.then(res=>{
                        if(res.data.status.code == 2000000){
                            if(res.data.body.length == 0){
                                this.$Message.error("无子目录");
                            }else{
                                // name 替换title
                                res.data.body.forEach((v,k)=>{
                                    v.title = v.name;
                                })
                                this.$refs.tree.addNodes(node, res.data.body);
                            }
                            node.isChildren = true;
                        }
                        this.$set(node, 'loading', false);
                        this.is_req = false;
                    }).catch(res=>{
                        _this.$Message.error("获取目录失败");
                        this.is_req = false;
                    })
                }
            },
            search () {
                this.$refs.tree.searchNodes(this.searchword)
            }
        //     validateForm() {
        //         if (!this.$refs.baseInfoForm) {
        //             return {result: true, invalidFields: {}};
        //         }

        //         let result = false;
        //         let invalids = {};
        //         this.$refs.baseInfoForm.clearValidate();

        //         this.$refs.baseInfoForm.validate((valid, invalidFields) => {
        //             if (valid) {
        //                 result = true;
        //             } else {
        //                 invalids = invalidFields;
        //             }
        //         });

        //         return {result: result, invalidFields: invalids};
        //     },
        //     getFormData() {
        //         this.form.orgCode = $('.js-org-tree-select').children('option:selected').val();
        //         this.form.about = aboutEditor.getData();

        //         return this.form;
        //     },
        },
        data() {
            return {
                treeData: [{
        title: 'node1',
        expanded: true,
        children: [{
          title: 'node 1-1',
          expanded: true,
          children: [{
            title: 'node 1-1-1'
          }, {
            title: 'node 1-1-2'
          }, {
            title: 'node 1-1-3'
          }]
        }, {
          title: 'node 1-2',
          children: [{
            title: "<span style='color: red'>node 1-2-1</span>"
          }, {
            title: "<span style='color: red'>node 1-2-2</span>"
          }]
        }]
      }],
                tableData: [{
                name: '猫狗测试',
                path: '/www/work',
                }],
                uploadImageTemplate: '',
                form: {
                    title: "标题",
                    subtitle: "副标题",
                },
                formRule: {
                    title: [
                        {
                            required: true,
                            message: "请输入标题",
                            trigger: 'blur'
                        },
                        {
                            min: 2,
                            message: Translator.trans('validate.length_min.message', {'length': 2}),
                            trigger: 'blur',
                        },
                        {
                            max: 30,
                            message: Translator.trans('validate.length_max.message', {'length': 30}),
                            trigger: 'blur',
                        },
                        {validator: validation.trim, trigger: 'blur'},
                        {validator: validation.course_title, trigger: 'blur'}
                    ],
                    subtitle: [
                        {
                            max: 30,
                            message: Translator.trans('validate.length_max.message', {'length': 30}),
                            trigger: 'blur',
                        },
                    ]
                },
            };
        },
        mounted() {
        }
    }
</script>

<style scoped>

</style>