document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('mobile-sidebar');
    const backdrop = document.getElementById('mobile-sidebar-backdrop');
    const openBtn = document.getElementById('open-sidebar-btn');
    const closeBtn = document.getElementById('close-sidebar-btn');

    const toggleSidebar = (open) => {
        if (!sidebar || !backdrop) {
            return;
        }
        sidebar.classList.toggle('-translate-x-full', !open);
        backdrop.classList.toggle('hidden', !open);
    };

    openBtn?.addEventListener('click', () => toggleSidebar(true));
    closeBtn?.addEventListener('click', () => toggleSidebar(false));
    backdrop?.addEventListener('click', () => toggleSidebar(false));

    document.querySelectorAll('[data-demo-email]').forEach((button) => {
        button.addEventListener('click', () => {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            if (emailInput && passwordInput) {
                emailInput.value = button.getAttribute('data-demo-email') || '';
                passwordInput.value = 'password';
                emailInput.focus();
            }
        });
    });

    const sourceBucketSelect = document.getElementById('source_bucket');
    if (sourceBucketSelect) {
        const syncTransferFields = () => {
            const option = sourceBucketSelect.options[sourceBucketSelect.selectedIndex];
            if (!option) {
                return;
            }
            const fieldMap = {
                tf_product_id: option.dataset.product || '',
                tf_lot_id: option.dataset.lot || '',
                tf_owner_id: option.dataset.owner || '',
                tf_warehouse_id: option.dataset.warehouse || '',
                tf_from_rack_id: option.dataset.rack || '',
                tf_status: option.dataset.status || '',
            };
            Object.entries(fieldMap).forEach(([id, value]) => {
                const input = document.getElementById(id);
                if (input) {
                    input.value = value;
                }
            });
        };

        sourceBucketSelect.addEventListener('change', syncTransferFields);
        syncTransferFields();
    }
});
