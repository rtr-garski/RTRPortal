function init_order_entry() {
    var contentEl = document.getElementById('content');

    if (contentEl._orderEntryHandler) {
        contentEl.removeEventListener('click', contentEl._orderEntryHandler);
    }
    contentEl._orderEntryHandler = function (e) {
        var link = e.target.closest('.order-detail-link');
        if (!link) return;
        e.preventDefault();
        var orderId = link.dataset.orderId;
        if (!orderId) return;
        fetch('pages/order_details.php?order_id=' + encodeURIComponent(orderId), { cache: 'no-store' })
            .then(function (r) {
                if (r.status === 401) { window.location.href = 'logout.php'; return null; }
                return r.text();
            })
            .then(function (html) {
                if (!html) return;
                contentEl.innerHTML = html;
                init_order_details();
            });
    };
    contentEl.addEventListener('click', contentEl._orderEntryHandler);
}
