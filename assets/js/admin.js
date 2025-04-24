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

    $('.edp-main-tab-content').on('change', 'input[data-dependable]', function(e) {

        let siblings = $(this).parents('tr').siblings('[data-depend_field="'+ e.target.name +'"]');
        
        if (e.target.checked) {
            siblings.addClass('edp-disabled-content');
        }
        else {
            siblings.removeClass('edp-disabled-content');
        }
        
    });

    $(document).on('submit', 'form#edp_donate_form_submit', function (e) {
        e.preventDefault();

        submitForm($(this));
    });

    function submitForm(form) {
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
            url: url || ehxdo_object.ajax_url,
            dataType: 'JSON'
        }

        if (typeof enctype === 'undefined') {
            options.data = form.serialize();
        }
        else {
            options.data = new FormData(form[0]);
            options.contentType = false;
            options.enctype = enctype;
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
    }

    var frame;
    $('.edp-admin-metabox').on('click', '#upload-image', function(e) {
        e.preventDefault();

        let btn = $(this);
        let title = btn.data('title');
        let button = btn.data('button');

        if (frame) {
            frame.open();
            return;
        }
        
        frame = wp.media({
            title: title,
            button: { text: button },
            multiple: false
        });
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            
            btn.siblings('input').val(attachment.id);
            btn.siblings('img').attr('src', attachment.url).show();
            btn.siblings('#remove-image').show();
        });
        frame.open();
    });

    $('.edp-admin-metabox').on('click', '#remove-image', function() {
        $(this).siblings('input').val('');
        $(this).siblings('img').hide();
        $(this).hide();
    });

    // Addons
    // Search functionality
    $('#edp-addons-search').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.edp-addon-card').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(searchTerm) > -1);
        });
    });

    // Filter tabs
    $('.edp-addons-wrap .filter-links').on('click', 'a', function(e) {
        e.preventDefault();
        const filter = $(this).attr('href').substring(1);
        $('.edp-addon-card').show();
        
        if (filter !== 'all') {
            $('.edp-addon-card').not('.' + filter).hide();
        }
        
        $('.filter-links a').removeClass('current');
        $(this).addClass('current');
    });

    // Filter tabs
    $('.edp-addons-wrap').on('click', '#manageAddon', function(e) {
        e.preventDefault();
        
        let type = $(this).data('type');
        let addon = $(this).data('addon');

        if(type == 'delete') {
            conf = confirm('Are you sure you want to delete this plugin?')
            if(!conf) return;
        }

        let form = $(this).parents('form#edp-addon-form');

        form.attr('data-redirect', location.href);
        form.find('input[name="type"]').val(type);
        form.find('input[name="slug"]').val(addon);

        submitForm(form);
        
    });

})(jQuery);
