/Users/Simon/Projects/php/edusoho/src/Topxia/Service/User/CurrentUser.php

getSelectOrgCode()


/Users/Simon/Projects/php/edusoho/src/Topxia/Service/Common/BaseService.php
fillOrgId()


/Users/Simon/Projects/php/edusoho/src/Org/Service/Org/Impl/OrgServiceImpl.php
switchOrg()


### 

接入组织机构的模块

    1.课程
    2.班级
    3.article
    5.announcement
    6.登录日志 login_record
    7.user_approval
    
###
 批量 更新
     {% include 'OrgBundle:Org:batch-update-org-btn.html.twig' with {module:'user', formId:'user-table'} %}