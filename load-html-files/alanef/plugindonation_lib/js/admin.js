function openPDLTab(evt, tabName) {
    evt.preventDefault();
    // Declare all variables
    var i, tabcontent, tablinks;

    // Get all elements with class="tabcontent" and hide them
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    // Get all elements with class="tablinks" and remove the class "active"
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    // Show the current tab, and add an "active" class to the button that opened the tab
    document.getElementById(tabName).style.display = "flex";
    evt.currentTarget.className += " active";
}


(function ($) {
    'use strict';
    $(function () {
        $(document).on('click', '.pdl_notice .notice-dismiss, .pdl_notice .dismiss', function (event) {
            let data = {
                action: 'pdl_dismiss_notice',
                id: $(this).closest('div').attr('id'),
                nonce: pdl_notice.nonce
            };

            $.post(pdl_notice.ajaxurl, data, function (response) {
                console.log(response, 'DONE!');
                location.reload();
            });
        });
        $(document).on('click', '.pdl_notice .remind', function (event) {
            let data = {
                action: 'pdl_later_notice',
                id: $(this).closest('div').attr('id'),
                nonce: pdl_notice.nonce
            };

            $.post(pdl_notice.ajaxurl, data, function (response) {
                console.log(response, 'DONE!');
                location.reload();
            });
        });
    });
})(jQuery);
