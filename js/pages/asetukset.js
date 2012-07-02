$(function(){
    $("#dialog-dataset").dialog({
		resizable: false,
		autoOpen: false,
		height:200,
		width: 250,
		modal: true,
		buttons: {
			"Lisää": function() {
				fetch = baseurl+'ajax/dataset_add/';
                $.post(fetch, $("#dataset_add").serialize(), function(data){
                    if(data.ret == true){
                        $( "form" )[ 0 ].reset();
                    }else{
                        alert("Ohjelman lisäys epäonnistui!\n\n"+data.ret);
                    }
                },"json");
			},
			"Sulje": function() {
				$(this).dialog( "close" );
				location.reload(true);
			}
		}
	});
	$("#dialog-instance").dialog({
		resizable: false,
		autoOpen: false,
		height:200,
		width: 250,
		modal: true,
		buttons: {
			"Lisää": function() {
				fetch = baseurl+'ajax/instance_add/';
                $.post(fetch, $("#instance_add").serialize(), function(data){
                    if(data.ret == true){
                        $( "form" )[ 0 ].reset();
                    }else{
                        alert("Ohjelman lisäys epäonnistui!\n\n"+data.ret);
                    }
                },"json");
			},
			"Sulje": function() {
				$(this).dialog( "close" );
				location.reload(true);
			}
		}
	});
});

function change_active(){
    fetch = baseurl+'ajax/dataset_change/'
    $.post(fetch,{"dataset":$("#datasetti").val()},function(data) {
        if(data.ret == true){
        }else{
            alert("Datasetin vaihto epäonnistui!");
        }
    },"json");
    return false;
}