(function($) {

    function convertAmount(number, decimals = 2, dec_point = '.', thousands_sep = ',') {
        var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        toFixedFix = function (n, prec) {
            // Fix for IE parseFloat(0.55).toFixed(0) = 0;
            var k = Math.pow(10, prec);
            return Math.round(n * k) / k;
        },
        s = (prec ? toFixedFix(n, prec) : Math.round(n)).toString().split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
    
        return `${edp_object.currency}${s.join(dec)}`;
    }

    window.edpConvertAmount = convertAmount

    /* Handle success request response */
    function handleSuccess(response, redirect = null) {
        
        if (typeof response.data?.message !== 'undefined') {
            toastrAlert(response.data?.message, response.success ? 'success' : 'error')
        }

        if ((typeof response.data?.redirect !== 'undefined' && response.data?.redirect !== null) || (typeof redirect !== 'undefined' && redirect !== null)) {
            location.replace(response.data.redirect || redirect);
        }
    }

    window.edpHandleSuccess = handleSuccess

    /* Handle error request response */
    function handleError(e, redirect = null) {
        let message = ''
        if (e.status === 0) {
            message = 'Not connected Please verify your network connection.'
        }
        else if (e.status === 200 && typeof redirect !== null && typeof redirect !== 'undefined') {
            location.replace(redirect);
        }
        else if (e.status === 404) {
            message = 'The requested data not found.'
        }
        else if (e.status === 403) {
            message = 'You are not allowed this action.'
        }
        else if (e.status === 419) {
            message = 'Nonce verify failed, please try again letter.'
        }
        else if (e.status === 500) {
            message = e.responseJSON?.data?.message || 'Internal Server Error, Please try again letter.'
        }
        else if (e === 'parsererror') {
            message = 'Requested JSON parse failed.'
        }
        else if (e === 'timeout') {
            message = 'Requested Time out.'
        }
        else if (e === 'abort') {
            message = 'Request aborted.'
        }
        else if (e.status === 422) {
            $.each(e.responseJSON.data.errors, function (index, error) {
                $("#invalid_" + index).text(error[0]);
                $("#" + index).addClass("is-invalid");
            });
            message = e.responseJSON?.data?.message
        }
        else if ([300, 301, 302, 401].includes(e.status)) {
            message = e.responseJSON?.data?.message
        }
        else {
            message = e.statusText
        }
        toastrAlert(message, 'error')

        return true;
    }
    window.edpHandleError = handleError

    function toastrAlert(message, type = 'success') {
        
        if (location.pathname == '/wp-admin/admin.php') {
            const element = $('#edp-notice');

            const alert = {
                success: 'notice-success',
                error: 'notice-error',
                info: 'notice-success',
            }[type]


            element.css('display', 'block')
            element.addClass(alert).find('p').html(message)

            setTimeout(() => element.css('display', 'none'), 10000)
        } 
        else {
            const element = $('#edp-alert-message');

            const alert = {
                success: {
                    color: 'edp-alert-primary',
                    icon: 'fa-regular fa-circle-question',
                },
                error: {
                    color: 'edp-alert-danger',
                    icon: 'fa-solid fa-triangle-exclamation',
                },
                info: {
                    color: 'edp-alert-info',
                    icon: 'fa-regular fa-circle-question',
                },
            }[type]

            element.parent().removeClass('d-none')

            element.attr('class', `edp-alert ${alert.color} text-center rounded-0`)
            element.find('i').attr('class', `${alert.icon}`)
            element.find('span').html(message)

            setTimeout(() => element.parent().addClass('d-none'), 10000)
        }
        
    }
    window.toastrAlert = toastrAlert

})(jQuery);