$(document).ready(function () {
  $('[data-toggle="offcanvas"]').click(function () {
    $('.row-offcanvas').toggleClass('active')
  });
});

$('#consent').change(function(){
  if($(this).prop("checked")) {
    $('#ageForm').show();
  } else {
    $('#ageForm').hide();
  }
});