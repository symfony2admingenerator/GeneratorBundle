// Custom scripts belonging to Admingenerator
;(function(window, $, undefined){
    //needed to make select2 formtypes function in filter modal
    $.fn.modal.Constructor.prototype.enforceFocus = function() {};
    
    var S2A = window.S2A || {};
    window.S2A = S2A;

    S2A.singleActionsManager = function(options){
        this.options = $.extend({}, {
                containerSelector: 'document',
                buttonSelector: 'a.object-action'
            },
            options
        );
        $(this.options.containerSelector).on('click',this.options.buttonSelector,this.clickHandler.bind(this));
    };

    S2A.singleActionsManager.prototype = {
        clickHandler: function(evt){
            var $elt = $(evt.currentTarget);
            if (this.isProtected($elt) && !this.needConfirmation($elt)) {
                evt.preventDefault();
                this.sendSecured($elt);
            }
        },

        isProtected: function($elt){
            return !!$elt.data('csrf-token');
        },

        needConfirmation: function($elt){
            return !!$elt.data('confirm');
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
            $form.submit();
        }
    };
    S2A.batchActionsManager = function(options){
        this.options = $.extend( {}, {
                containerSelector: 'document',
                actionSelector: 'select[name=action]',
                toggleSelector: 'input[name=batch_select_all]',
                elementSelector: 'input[name="selected[]"]',
                noActionValue: 'none',
                noElementSelectedMessage: 'You have to select at least one element.'
            },
            options
        );

        this.allElementsToggleButton = $(this.options.containerSelector).find(this.options.toggleSelector);
        this.actionInputSelector = $(this.options.containerSelector).find(this.options.actionSelector);
        $(this.options.containerSelector).find('*[type=submit]').hide();

        this.actionInputSelector.on('change', this.selectedActionChangedHandler.bind(this));
        this.allElementsToggleButton.on('change ifChecked ifUnchecked', this.allElementsChangedHandler.bind(this));
    };

    S2A.batchActionsManager.prototype = {
        selectedActionChangedHandler: function(evt){
            var $elt = $(evt.currentTarget);
            if (!this.isValidActionSelected($elt.val())) {
                return;
            }

            if (!this.hasElementsSelected()) {
                evt.preventDefault();
                $elt.val(this.options.noActionValue);
                $('#alertModal').find('.modal-title').text(this.options.noElementSelectedMessage);
                $('#alertModal').modal('show');
                return;
            }

            if (this.needConfirmation($elt)) {
                $(this.selectedOption($elt).data('confirm-modal')).modal('show', $elt);
                return;
            }

            $elt[0].form.submit();
        },

        allElementsChangedHandler: function(evt){
            var $element = $(this.options.elementSelector);
            $element.prop('checked', $(evt.currentTarget).is(':checked'));
            if(typeof($element.iCheck) == "function"){
                $element.iCheck('update');
            }
        },

        isValidActionSelected: function(actionValue){
            return actionValue != this.options.noActionValue;
        },

        hasElementsSelected: function(){
            return 0 !== $(this.options.containerSelector + ' ' + this.options.elementSelector).filter(':checked').length;
        },

        needConfirmation: function($elt){
            return !!this.selectedOption($elt).data('confirm');
        },

        selectedOption: function ($elt) {
            return $(':selected', $elt);
        }

    };

    S2A.nestedListManager = function(options){
        this.options = $.extend({}, {
                tableSelector: 'table'
            },
            options
        );

        $(this.options.tableSelector).treetable({expendable: true});
    };

    // Called once the DOM is loaded
    $(function(){
        // Initializing the iCheck dependency: should be made in AdminLTE dist app.js code...
        // However, check if it is loaded. If not, do not crash
        var checkboxes = $("input[type='checkbox']:not(.simple), input[type='radio']:not(.simple)");
        if(typeof(checkboxes.iCheck) == "function") {
            checkboxes.iCheck({
                checkboxClass: 'icheckbox_minimal',
                radioClass: 'iradio_minimal'
            });
            // We bind this such that users can use the change event
            checkboxes.on('ifChecked ifUnchecked', function () {
                $(this).trigger('change');
            });
        }

        // Force first tab to be displayed
        $('.nav-tabs *[data-toggle="tab"]:first').click();
        
        // Display number of errors on tabs
        $('.nav.nav-tabs li').each(function(i){
            $(this).find('a span.label-danger').remove();
            var invalid_items = $('fieldset'+$(this).find('a:first').data('target')).find('.has-error');
            if (invalid_items.length > 0) {
                $(this).find('a:first').append('<span class="label label-danger">'+invalid_items.length+'</span>');
            }
        });
        
        // Display object actions tooltips
        $('a.object-action').tooltip();

        // Save action for modals
        $('.object-action, .generic-action, select[name=action] option').each(function(index, item) {
            $item = $(item);
            $item.data('action', $item.attr('href'));
            $item.attr('href', $item.data('confirmModal'));
        });

        // hookup on submit button
        $('button[type=submit].generic-action').click(function(event) {
            if ($(this).data('confirm')) {
                event.preventDefault();
            }
        });

        $('.confirm-object-modal, .confirm-generic-modal').on('show.bs.modal', function (event) {
          var $elt = $(event.relatedTarget);
          var $form = $(this).find('form');
          var action = $elt.data('action');
          var confirm = $elt.data('confirm');
          var csrf_token = $elt.data('csrf-token');
          $form.attr('action', action);
          $(this).find('.modal-title').text(confirm);
          // submit button confirmation
          if ($elt.is('button[type=submit]')) {
            $form.submit(function(event) {
               event.preventDefault();
               $elt.closest('form').submit();
            });
          }
          if (csrf_token) {
            $('<input />').attr({
                    type:   'hidden',
                    name:   '_csrf_token',
                    value:  csrf_token
                }).appendTo($form);
          }
        });

        $('.confirm-batch-modal').on('show.bs.modal', function (event) {
          var $elt = $(event.relatedTarget);
          var confirm = $(':selected', $elt).data('confirm');
          $(this).find('.modal-title').text(confirm);
          $(this).find('.confirm').click(function() {
            $elt[0].form.submit();
          })
          $(this).find('.cancel').click(function() {
            $elt.val(S2A.batchActionsAdminOptions.noActionValue);
          })
        });

        // Object actions
        if (S2A.hasOwnProperty('singleActionsAdminOptions')) {
            new S2A.singleActionsManager(S2A.singleActionsAdminOptions);
        }
        // Generic actions
        if (S2A.hasOwnProperty('genericActionsAdminOptions')) {
            new S2A.singleActionsManager(S2A.genericActionsAdminOptions);
        }
        // Batch actions
        if (S2A.hasOwnProperty('batchActionsAdminOptions')) {
            new S2A.batchActionsManager(S2A.batchActionsAdminOptions);
        }
        // Nested list
        if (S2A.hasOwnProperty('nestedTreeAdminOptions')) {
            new S2A.nestedListManager(S2A.nestedTreeAdminOptions);
        }
    });
})(window, jQuery);
