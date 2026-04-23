function init_order_details() {
    var contentEl = document.getElementById('content');

    var backBtn = document.getElementById('order-entry-back-btn');
    if (backBtn) {
        backBtn.addEventListener('click', function (e) {
            e.preventDefault();
            fetch('pages/order_entry.php', { cache: 'no-store' })
                .then(function (r) {
                    if (r.status === 401) { window.location.href = 'logout.php'; return null; }
                    return r.text();
                })
                .then(function (html) {
                    if (!html) return;
                    contentEl.innerHTML = html;
                    init_order_entry();
                });
        });
    }

    var modalEl = document.getElementById('changeInfoModal');
    if (!modalEl) return;

    var modal = new bootstrap.Modal(modalEl);
    var activeInput = null;

    contentEl.querySelectorAll('input.change-info-input.is-invalid').forEach(function (input) {
        input.style.cursor = 'pointer';
        input.addEventListener('click', function () {
            activeInput = input;
            document.getElementById('changeInfoSubmitted').textContent = input.value || '(empty)';
            document.getElementById('changeInfoSelect').value = '';
            document.getElementById('selectedCarrierInfo').style.display = 'none';

            var address = input.dataset.address || '';
            var phone   = input.dataset.phone   || '';
            var fax     = input.dataset.fax     || '';

            var addrRow    = document.getElementById('changeInfoAddress');
            var contactRow = document.getElementById('changeInfoContact');

            if (address) {
                document.getElementById('changeInfoAddressText').textContent = address;
                addrRow.style.display = '';
            } else {
                addrRow.style.display = 'none';
            }

            if (phone || fax) {
                document.getElementById('changeInfoPhone').textContent = phone || '—';
                document.getElementById('changeInfoFax').textContent   = fax   || '—';
                contactRow.style.display = '';
            } else {
                contactRow.style.display = 'none';
            }

            modal.show();
        });
    });

    document.getElementById('changeInfoSelect').addEventListener('change', function () {
        var opt = this.options[this.selectedIndex];
        var info = document.getElementById('selectedCarrierInfo');
        if (opt && opt.dataset.address) {
            document.getElementById('selectedCarrierAddress').textContent = opt.dataset.address;
            document.getElementById('selectedCarrierPhone').textContent   = opt.dataset.phone || '—';
            document.getElementById('selectedCarrierFax').textContent     = opt.dataset.fax   || '—';
            info.style.display = '';
        } else {
            info.style.display = 'none';
        }
    });

    document.getElementById('changeInfoSave').addEventListener('click', function () {
        var selected = document.getElementById('changeInfoSelect').value;
        if (activeInput && selected) {
            activeInput.value = selected;
            activeInput.classList.remove('is-invalid');
            activeInput.classList.add('is-valid');
            activeInput.style.cursor = '';
        }
        modal.hide();
    });
}
