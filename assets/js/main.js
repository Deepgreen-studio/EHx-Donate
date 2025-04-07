(function ($) {

    if($('.edp-datatable').length) {
        new DataTable('.edp-datatable');
    }

    $(document).on("submit", "form#ehxdo_form_submit", async function (e) {
        e.preventDefault();

        // Gather form data
        let form = $(this);
        let file = form.attr("enctype");
        let submitBtn = form.find('button[type="submit"]');
        let btnLoaderEl = submitBtn.find('#ehx-loader');
        let btnTextEl = submitBtn.find('#ehx-btn-text');
        let btnLoaderCurrTxt = btnTextEl.text();
        let btnLoaderLoadTxt = submitBtn.data('submit');

        let options = {
            type: "POST",
            url: edp_object.ajax_url,
            dataType: "JSON",
        };

        if (typeof file == "undefined") {
            options.data = form.serialize();
        } else {
            options.data = new FormData(form[0]);
            options.contentType = false;
            options.enctype = file;
            options.processData = false;
        }

        $.ajax({
            ...options,
            beforeSend: () => {
                submitBtn.attr("disabled", true);
                btnLoaderEl.css("display", 'block');
                btnTextEl.text(btnLoaderLoadTxt);
            },
            success: (response) => {
                form.trigger("reset");
                edpHandleSuccess(response);
            },
            complete: () => {
                submitBtn.attr("disabled", false);
                btnLoaderEl.css("display", 'none');
                btnTextEl.text(btnLoaderCurrTxt);
            },
            error: function (e) {
                edpHandleError(e);
            },
        });
    });

    var current_fs, next_fs, previous_fs; //fieldsets
    var opacity;
    var current = 1;
    // var steps = $("fieldset").length;

    // setProgressBar(current);

    $(".edp-card").on('click', '.edp-next-btn', function (e) {
        current_fs = $(this).parent();
        next_fs = $(this).parent().next();

        let valid = validateInputFields();
        if(!valid) return valid;

        //Add Class Active
        $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("edp-progress-active");

        //show the next fieldset
        next_fs.show();
        //hide the current fieldset with style
        current_fs.animate(
            { 
                opacity: 0 
            },
            {
                step: function (now) {
                    // for making fielset appear animation
                    opacity = 1 - now;

                    current_fs.css({
                        display: "none",
                        position: "relative",
                    });
                    next_fs.css({ opacity: opacity });
                },
                duration: 500,
            }
        );

        ++current
    });

    $(".edp-card").on('click', '.edp-previous-btn', function () {
        current_fs = $(this).parent();
        previous_fs = $(this).parent().prev();

        //Remove class active
        $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("edp-progress-active");

        //show the previous fieldset
        previous_fs.show();

        //hide the current fieldset with style
        current_fs.animate(
                { opacity: 0 }, 
                {
                step: function (now) {
                    // for making fielset appear animation
                    opacity = 1 - now;

                    current_fs.css({
                        display: "none",
                        position: "relative",
                    });
                    previous_fs.css({ opacity: opacity });
                },
                duration: 500,
            }
        );

        --current
    });

    $(".edp-card").on('click', '.edp-plan-list', function (e) {
        let amount = parseFloat($(this).data('amount') || ($('.edp-plan-list.edp-plan-list-custom-input input').val() || 0));
        let service = amount * 1.4 / 100;
        let payable_amount = amount + service;

        $('.edp-plan-lists .edp-plan-list').removeClass('edp-plan-list-active');
        $(this).addClass('edp-plan-list-active');

        if(amount > 0) {
            $('#edp-donation-amounts').css('display', 'block');
            $('#edp-donation-amounts #edp_donation_amount').text(edpConvertAmount(amount));
            $('#edp-donation-amounts #edp_donation_pay').text(edpConvertAmount(payable_amount));

            $('#edp-pay-amounts').css('display', 'block');
            $('#edp-pay-amounts #edp_donation_payable_amount').text(edpConvertAmount(payable_amount));
        }

        $('.edp-card input[name="amount"]').val(amount)
    });

    $(".edp-card").on('input', '.edp-plan-list.edp-plan-list-custom-input input', function (e) {
        let amount = parseFloat(e.target.value || 0);
        let service = amount * 1.4 / 100;
        let payable_amount = amount + service;
        
        $(this).attr('placeholder', $(this).attr('area-label'));
        $(this).parent().siblings('.edp-plan-list-currency').css('display', 'inline-block');
        
        if(amount > 0) {
            $('#edp-donation-amounts').css('display', 'block');
            $('#edp-donation-amounts #edp_donation_amount').text(edpConvertAmount(amount));
            $('#edp-donation-amounts #edp_donation_pay').text(edpConvertAmount(payable_amount));

            $('#edp-pay-amounts').css('display', 'block');
            $('#edp-pay-amounts #edp_donation_payable_amount').text(edpConvertAmount(payable_amount));
        }

        $('.edp-card input[name="amount"]').val(amount)
    });

    function validateInputFields() {
        let valid = true;
        
        $('#edp__donation__message').css('display', 'none');
        $('#edp__personal__message').css('display', 'none');

        // Step 1: Validate Amount (Current Step: 1)
        if (current === 1) {
            let campaign = $('.edp-card input[name="campaign"], .edp-card select[name="campaign"]').val().trim();
            let amount = $('.edp-card input[name="amount"]').val().trim();
            if(amount <= 0 || campaign == '') {
                valid = false;
            }

            $('#edp__donation__message').toggle(!valid);
        }
        // Step 2: Validate Personal Details (Current Step: 2)
        else if (current === 2) {
            let fields = [];
    
            valid = fields.every(field => $('.edp-card input[name="' + field + '"]').val().trim() !== '');
            
            $('#edp__personal__message').toggle(!valid);

        }

        if(valid) {
            $('html, body').animate({
                scrollTop: $("#edp-card-element").offset().top
            }, 500);
        }
        return valid;
    }
    
    $(".edp-card").on('click', '.edp-modal-close', function () {
        
        $('#edp-callback-modal').removeClass('edp-modal-active')
    });

})(jQuery);
