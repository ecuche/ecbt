// Date Picker
jQuery(".mydatepicker, #datepicker, .input-group.date").datepicker();
jQuery("#datepicker-autoclose").datepicker({
  format: "dd-mm-yyyy",
  autoclose: true,
  todayHighlight: true,
});
jQuery("#date-range").datepicker({
  toggleActive: true,
});
jQuery("#datepicker-inline").datepicker({
  todayHighlight: true,
});
