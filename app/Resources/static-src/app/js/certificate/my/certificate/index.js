import DateRangePicker from 'app/common/daterangepicker';

new DateRangePicker("#mycart_search_form #date_range", {
    startDate: $("#mycart_search_form [name='startdate']").val(), 
    endDate: $("#mycart_search_form [name='enddate']").val(),
});

$('#mycart_search_form #search').click(function () {
    if ($("#mycart_search_form #date_range").val()) {
        var dates = $("#mycart_search_form #date_range").val().split('-');
        $("#mycart_search_form [name='startdate']").val(dates[0]);
        $("#mycart_search_form [name='enddate']").val(dates[1]);
    }
});

$("#mycart_search_form [name='valid']").click(function(){
    $('#mycart_search_form').submit();
});