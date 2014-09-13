$(document).ready(function(){
    // Twitter Bootstrap hack for menus on touch device
    $('body').on('touchstart.dropdown', '.dropdown-menu', function (e) { e.stopPropagation(); });
	
    $('a[rel=tooltip]').tooltip({
        container: 'body'
    });
    
    // enable iScroll for list table
    var listTable = new IScroll('.list-table-wrapper', {
        scrollX: true,
        scrollY: false,
        mouseWheel: true,
        scrollbars: true
    });
    $(document).on('touchmove', function(e) { e.preventDefault(); }, false);

    // Moved all scripts to dedicated widgets
    // Do not remove, this file is used when developing new features
});
