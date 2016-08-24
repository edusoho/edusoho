{{ render(controller('TopxiaWebBundle:Attachment:list', {targetType: 'group.thread.post', targetId: post.id })) }}
{{ render(controller('TopxiaWebBundle:Attachment:list', {targetType: 'group.thread', targetId: threadMain.id })) }}

{{ render(controller('TopxiaWebBundle:Attachment:formFields', {targetType: 'group.thread', targetId: thread.id|default(0)})) }}


小组
group.thread
group.thread.post

资讯
article

'article' => 0, 'course' => 0, 'classroom' => 0, 'group' => 0, 'question' => 0