(function ($) {

    $(document).on("submit", "form#ehx_member_form_submit", async function (e) {
        e.preventDefault();

        // Gather form data
        let form = $(this);
        let file = form.attr("enctype");
        let submitBtn = form.find('button[type="submit"]');

        let options = {
            type: "POST",
            url: ehx_object.ajax_url,
            dataType: "JSON",
        };

        if (typeof file == "undefined") {
            options.data = form.serialize();
        } else {
            options.data = new FormData(form[0]);
            options.contentType = false;
            options.enctype = file;
            options.processData = false;

            if (card) {
                let { token, error } = await stripe.createToken(card);
                if (typeof error != "undefined") {
                    return;
                }
                options.data.append("stripe_token", JSON.stringify(token));
            }
        }

        $.ajax({
            ...options,
            beforeSend: () => {
                submitBtn.attr("disabled", true);
            },
            success: (response) => {
                form.trigger("reset");
                handleSuccess(response);
            },
            complete: () => {
                submitBtn.attr("disabled", false);
            },
            error: function (e) {
                handleError(e);
            },
        });
    });

    // let cards;
    let cardElement;
    let card;
    let stripe;
    // Stripe API
    function initilizeCard() {
        stripe = Stripe($("#card_element").data("key"));
        var elements = stripe.elements();
        var style = {
            base: {
                color: "#32325d",
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: "antialiased",
                fontSize: "16px",
                padding: "16px",
                "::placeholder": {
                color: "#aab7c4",
                },
            },
            invalid: {
                color: "#fa755a",
                iconColor: "#fa755a",
            },
        };
        card = elements.create("card", { hidePostalCode: true, style: style });
        return card;
    }

    function waitForElm() {
        const element = document.querySelector("#card_element");
        return new Promise((resolve) => {
            if (element) {
                return resolve(element);
            }
            const observer = new MutationObserver((mutations) => {
                if (element) {
                observer.disconnect();
                resolve(element);
                }
            });
            observer.observe(document.body, {
                childList: true,
                subtree: true,
            });
        });
    }

    function renderStripeElement() {
        waitForElm().then((elm) => {
            cardElement = document.getElementById("card_element");
            card = initilizeCard();
            card.mount(cardElement);
        });
    }

    if ($("#card_element").length) {
        setTimeout(() => renderStripeElement(), 2000);
    }
    

    var current_fs, next_fs, previous_fs; //fieldsets
    var opacity;
    var current = 1;
    var steps = $("fieldset").length;

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

    $(".submit").click(function () {
        return false;
    });

    $(".edp-card").on('change', 'select#recurring', function (e) {
        $('.edp-plan-list .edp-plan-list-text').text(e.target.value)
    });

    $(".edp-card").on('click', '.edp-plan-list', function (e) {
        let amount = parseFloat($(this).data('amount') || ($('.edp-plan-list.edp-plan-list-custom-input input').val() || 0));
        let service = amount * 1.4 / 100;
        let payable_amount = amount + service;
        let gift_aid = payable_amount + (payable_amount * 25 / 100);

        $('.edp-plan-lists .edp-plan-list').removeClass('edp-plan-list-active');
        $(this).addClass('edp-plan-list-active');

        if(amount > 0) {
            $('#edp-donation-amounts').css('display', 'block');
            $('#edp-donation-amounts #edp_donation_amount').text(convertAmount(amount));
            $('#edp-donation-amounts #edp_donation_pay').text(convertAmount(payable_amount));

            $('#edp-pay-amounts').css('display', 'block');
            $('#edp-pay-amounts #edp_donation_payable_amount').text(convertAmount(payable_amount));
            $('#edp-pay-amounts #edp_donation_pay').text(convertAmount(gift_aid));
        }

        $('.edp-card input[name="amount"]').val(amount)
    });


    $(".edp-card").on('input', '.edp-plan-list.edp-plan-list-custom-input input', function (e) {
        let amount = parseFloat(e.target.value || 0);
        let service = amount * 1.4 / 100;
        let payable_amount = amount + service;
        let gift_aid = payable_amount + (payable_amount * 25 / 100);
        
        $(this).attr('placeholder', $(this).attr('area-label'));
        $(this).parent().siblings('.edp-plan-list-currency').css('display', 'inline-block');
        
        if(amount > 0) {
            $('#edp-donation-amounts').css('display', 'block');
            $('#edp-donation-amounts #edp_donation_amount').text(convertAmount(amount));
            $('#edp-donation-amounts #edp_donation_pay').text(convertAmount(payable_amount));

            $('#edp-pay-amounts').css('display', 'block');
            $('#edp-pay-amounts #edp_donation_payable_amount').text(convertAmount(payable_amount));
            $('#edp-pay-amounts #edp_donation_pay').text(convertAmount(gift_aid));
        }

        $('.edp-card input[name="amount"]').val(amount)
    });

    $(".edp-card").on('change', 'input#gift_aid', function (e) {
        
        if(e.target.checked) {
            $('#gift_aid_fields').css('display', 'block')
            $('#edp-pay-gift-aid').css('display', 'flex')
        }
        else {
            $('#gift_aid_fields').css('display', 'none')
            $('#edp-pay-gift-aid').css('display', 'none')
        }

    });

    function validateInputFields() {
        let valid = true;
        
        $('#edp__donation__message').css('display', 'none');
        $('#edp__personal__message').css('display', 'none');

        // Step 1: Validate Amount (Current Step: 1)
        if (current === 1) {
            let amount = $('.edp-card input[name="amount"]').val().trim();
            valid = amount > 0;
            $('#edp__donation__message').toggle(!valid);
        }
        // Step 2: Validate Personal Details (Current Step: 2)
        else if (current === 2) {
            let giftAidChecked = $('.edp-card input[name="gift_aid"]').is(':checked');
    
            let fields = giftAidChecked
                ? ['first_name', 'last_name', 'email', 'phone', 'address_line_1', 'address_line_2', 'city', 'state', 'country', 'post_code']
                : ['first_name', 'last_name', 'email', 'phone'];
    
            valid = fields.every(field => $('.edp-card input[name="' + field + '"]').val().trim() !== '');
            
            $('#edp__personal__message').toggle(!valid);

        }

        if(valid) {
            $('html, body').animate({
                scrollTop: $("#edp-card-element").offset().top
            }, 500);
        }
        
        console.log(valid);
        
        return valid;
    }
    
    

})(jQuery);
