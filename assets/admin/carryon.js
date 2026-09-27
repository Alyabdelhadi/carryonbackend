/* CarryOn admin shell: sidebar, toasts, confirmations. Needs jQuery + SweetAlert2. */
(function () {
    'use strict';

    var body = document.body;

    // sidebar on small screens
    document.addEventListener('click', function (e) {
        if (e.target.closest('[data-co-sidebar-open]')) {
            body.classList.add('co-sidebar-open');
        } else if (e.target.closest('[data-co-sidebar-close]')) {
            body.classList.remove('co-sidebar-open');
        }
        var toggle = e.target.closest('[data-co-toggle-group]');
        if (toggle) {
            var group = toggle.parentElement;
            group.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', group.classList.contains('is-open'));
        }
        var close = e.target.closest('.co-toast-close');
        if (close) hideToast(close.parentElement);
    });

    // flash messages fade out on their own
    function hideToast(toast) {
        toast.classList.add('is-hiding');
        setTimeout(function () { toast.remove(); }, 300);
    }
    document.querySelectorAll('.co-toast').forEach(function (toast) {
        setTimeout(function () { if (toast.isConnected) hideToast(toast); }, 5000);
    });

    // keep the active link in view in a long sidebar
    var active = document.querySelector('.co-nav .is-active');
    if (active && active.scrollIntoView) active.scrollIntoView({ block: 'center' });

    if (window.jQuery) {
        jQuery('[data-toggle="tooltip"]').tooltip();
    }

    // rich text editor on the fields the old pages mark with id="content"
    if (window.CKEDITOR && document.getElementById('content')) {
        CKEDITOR.replace('content');
    }

    function confirmDialog(options) {
        return Swal.fire(Object.assign({
            title: 'Are you sure?',
            text: "You won't be able to revert this.",
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
            confirmButtonClass: 'btn btn-danger',
            cancelButtonClass: 'btn btn-light ml-1',
            buttonsStyling: false,
            reverseButtons: true
        }, options || {}));
    }

    // legacy delete buttons: onclick="confirmAlert(url)"
    window.confirmAlert = function (url, text) {
        confirmDialog(text ? { text: text } : {}).then(function (result) {
            if (result.value) window.location = url;
        });
    };

    // status toggles and other one-click GET actions:
    // <a href="..." data-co-go="Change the status?">
    document.addEventListener('click', function (e) {
        var link = e.target.closest('a[data-co-go]');
        if (!link) return;
        e.preventDefault();
        confirmDialog({
            text: link.dataset.coGo,
            type: 'question',
            confirmButtonText: link.dataset.coGoLabel || 'Yes, continue',
            confirmButtonClass: 'btn btn-primary'
        }).then(function (result) {
            if (result.value) window.location = link.href;
        });
    });

    // <form data-co-confirm="Delete this admin?"> (DELETE forms of the new pages)
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.matches('[data-co-confirm]') || form.dataset.coConfirmed) return;
        e.preventDefault();
        confirmDialog({ text: form.dataset.coConfirm }).then(function (result) {
            if (result.value) {
                form.dataset.coConfirmed = '1';
                form.submit();
            }
        });
    });
})();
