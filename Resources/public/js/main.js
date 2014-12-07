// Custom scripts belonging to Admingenerator
;(function(window, $, undefined){
    // Force first tab to be displayed
    $('.nav-tabs *[data-toggle="tab"]:first').click();

    // Object actions handler
    $('section.content').on('click', 'a.object-action', function(evt){
        var $elt = $(this);

        // TODO: move this to custom popin
        if ($elt.data('confirm') && !confirm($elt.data('confirm'))) {
            evt.preventDefault();
            return;
        }

        if ($elt.data('csrf-token')) {
            evt.preventDefault();
            // Transform in POST request
            var $form = $('<form />').attr({
                method: 'POST',
                action: $elt.attr('href'),
                style:  'visibility: hidden'
            }).appendTo($('body'));
            // Add CSRF protection token
            $('<input />').attr({
                type:   'hidden',
                name:   '_csrf_token',
                value:  $elt.data('csrf-token')
            }).appendTo($form);
            $form.submit();
        }
    });
})(window, jQuery);