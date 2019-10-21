let data = {
    title:'用户',
    data:[
        {
            name: '用户管理',
            id: '11',
            link: '',   // 链接
            grade: 0,   // 层级
            nodes: [{
                name: '用户管理二级',
                id: '11',
                link: '',
                grade: 1,
                nodes: []
            }, {
                name: '在线用户',
                id: '11',
                link: '',
                grade: 1
            }, {
                name: '登陆日志',
                id: '11',
                link: '',
                grade: 1,
                nodes: []
            }]
        }, {
            name: '数据中心',
            id: '11',
            link: '',
            grade: 0,
            nodes: [{
                name: '数据中心',
                id: '11',
                link: '',
                grade: 1,
                nodes: []
            }, {
                name: '数据中心',
                link: '',
                grade: 1
            }]
        }
    ]
}

cd.layout({data});
