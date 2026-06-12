/**
 * Enterprise Incubator System — Main JS
 * ES6+ | No dependencies beyond Bootstrap 5 & Chart.js
 */

'use strict';

// ═══ GLOBALS ════════════════════════════════════════════════
const App = {
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',
    appUrl:    window.APP_URL || '',

    // ─── AJAX helper ────────────────────────────────────────
    async fetch(url, options = {}) {
        const defaults = {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token':     this.csrfToken,
                'Accept':           'application/json',
            }
        };
        if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
            options.body = JSON.stringify(options.body);
            defaults.headers['Content-Type'] = 'application/json';
        }
        const resp = await fetch(url, { ...defaults, ...options, headers: { ...defaults.headers, ...(options.headers || {}) } });
        const data = await resp.json();
        if (!resp.ok || data.success === false) throw new Error(data.message || 'Request failed.');
        return data;
    },

    // ─── Toast ──────────────────────────────────────────────
    toast(message, type = 'success', duration = 4000) {
        let container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.style.cssText = 'position:fixed;top:70px;right:1rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem;';
            document.body.appendChild(container);
        }

        const icons = { success: 'check-circle-fill', danger: 'x-circle-fill', warning: 'exclamation-triangle-fill', info: 'info-circle-fill' };
        const div = document.createElement('div');
        div.className = `toast align-items-center text-bg-${type} border-0 show shadow-sm`;
        div.style.cssText = 'min-width:280px;max-width:380px;animation:fadeIn .25s ease';
        div.innerHTML = `
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi bi-${icons[type] || 'info-circle-fill'}"></i>
                    <span>${message}</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('.toast').remove()"></button>
            </div>`;
        container.appendChild(div);

        if (duration) setTimeout(() => div.remove(), duration);
        return div;
    },

    // ─── Confirm dialog ──────────────────────────────────────
    async confirm(message, title = 'Are you sure?') {
        return new Promise(resolve => {
            const id = 'confirmModal_' + Date.now();
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.id = id;
            modal.innerHTML = `
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h6 class="modal-title fw-bold">${title}</h6>
                    </div>
                    <div class="modal-body pt-1 text-muted" style="font-size:.875rem">${message}</div>
                    <div class="modal-footer border-0 pt-0">
                        <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-sm btn-danger" id="${id}_ok">Confirm</button>
                    </div>
                </div>
            </div>`;
            document.body.appendChild(modal);
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
            modal.querySelector(`#${id}_ok`).addEventListener('click', () => { bsModal.hide(); resolve(true); });
            modal.addEventListener('hidden.bs.modal', () => { modal.remove(); resolve(false); });
        });
    },

    // ─── Format currency ────────────────────────────────────
    currency(val, symbol = '$') {
        return symbol + parseFloat(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    // ─── Format number ───────────────────────────────────────
    number(val, decimals = 0) {
        return parseFloat(val || 0).toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
    },
};

// ═══ SIDEBAR ════════════════════════════════════════════════
const Sidebar = {
    init() {
        const sidebar        = document.getElementById('sidebar');
        const overlay        = document.getElementById('sidebarOverlay');
        const toggleBtn      = document.getElementById('sidebarToggle');       // mobile
        const collapseBtn    = document.getElementById('sidebarCollapseBtn');  // desktop

        if (localStorage.getItem('sidebarCollapsed') === '1') {
            document.body.classList.add('sidebar-collapsed');
        }

        toggleBtn?.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        });

        overlay?.addEventListener('click', () => {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });

        collapseBtn?.addEventListener('click', () => {
            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', document.body.classList.contains('sidebar-collapsed') ? '1' : '0');
        });
    }
};

// ═══ CLOCK ══════════════════════════════════════════════════
const Clock = {
    init() {
        const el   = document.getElementById('liveClock');
        const foot = document.getElementById('footerTime');
        const tick = () => {
            const now = new Date().toLocaleString('en-ZW', { timeZone: 'Africa/Harare', hour12: false });
            if (el)   el.textContent = now;
            if (foot) foot.textContent = now;
        };
        tick();
        setInterval(tick, 1000);
    }
};

// ═══ NOTIFICATIONS ══════════════════════════════════════════
const Notifications = {
    init() {
        const btn  = document.getElementById('notifBtn');
        if (!btn) return;

        // Load on dropdown open
        btn.closest('.dropdown')?.addEventListener('show.bs.dropdown', () => this.load());

        document.getElementById('markAllReadBtn')?.addEventListener('click', async () => {
            await App.fetch(App.appUrl + '/notifications/read-all', { method: 'POST' });
            this.load();
            this.updateBadge(0);
        });

        // Poll every 30s
        this.pollBadge();
        setInterval(() => this.pollBadge(), 30000);
    },

    async load() {
        const list = document.getElementById('notifList');
        if (!list) return;
        try {
            const res = await App.fetch(App.appUrl + '/notifications/unread');
            this.updateBadge(res.data.count);
            if (!res.data.items.length) {
                list.innerHTML = '<div class="text-center text-muted py-4 small">No new notifications</div>';
                return;
            }
            const icons = { warning: 'exclamation-triangle text-warning', danger: 'x-circle text-danger',
                            success: 'check-circle text-success', info: 'info-circle text-info' };
            list.innerHTML = res.data.items.map(n => `
                <div class="notif-item ${n.is_read ? '' : 'unread'}" data-id="${n.id}" onclick="Notifications.markRead(${n.id}, this)">
                    <div class="d-flex gap-2 align-items-start">
                        <i class="bi bi-${icons[n.severity] || 'bell'} mt-1 flex-shrink-0"></i>
                        <div>
                            <div class="fw-semibold">${n.title}</div>
                            <div class="text-muted">${n.message}</div>
                            <div class="text-muted" style="font-size:.72rem">${n.created_at}</div>
                        </div>
                    </div>
                </div>`).join('');
        } catch(e) {}
    },

    async markRead(id, el) {
        el?.classList.remove('unread');
        await App.fetch(App.appUrl + `/notifications/${id}/read`, { method: 'POST' });
    },

    async pollBadge() {
        try {
            const res = await App.fetch(App.appUrl + '/notifications/unread');
            this.updateBadge(res.data.count);
        } catch(e) {}
    },

    updateBadge(count) {
        const badge = document.getElementById('notifBadge');
        if (!badge) return;
        badge.textContent = count > 9 ? '9+' : count;
        badge.classList.toggle('d-none', count < 1);
    }
};

// ═══ SSE LIVE FEED ═══════════════════════════════════════════
const LiveFeed = {
    source: null,

    start(onData) {
        if (!window.EventSource) return; // Fallback to polling

        this.source = new EventSource(App.appUrl + '/dashboard/activity-stream');

        this.source.onmessage = (e) => {
            try {
                const data = JSON.parse(e.data);
                if (typeof onData === 'function') onData(data);
            } catch(err) {}
        };

        this.source.onerror = () => {
            this.source.close();
            // Retry after 15s
            setTimeout(() => this.start(onData), 15000);
        };
    },

    stop() {
        this.source?.close();
        this.source = null;
    }
};

// ═══ CHART HELPERS ══════════════════════════════════════════
const Charts = {
    defaults: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8 } },
            tooltip: { mode: 'index', intersect: false }
        }
    },

    palette: ['#2563eb','#16a34a','#d97706','#dc2626','#7c3aed','#0891b2','#ea580c'],

    lineChart(ctx, labels, datasets) {
        return new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: datasets.map((d, i) => ({
                    borderColor:     this.palette[i % this.palette.length],
                    backgroundColor: this.palette[i % this.palette.length] + '20',
                    fill: true, tension: .35, pointRadius: 3,
                    ...d
                }))
            },
            options: { ...this.defaults, scales: { y: { beginAtZero: true, ticks: { callback: v => '$' + App.number(v) } } } }
        });
    },

    barChart(ctx, labels, datasets) {
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: datasets.map((d, i) => ({
                    backgroundColor: this.palette[i % this.palette.length] + 'cc',
                    borderRadius: 4,
                    ...d
                }))
            },
            options: { ...this.defaults, scales: { y: { beginAtZero: true } } }
        });
    },

    doughnut(ctx, labels, data) {
        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{ data, backgroundColor: this.palette, borderWidth: 0 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '65%',
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } } }
            }
        });
    }
};

// ═══ FORM HELPERS ═══════════════════════════════════════════
const Forms = {
    // Serialize form to object
    serialize(form) {
        const data = {};
        new FormData(form).forEach((v, k) => { data[k] = v; });
        return data;
    },

    // Show validation errors next to fields
    showErrors(form, errors) {
        // Clear old errors
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

        Object.entries(errors || {}).forEach(([field, msgs]) => {
            const el = form.querySelector(`[name="${field}"]`);
            if (el) {
                el.classList.add('is-invalid');
                const fb = document.createElement('div');
                fb.className = 'invalid-feedback';
                fb.textContent = Array.isArray(msgs) ? msgs[0] : msgs;
                el.parentNode.appendChild(fb);
            }
        });
    },

    clearErrors(form) {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    }
};

// ═══ DATA TABLES (lightweight sortable) ═════════════════════
const DataTable = {
    init(tableId) {
        const table = document.getElementById(tableId);
        if (!table) return;

        table.querySelectorAll('th[data-sort]').forEach(th => {
            th.style.cursor = 'pointer';
            th.addEventListener('click', () => {
                const col  = th.dataset.sort;
                const asc  = th.dataset.order !== 'asc';
                th.dataset.order = asc ? 'asc' : 'desc';

                const tbody = table.querySelector('tbody');
                const rows  = Array.from(tbody.querySelectorAll('tr'));

                rows.sort((a, b) => {
                    const aVal = a.querySelector(`td:nth-child(${th.cellIndex + 1})`)?.textContent.trim() || '';
                    const bVal = b.querySelector(`td:nth-child(${th.cellIndex + 1})`)?.textContent.trim() || '';
                    const n = parseFloat(aVal.replace(/[^0-9.-]/g,''));
                    return isNaN(n)
                        ? (asc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal))
                        : (asc ? (parseFloat(aVal) - parseFloat(bVal)) : (parseFloat(bVal) - parseFloat(aVal)));
                });

                rows.forEach(r => tbody.appendChild(r));
            });
        });
    }
};

// ═══ INIT ════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
    Sidebar.init();
    Clock.init();
    Notifications.init();

    // Auto-dismiss alerts after 6s
    document.querySelectorAll('.alert-dismissible').forEach(el => {
        setTimeout(() => {
            if (el.isConnected) bootstrap.Alert.getOrCreateInstance(el)?.close();
        }, 6000);
    });

    // AJAX form submissions via data-ajax="true"
    document.querySelectorAll('form[data-ajax]').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = form.querySelector('[type=submit]');
            const origText = btn?.innerHTML;
            if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing…'; }
            Forms.clearErrors(form);

            try {
                const res = await App.fetch(form.action, { method: form.method || 'POST', body: new FormData(form) });
                App.toast(res.message || 'Success', 'success');
                if (form.dataset.redirect) window.location = form.dataset.redirect;
                if (form.dataset.reload) window.location.reload();
                if (typeof window.onFormSuccess === 'function') window.onFormSuccess(res, form);
            } catch(err) {
                App.toast(err.message || 'An error occurred.', 'danger');
                if (btn) { btn.disabled = false; btn.innerHTML = origText; }
            }
        });
    });

    // Confirm delete
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', async (e) => {
            e.preventDefault();
            const ok = await App.confirm(el.dataset.confirm, el.dataset.confirmTitle || 'Confirm');
            if (ok) {
                if (el.tagName === 'A') window.location = el.href;
                else if (el.closest('form')) el.closest('form').submit();
                else if (el.dataset.action) {
                    try {
                        await App.fetch(el.dataset.action, { method: 'POST' });
                        App.toast('Done.', 'success');
                        window.location.reload();
                    } catch(err) {
                        App.toast(err.message, 'danger');
                    }
                }
            }
        });
    });
});
