<script type="text/javascript">
  $(document).ready(function() {
    $('#docdialog').dialog({
      autoOpen: false,
      height: 305,
      width: 430,
      modal: true
    });

    $('.loadDoc').live('click',function() {
      $('#docdialog').dialog('open');
      return false;
    });

    $('#docdialogtabs').tabs();

  });

</script>

<div style="align:center;"><iframe src="<?php echo url::site("/",true); ?>" style="-moz-transform: scale(0.4, 0.4); -webkit-transform: scale(0.4, 0.4); width:1050px; height:750px;padding:0;margin:-220px -312px;" ></iframe></div>