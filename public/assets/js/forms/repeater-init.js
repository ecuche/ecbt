$(function () {
  "use strict";

  // Default
  $(".repeater-default").repeater();

  // Custom Show / Hide Configurations
  $(".file-repeater, .email-repeater").repeater({
    show: function () {
      $(this).slideDown();
    },
    hide: function (remove) {
      if (confirm("Are you sure you want to remove this item?")) {
        $(this).slideUp(remove);
      }
    },
  });
});

var room = 1;

function education_fields() {
  room++;
  var objTo = document.getElementById("education_fields");
  var divtest = document.createElement("div");
  divtest.setAttribute("class", "mb-1 removeclass" + room);
  var rdiv = "removeclass" + room;
  divtest.innerHTML =
  '<div class="row"><div class="col-sm-8"><div class="mb-3"><input type="text" class="form-control" id="option"  name="options[]" placeholder="Add an option to select in the question"></div></div><div class="col-sm-2 d-flex align-items-center justify-content-center"><div class="form-check form-switch mb-3"><input type="hidden" name="corrects[]" id="correct" value="0"><input class="form-check-input success checkbox" type="checkbox" id="color-success"></div></div><div class="col-sm-2"><div class="form-group col-sm-2 d-flex align-items-center justify-content-center"><button class="btn btn-danger" type="button" onclick="remove_education_fields(' + room + ');"><i class="ti ti-minus"></i></button></div></div></div>';

  objTo.appendChild(divtest);
}

function remove_education_fields(rid) {
  $(".removeclass" + rid).remove();
}
