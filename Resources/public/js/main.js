// Custom scripts belonging to Admingenerator
;(function(window, $, undefined){
    var S2A = window.S2A || {};
    window.S2A = S2A;

    S2A.actionsManager = function(container, selector){
        $(container).on('click', selector, this.clickHandler.bind(this));
    };

    S2A.actionsManager.prototype = {
        clickHandler: function(evt){
            var $elt = $(evt.target);

            if (!this.isConfirmed($elt)) {
                evt.preventDefault();
                return;
            }

            if (this.isProtected($elt)) {
                evt.preventDefault();
                this.sendSecured($elt);
            }
        },

        isConfirmed: function($elt){
            // TODO: move confirm() to custom popin
            return !$elt.data('confirm') || confirm($elt.data('confirm'))
        },

        isProtected: function($elt){
            return !!$elt.data('csrf-token');
        },

        sendSecured: function($elt){
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
            // TODO: add pre-submit trigger
            $form.submit();
            // TODO: add post-submit trigger
        }
    };
    // Force first tab to be displayed
    $('.nav-tabs *[data-toggle="tab"]:first').click();

    // Object and Generic actions
    new S2A.actionsManager('section.content', '.object-action, .generic-action');
})(window, jQuery);