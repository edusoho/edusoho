import { publishCourse } from '../course-manage/help';

$("#course-select").change(function(){
	location.href = $(this).val();
});

publishCourse();

