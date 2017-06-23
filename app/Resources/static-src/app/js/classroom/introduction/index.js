
function postClassroomViewEvent()
{
    let $obj = $('#event-report');
    let postData = $obj.data();
    $.post($obj.data('url'), postData);
}

postClassroomViewEvent();