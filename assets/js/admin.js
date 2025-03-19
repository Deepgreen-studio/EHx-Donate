(function($) {
    $('span[title]').tooltip(); 

    // Main tab click handler
    $('.edp-tab-wrapper').on('click', '.nav-tab', function(e) {
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
            const $subTabPanels = $targetTabPanel.find('.edp-sub-tab-content .tab-panel');
            const $subTabLinks = $targetTabPanel.find('.edp-sub-tab-wrapper .nav-sub-tab');
            
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
    $('.edp-main-tab-content').on('click', '.nav-sub-tab', function(e) {
        e.preventDefault();

        const $clickedSubTab = $(this);
        const $targetSubTabPanel = $(`.edp-sub-tab-content .tab-panel${$clickedSubTab.attr('href')}`);

        // Update sub-tabs
        $clickedSubTab.closest('.edp-sub-tab-wrapper').find('.nav-sub-tab').removeClass('current');
        $clickedSubTab.addClass('current');

        // Update sub-tab panels
        $clickedSubTab.closest('.edp-main-tab-content').find('.edp-sub-tab-content .tab-panel').removeClass('tab-panel-active');
        $targetSubTabPanel.addClass('tab-panel-active');

        // Update heading and description
        const $parentContent = $clickedSubTab.closest('.edp-sub-tab-wrapper');
        
        $parentContent.siblings('h2').text($clickedSubTab.text());
        $parentContent.siblings('p').text($clickedSubTab.data('description'));
    });

    $(document).on('submit', 'form#edp_donate_form_submit', function (e) {
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
                console.log(response);
                
                edpHandleSuccess(response, redirect)
            },
            complete: () => {
                $(spinner).addClass('d-none')
                $(btn).removeClass('disabled')
                $(btnText).text($(btn).data('text'))
            },
            error: (e) => {
                edpHandleError(e, redirect)
            }
        });
    });

})(jQuery);
