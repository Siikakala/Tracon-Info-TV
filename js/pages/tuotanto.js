$(document).ready(function() {
    $(document).on("contextmenu",function(e){
      return false; //tapetaan selaimen oma context menu koko sivulta
   });
   widen();
});

$(function(){
    $("#start_date").datepicker({
		defaultDate: "+1w",
		firstDay: 1,
		minDate:-7,
		maxDate:"+1Y +6M",
		dateFormat:"dd.mm.yy",
		changeMonth: true,
		numberOfMonths: 1,
		changeYear: true
	});

});