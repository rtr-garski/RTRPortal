function init_client_order_details() {
    var contentEl = document.getElementById('content');

    var backBtn = document.getElementById('client-reports-back-btn');
    if (backBtn) {
        backBtn.addEventListener('click', function (e) {
            e.preventDefault();
            fetch('pages/client_reports.php', { cache: 'no-store' })
                .then(function (r) {
                    if (r.status === 401) { window.location.href = 'logout.php'; return null; }
                    return r.text();
                })
                .then(function (html) {
                    if (!html) return;
                    contentEl.innerHTML = html;
                    init_client_reports();
                });
        });
    }

    // Location trigger: show right panel with selected location details
    contentEl.querySelectorAll('.loc-trigger').forEach(function (trigger) {
        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            var self = this;

            contentEl.querySelectorAll('.recordlocationhist').forEach(function (el) {
                el.classList.remove('d-none');
            });

            document.getElementById('locSpinner').classList.remove('d-none');
            document.getElementById('locDetailContent').classList.add('d-none');

            contentEl.querySelectorAll('.loc-trigger').forEach(function (el) {
                el.classList.remove('active');
            });
            self.classList.add('active');

            setTimeout(function () {
                document.getElementById('locDetailName').textContent    = self.dataset.locName;
                document.getElementById('locDetailAddress').textContent = self.dataset.locAddress;
                document.getElementById('locDetailPhone').textContent   = self.dataset.locPhone;
                document.getElementById('locDetailFax').textContent     = self.dataset.locFax;

                var recTypeEl = document.getElementById('locDetailRecType');
                if (recTypeEl) recTypeEl.textContent = self.dataset.locRecType || '';

                var specialEl = document.getElementById('locDetailSpecial');
                if (specialEl) specialEl.textContent = self.dataset.locSpecial || '';

                document.getElementById('locSpinner').classList.add('d-none');
                document.getElementById('locDetailContent').classList.remove('d-none');
            }, 100);
        });
    });

    // Modal
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
