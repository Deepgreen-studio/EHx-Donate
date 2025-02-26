(function($) {

    $('span[title]').tooltip(); 

    // Main tab click handler
    $('.ehx-tab-wrapper').on('click', '.nav-tab', function(e) {
        e.preventDefault();

        const $clickedTab = $(this);
        const tab = $clickedTab.attr('href');
        const $targetTabPanel = $(`.tab-panel${tab}`);

        // Update main tabs
        $('.nav-tab').removeClass('nav-tab-active');
        $('.tab-panel').removeClass('tab-panel-active');

        $clickedTab.addClass('nav-tab-active');
        $targetTabPanel.addClass('tab-panel-active');
        
        if (!['#email'].includes(tab)) {
            // Handle sub-tabs within the selected main tab
            const $subTabPanels = $targetTabPanel.find('.ehx-sub-tab-content .tab-panel');
            const $subTabLinks = $targetTabPanel.find('.ehx-sub-tab-wrapper .nav-sub-tab');
            
            if ($subTabLinks) {
                // Reset sub-tabs to the first one
                $subTabPanels.removeClass('tab-panel-active').first().addClass('tab-panel-active');
                $subTabLinks.removeClass('current').first().addClass('current');

                // Update heading and description
                const $firstSubTab = $subTabLinks.first();
                
                $targetTabPanel.find('h2').text($firstSubTab.text());
                $targetTabPanel.find('p').text($firstSubTab.data('description'));
            }
            
        }
    });

    // Sub-tab click handler
    $('.ehx-main-tab-content').on('click', '.nav-sub-tab', function(e) {
        e.preventDefault();

        const $clickedSubTab = $(this);
        const $targetSubTabPanel = $(`.ehx-sub-tab-content .tab-panel${$clickedSubTab.attr('href')}`);

        // Update sub-tabs
        $clickedSubTab.closest('.ehx-sub-tab-wrapper').find('.nav-sub-tab').removeClass('current');
        $clickedSubTab.addClass('current');

        // Update sub-tab panels
        $clickedSubTab.closest('.ehx-main-tab-content').find('.ehx-sub-tab-content .tab-panel').removeClass('tab-panel-active');
        $targetSubTabPanel.addClass('tab-panel-active');

        // Update heading and description
        const $parentContent = $clickedSubTab.closest('.ehx-sub-tab-wrapper');
        
        $parentContent.siblings('h2').text($clickedSubTab.text());
        $parentContent.siblings('p').text($clickedSubTab.data('description'));
    });

    $(document).on('submit', 'form#ehx_member_form_submit', function (e) {
        e.preventDefault();

        const form     = $(this);
        const url      = form.attr('action');
        const method   = form.attr('method');
        const enctype  = form.attr('enctype');
        const redirect = form.data('redirect');
        const btn      = form.find('button[data-button="submit"]');
        const spinner  = form.find('span#submit-spinner');
        const btnText  = form.find('span#btn--text');
        
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        const options = {
            type: method || 'POST',
            url: url,
            dataType: 'JSON'
        }

        if (typeof enctype === 'undefined') {
            options.data = form.serialize();
        }
        else {
            options.data = new FormData(form[0]);
            options.contentType = false;
            options.enctype = file;
            options.processData = false;
        }

        $.ajax({
            ...options,
            beforeSend: () => {
                $(spinner).removeClass('d-none')
                $(btn).addClass('disabled')
                $(btnText).text('Please wait...')
            },
            success: (response) => {
                handleSuccess(response, redirect)
            },
            complete: () => {
                $(spinner).addClass('d-none')
                $(btn).removeClass('disabled')
                $(btnText).text($(btn).data('text'))
            },
            error: (e) => {
                handleError(e, redirect)
            }
        });
    });

    $('.ehx-admin-metabox').on('change', 'select[name="ehx_form[register_use_custom_settings]"], select[name="ehx_form[register_use_gdpr]"]', function(e) {
        e.preventDefault();
        
        let conditionedElement = $(this).parents('tr').siblings('.ehx-forms-line.ehx-forms-line-conditioned');

        if (e.target.value == 1) {
            conditionedElement.css('display', 'block')
        }
        else {
            conditionedElement.css('display', 'none')
        }
    });

    $('.ehx-admin-metabox').on('change', 'select[name="ehx_form[member_role]"]', function(e) {
        e.preventDefault();
        
        let conditionedElement = $(this).parents('tr').siblings('.ehx-forms-line.ehx-forms-line-conditioned');

        if (e.target.value == 'paid_member') {
            conditionedElement.css('display', 'block')
        }
        else {
            conditionedElement.css('display', 'none')
        }

        $('input[name="ehx_form[ehx_amount]"]').val('')
    });

    $('.ehx-admin-metabox').on('change', 'select[name="ehx_form[login_after_login]"]', function(e) {
        e.preventDefault();
        
        let conditionedElement = $(this).parents('tr').siblings('.ehx-forms-line.ehx-forms-line-conditioned');

        if (e.target.value == 'redirect_url') {
            conditionedElement.css('display', 'block')
        }
        else {
            conditionedElement.css('display', 'none')
        }
    });

    $('.ehx-member-metabox').on('change', 'input[name="ehx_content_restriction[_ehx_custom_access_settings]"]', function(e) {
        e.preventDefault();
        
        let conditionedElement = $(this).parents('tr').siblings('[data-field="ehx_content_restriction__ehx_accessible"]');

        if (e.target.checked) {
            conditionedElement.css('display', 'table-row')
        }
        else {
            $(this).parents('tr').siblings('.ehx-forms-line').css('display', 'none')
        }
    });

    $('.ehx-member-metabox').on('change', 'select[name="ehx_content_restriction[_ehx_accessible]"]', function(e) {
        e.preventDefault();
        
        let conditionedAllElement = $(this).parents('tr').siblings('.ehx-forms-line.ehx-forms-line-conditioned');
        let conditionedLoggedInElement = $(this).parents('tr').siblings('.ehx-forms-line.ehx-forms-logged-in');
        let conditionedLoggedOutElement = $(this).parents('tr').siblings('.ehx-forms-line.ehx-forms-logged-out');
        
        if (e.target.value != 0) {
            conditionedAllElement.css('display', 'table-row')
        }
        else {
            conditionedAllElement.css('display', 'none')
        }
        
        if (e.target.value == 1) {
            conditionedLoggedOutElement.css('display', 'table-row')
        }
        else {
            conditionedLoggedOutElement.css('display', 'none')
        }
        
        if (e.target.value == 2) {
            conditionedLoggedInElement.css('display', 'table-row')
        }
        else {
            conditionedLoggedInElement.css('display', 'none')

            $('input[name="ehx_content_restriction[_ehx_access_roles][]"]').attr('checked', false)
        }
    });

    $('.ehx-member-metabox').on('change', 'input[name="ehx_content_restriction[_ehx_access_hide_from_queries]"]', function(e) {
        e.preventDefault();
        
        let conditionedElement = $(this).parents('tr').siblings('[data-field="_ehx_access_hide_from_queries"]');

        if (!e.target.checked) {
            conditionedElement.css('display', 'table-row')
        }
        else {
            conditionedElement.css('display', 'none')
        }
    });

    $('.ehx-member-metabox').on('change', 'input[name="ehx_content_restriction[_ehx_noaccess_action]"]', function(e) {
        e.preventDefault();
        
        let conditionedElement = $(this).parents('tr').siblings('[data-field="_ehx_access_hide_from_queries"]');

        if (!e.target.checked) {
            conditionedElement.css('display', 'table-row')
        }
        else {
            conditionedElement.css('display', 'none')
        }
    });

    $('.ehx-admin-boxed-links').on('click', 'a', function(e) {
        e.preventDefault();

        $('.ehx-admin-boxed-links a').removeClass('ehx-admin-activebg');
        $(this).addClass('ehx-admin-activebg');

        let role = $(this).data('role');
        
        $('#form__ehx_mode').val(role);

        if (role == 'profile') {
            $('#ehx_form_options_meta_box').css('display', 'none');
        }
        else {
            $('#ehx_form_options_meta_box').css('display', 'block');
        }

        if (['profile','login'].includes(role)) {
            $('#ehx_form_membership_meta_box').css('display', 'none');
        }
        else {
            $('#ehx_form_membership_meta_box').css('display', 'block');
        }
    });

    $(document).on('click', '#openEhxModal', function(e) {
        e.preventDefault();

        let key = $(this).data('key');
        let values = $(this).data('data');
        let element = $(this).data('element');
        let action = $(this).data('action');

        console.log(values);
        

        let data = {};
        if (typeof key != 'undefined') {
            $(this).parent().siblings('.custom_field').each(function() {
                let key = $(this).data('key');
                data[key] = $(this).val();
            })
            data.key = key;
        }

        if (typeof values != 'undefined') {
            data = { ...data, ...values };
        }
        
        $.ajax({
            url: ehx_ajax_obj.ajax_url,
            type: 'POST',
            data: {
                action: action,
                security: ehx_ajax_obj.nonce,
                validated: data
            },
            success: function(response) {
                if (typeof values != 'undefined') {
                    $(element).remove();
                }
                $('body').append(response.data.html);
                $(element).addClass('normal');
                $('.ehx-admin-overlay').addClass('active');

                $('span[title]').tooltip(); 
            },
            error: function(e) {
                handleError(e)
            }
        });
    });

    $(document).on('click', '[data-action="ehx_remove_modal"], .ehx-admin-overlay', function(e) {
        e.preventDefault();
        
        $('#ehx-modal').remove();
        $('.ehx-admin-overlay').remove();
    });

    $(document).on('submit', 'form#ehx_render_input_field', function (e) {
        e.preventDefault();

        const form     = $(this);
        const method   = form.attr('method');
        const btn      = form.find('button[data-button="submit"]');
        const spinner  = form.find('span#submit-spinner');
        const btnText  = form.find('span#btn--text');
        
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        if (!form.find('input[name="edit_key"]').length) {
            let _metakey = form.find('input[name="_metakey"]');

            let isValid = true;
            $('.ehx-admin-columns .ehx-admin-drag-fld').each(function(index) {
                let value = $(this).find(`input[name="_ehx_custom_field[${index}][_metakey]"]`).val();
                if (value == _metakey.val()) {
                    $("#invalid__metakey").text('Meta key must be a unique.');
                    $("#_metakey").addClass("is-invalid");
                    
                    isValid = false;
                }
            });

            if (!isValid) return;
        }

        $.ajax({
            type: method || 'POST',
            url: ehx_ajax_obj.ajax_url,
            dataType: 'JSON',
            data: form.serialize(),
            beforeSend: () => {
                $(spinner).removeClass('d-none')
                $(btn).addClass('disabled')
                $(btnText).text('Please wait...')
            },
            success: (response) => {
                if (typeof response.data.edit_key != 'undefined' && response.data.edit_key != null) {
                    $(`.ehx-admin-columns .ehx-admin-drag-fld[data-index="${response.data.edit_key}"]`).replaceWith(response.data.html);
                } 
                else {
                    $('.ehx-admin-columns').append(response.data.html);
                }
                
                customColumnIndexKey()

                $('body').css('overflow', 'none');
                $('#ehx-modal').remove();
                $('.ehx-admin-overlay').remove();
            },
            complete: () => {
                $(spinner).addClass('d-none')
                $(btn).removeClass('disabled')
                $(btnText).text($(btn).data('text'))
            },
            error: (e) => {
                handleError(e)
            }
        });
    });

    function customColumnIndexKey() {
        $('.ehx-admin-columns .ehx-admin-drag-fld').each(function(key) {
            $(this).attr('data-index', key);
            $(this).find('#openEhxModal').attr('data-key', key);
            $(this).find('input.custom_field').each(function() {
                $(this).attr('name', `_ehx_custom_field[${key}][${$(this).data('key')}]`);
            })
        });
    }

    $('.ehx-admin-columns').on('click', '#duplicateField', function(e) {
        e.preventDefault();
        
        let element  = $(this).parent().parent().clone();
        let fldTitle = $(this).parent().siblings('.ehx-admin-drag-fld-title');
        let _metaKey = $(this).parent().siblings('input[data-key="_metakey"]');
        
        let value = `${_metaKey.val()}_1`;
        element.find('input[data-key="_metakey"]').attr('value', value).attr('data-key', value);

        element.find('input[data-key="_title"]').attr('value', `${fldTitle.text()} #1`);
        element.find('.ehx-admin-drag-fld-title').text(`${fldTitle.text()} #1`);
        
        element.find('.ehx-admin-drag-fld-type').text(value);

        $('.ehx-admin-columns').append(element);
    });

    $('.ehx-admin-columns').on('click', '#deleteField', function(e) {
        e.preventDefault();
        
        $(this).parent().parent().remove();
    });

    $('.ehx-admin-columns').on('click', '#editField', function(e) {
        e.preventDefault();
        
        $(this).parent().parent().remove();
    });

    $(document).on('click', '#predefinedFieldBtn', function(e) {
        e.preventDefault();
        
        let data = $(this).data('content');

        $.ajax({
            type: 'POST',
            url: ehx_ajax_obj.ajax_url,
            data: {
                action: 'ehx_render_input_field',
                security: ehx_ajax_obj.nonce,
                ...data
            },
            dataType: 'JSON',
            success: function (response) {

                $('.ehx-admin-columns').append(response.data.html);

                customColumnIndexKey()

                $('body').css('overflow', 'none');
                $('#ehx-modal').remove();
                $('.ehx-admin-overlay').remove();
            }
        });
    });

    let adminColumns = document.querySelector('.ehx-admin-columns');
    if (adminColumns) {
        Sortable.create(adminColumns, {
            swapClass: 'highlight', // The class applied to the hovered swap item
            animation: 150,
            onEnd: function(){
                customColumnIndexKey();
            }
        });
    }

})(jQuery);
