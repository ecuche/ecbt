$(document).ready(function () {

    // Toastr start
    if($('alert').length){
        var alerts = $('alert').toArray();
        alerts.forEach(function(alert) {
            var value = alert.innerHTML;
            var color = alert.getAttribute('color');
            if(color === 'danger'){
                color = 'error';
            }
            toastr[color](
               value,
                "Alert",
                {   positionClass: "toastr toast-top-left",
                    containerId: "toast-top-left",
                    showMethod: "fadeIn",
                    hideMethod: "fadeOut",
                    timeOut: 3000
                });
            
        });
    }
    // Toastr end

    // Hovertext start
    $('body').append('<div id="hoverdiv"></div>');
    $('#hoverdiv').css({'display':'none', 'position':'absolute', 'font-size': '14px', 'background-color': '#fff', 'color': '#404040', 'border': '1px solid #999', 'padding': '7px' });
    $(document).on('mousemove', '[hovertext]', function(e){
        var text = $(this).attr('hovertext');
        $('#hoverdiv').text(text).show();
        $('#hoverdiv').css({'top': e.clientY + 10, 'left': e.clientX + 10});
    });
    $(document).on('mouseout', '[hovertext]', function(e){
        $('#hoverdiv').hide();
    });
    // Hovertext end
    
    home_url = $('#home_url').attr('href')

    // zoomable image start
    $(document).on('click', '.zoomable-image', function () {
        const imgSrc = $(this).attr('src');
        $('#modalImage').attr('src', imgSrc);
        $('#imageModal').modal('show');
    });
    // zoomable image end


    // hide show password start
    $('.hide-show-password').css('cursor', 'pointer');
    $(document).on('click', '.hide-show-password', function (e) { 
        e.preventDefault();
        if($(this).hasClass('bi-eye-slash-fill')){
            $(this).removeClass('bi-eye-slash-fill');
            $(this).addClass('bi-eye-fill');
            $(this).siblings('#password').attr('type', 'text');;
        }else if($(this).hasClass('bi-eye-fill')){
            $(this).removeClass('bi-eye-fill');
            $(this).addClass('bi-eye-slash-fill');
            $(this).siblings('#password').attr('type', 'password');;
        }
    });
    // hide show password end

    // checkbox to input start
    $(document).on('change', '.checkbox', function(e) {
        e.preventDefault();
        if ($(this).is(':checked')) {
            $(this).siblings('#correct').val(1);
        } else {
            $(this).siblings('#correct').val(0);
        }
    });
    // checkbox to input end

    // add and remove option field start
    $(document).on('click', '.addOptionField', function (e) { 
        e.preventDefault();
        var content =   
        `<div class="row mb-1 optionField">
            <div class="col-sm-8">
                <div class="mb-3">
                    <input type="text" class="form-control" id="option" name="options[]" placeholder="Add an option to select in the question">
                </div>
            </div>
            <div class="col-sm-2 d-flex align-items-center justify-content-center">
                <div class="form-check form-switch mb-3">
                    <input type="hidden" name="corrects[]" id="correct" value="0">
                    <input class="form-check-input success checkbox" type="checkbox" id="color-success">
                </div>
            </div>
            <div class="col-sm-2">
                <div class="form-group col-sm-2 d-flex align-items-center justify-content-center">
                    <button class="btn btn-danger removeOptionField" type="button">
                        <i class="ti ti-minus"></i>
                    </button>
                </div>
            </div>
        </div>`;
        $('.answerOptions').append(content);
    });
    // add and remove option field end

    $(document).on('click', '.removeOptionField', function (e) { 
        e.preventDefault();
        $(this).closest('.optionField').remove();
    });

    $(document).on('click', '.submitAnchor', function (e) { 
        e.preventDefault();
        $(this).closest('.formSubmit').submit();
    });


    $(document).on('click', '.modalSubmit', function (e) { 
        e.preventDefault();
        $('.formSubmit').submit();
    });

    $(document).on('click', '#next, #previous', function (e) { 
        e.preventDefault();
        next =  $(this).attr('next');
        q_id =  $(this).attr('q_id');
        $.ajax({
            type: "post",
            data: {next: next, q_id: q_id},
            async: false,
            url: home_url + "/paper/test/prev-next",
            success: function (response) {
                $('.ajaxContainer').html(response);
            }
        });
    });

    $(document).on('click', '.option', function (e) { 
        ans_code = ($(this).attr('ans_code'));
        q_id =  $(this).attr('q_id');
        $.ajax({
            type: "post",
            data: {ans_code: ans_code, q_id: q_id},
            async: false,
            url: home_url + "/paper/test/submit-option-selected",
            success: function (response) {
            }
        });
    });

    $(document).on('click', '.pagi', function (e) {
        e.preventDefault(); 
        q_id =  $(this).attr('value');
        $.ajax({
            type: "post",
            data: {q_id: q_id},
            async: false,
            url: home_url + "/paper/test/prev-next",
            success: function (response) {
                $('.ajaxContainer').html(response);
            }
        });
    });


    $(document).on('click', '.view_answers', function (e) {
        if($('.view_answers').is(':checked')){
            $('.view_result').prop('checked', true);
        }
    });

    $(document).on('click', '#print-btn', function () {
        var printContents = $('#printable-area').clone(); // Clone the div
        var printWindow = window.open('', '');
        var styles = $('head').html(); // Capture all <style> and <link> tags from the current document
        var foot = $('footer').html();

        // Write content to the new print window
        printWindow.document.write('<html><head>' + styles + '</head><body>' + printContents.prop('outerHTML') + foot +'</body></html>');
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
   
    });

    $(document).on('click', '.mymodal', function (e) { 
        e.preventDefault();
        var val = $(this).attr('href');
        $('.mymodalpop').attr('href', val);
    });
});