$(document).ready(function() {
    $(document).on("contextmenu",function(e){
      return false; //tapetaan selaimen oma context menu koko sivulta
   });
   widen();
   $(window).bind("keydown",function(e){
        switch(e.which){
            case 13://enter
                add();
                break;
        }
    });
    ref();
});

$("form").submit(function(e) {
    e.preventDefault();
});

$(function(){
    $("#start_date").datepicker({
		defaultDate: "today",
		firstDay: 1,
		minDate:-7,
		maxDate:"+1Y +6M",
		dateFormat:"dd.mm.yy",
		changeMonth: true,
		numberOfMonths: 1,
		changeYear: true
	});

});

function refresh(){
    var container = $("#refresh");
    fetch = baseurl+'ajax/tuotanto_refresh/'
    $.getJSON(fetch,function(data) {
        container.html(data.rivit);
    });
};

function ref(){
    refresh();
    window.setTimeout(function(){
        ref();
    },5000);
};

/**
 * Tallentaa uudet rivit
 * @access public
 * @return boolean false
 **/
function add(){
    fetch = baseurl+'ajax/tuotanto_save/'
    $.post(fetch,$("#tuotanto_add").serialize(),function(data) {
        if(data.ret == true){
            $("form")[0].reset();
            inform($("#feedback"),"Tallennettu");
        }else{
            inform($("#feedback"),data.msg);
        }
    },"json");
    return false;
}