function init_client_reports() {
    var contentEl = document.getElementById('content');

    if (contentEl._clientReportsHandler) {
        contentEl.removeEventListener('click', contentEl._clientReportsHandler);
    }
    contentEl._clientReportsHandler = function (e) {
        var link = e.target.closest('.client-order-detail-link');
        if (!link) return;
        e.preventDefault();
        var orderId = link.dataset.orderId;
        if (!orderId) return;
        fetch('pages/client_order_details.php?order_id=' + encodeURIComponent(orderId), { cache: 'no-store' })
            .then(function (r) {
                if (r.status === 401) { window.location.href = 'logout.php'; return null; }
                return r.text();
            })
            .then(function (html) {
                if (!html) return;
                contentEl.innerHTML = html;
                init_client_order_details();
            });
    };
    contentEl.addEventListener('click', contentEl._clientReportsHandler);
}
