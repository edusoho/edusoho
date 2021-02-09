import notify from 'common/notify';
$(function(){
    var tags = JSON.parse($(".default_tags_value").text()) == null ? [] :JSON.parse($(".default_tags_value").text());
    let cuid = 0;
    init();
    function init(){
        // 初始选中
        $(".list-info li").each(function(){
            cuid = $(this).data("id");
                for(let i= 0; i<tags.length;i++){
                    if(tags[i].id == cuid){
                        $(this).addClass("dataset_active");
                    }
                }
        });
    }

    // 点击保存
    $(".js-pick-btn").on("click",function(){
        let $modal = $(".js-select-container").parents('.modal')
        let $content = $('#task-create-content-iframe').contents();
        $content.find(".dataset-cache").attr("value",JSON.stringify(tags));
        $content.find(".dataset-cache").trigger("click");

         $modal.modal('hide');
         $('.js-close-modal').trigger('click');
    });

    $(".js-select-container").on("click",".pagination li",function(){
        let url = $(".js-select-container").data("url");
        let conditions = "page="+$(this).data("page");
        $.ajax({
            type: 'GET',
            url: url,
            data: conditions
        }).done(function(resp){
            $(".list-info").html(resp);
            init();
        }).fail(function(){
            console.log("fail");
        });
    })

    $(".list-info").on("click",".dataset_li",function(){
        let currentTagId = $(this).data("id");
        let currentTagName = $(this).data("name");
        let check = 0;
        // 判断是新增还是删除
        for(let i=0;i<tags.length;i++){
            if(tags[i].id == currentTagId){
                removeTag(currentTagId);
                check = 1;
                break; 
            }
        }
        if (check == 0){
            if(tags.length >=3){
                notify('danger', "数据集最多只能选择3个！");
                return;
            }
            addTag({id:currentTagId,name:currentTagName});
        }
        $(this).toggleClass("dataset_active")
    })

    // 点击删除标签
    $(".tag-lists").on("click",".bb-icon-close",function(){
        let currentId = $(this).data("id");
        removeTag(currentId);
        $(".dataset_li_"+currentId).removeClass("dataset_active");
    });

    // 增加标签
    function addTag(info){
        console.log(tags);
        tags.push(info);
        let html = "<div class='tag-info checktag-"+info.id+"'><span class='label label-primary'>"+info.name+"+<a id='bb' href='javascript:void(0)' class='panel-tool-close bb-icon-close'  data-id='"+info.id+"'>X</a></span></div>"
        $(".tag-lists").append(html);
    }

    // 删除标签
    function removeTag(id){
        let newTags = [];
        tags.forEach((v,k)=>{
            if(v.id != id){
                newTags.push(v);
                return;
            }
        })
        tags = newTags;
        $(".checktag-"+id).remove();
    }
})