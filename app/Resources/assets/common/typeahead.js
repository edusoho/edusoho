import 'typeahead.js';

const typeahead = ($element, source, display = function(){} ) => {
    let converKey = {'classroom': '班级','course':'课程'};
    $element.typeahead (
        {
            highlight: true
        },
        {
            //数据来源
            source: source,
            //请求方式
            async: true,
            //
            limit: 10,
            //模板类型
            //
            templates: {
                suggestion: function(ob){
                    let type = converKey[ob.type];
                    return `<div class="suggestion">
                        <div class="suggestion-left">${type}</div>
                        <div class="suggestion-content">${ob.title}</div>
                        <div class="suggestion-right">ID: ${ob.id}</div>
                    </div>`; 
                }
            },
            //选中返回值
            display: display
        }
    );
}

export default typeahead;
