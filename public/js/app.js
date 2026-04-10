(function() {
    'use strict';

    // Configuration depuis la vue Blade
    var CFG = window.SYSTICKET || {};
    var BASE  = CFG.baseUrl || '';
    var API   = CFG.apiUrl  || (BASE + '/api');
    var CSRF  = CFG.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // Pagination
    var ITEMS_PER_PAGE = 15;
    var _pagination = {};
    var _boundElements = new WeakSet();

    function paginateData(items, key) {
        if (!_pagination[key]) _pagination[key] = { page: 1 };
        var page = _pagination[key].page || 1;
        var total = items.length;
        var totalPages = Math.max(1, Math.ceil(total / ITEMS_PER_PAGE));
        if (page > totalPages) page = totalPages;
        _pagination[key].page = page;
        var start = (page - 1) * ITEMS_PER_PAGE;
        return {
            items: items.slice(start, start + ITEMS_PER_PAGE),
            total: total,
            page: page,
            totalPages: totalPages
        };
    }

    function updatePaginationUI(containerId, key, loadFn) {
        var container = document.getElementById(containerId);
        if (!container) return;
        var totalPages = parseInt(container.getAttribute('data-total-pages') || '1');
        var page = (_pagination[key] && _pagination[key].page) || 1;
        if (totalPages <= 1) { container.innerHTML = ''; return; }
        var html = '';
        html += '<button class="btn btn-text btn-small" data-page="' + Math.max(1, page - 1) + '"' + (page <= 1 ? ' disabled' : '') + '>&laquo;</button>';
        for (var i = 1; i <= totalPages; i++) {
            html += '<button class="btn btn-small' + (i === page ? ' btn-primary' : ' btn-text') + '" data-page="' + i + '">' + i + '</button>';
        }
        html += '<button class="btn btn-text btn-small" data-page="' + Math.min(totalPages, page + 1) + '"' + (page >= totalPages ? ' disabled' : '') + '>&raquo;</button>';
        container.innerHTML = html;
        container.querySelectorAll('button[data-page]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var p = parseInt(this.getAttribute('data-page'));
                if (!_pagination[key]) _pagination[key] = {};
                _pagination[key].page = p;
                loadFn();
            });
        });
    }

    // ===================== FETCH API =====================

    function apiUrl(endpoint) {
        return API + '/' + endpoint;
    }

    function appUrl(path) {
        return BASE + '/' + path;
    }

    function fetchApi(endpoint, options) {
        options = options || {};
        options.headers = options.headers || {};
        options.headers['Content-Type'] = options.headers['Content-Type'] || 'application/json';
        options.headers['Accept'] = 'application/json';
        options.headers['X-CSRF-TOKEN'] = CSRF;
        options.credentials = 'same-origin';

        return fetch(apiUrl(endpoint), options).then(function(resp) {
            if (!resp.ok) {
                return resp.json().then(function(err) {
                    var msg = err.message || err.error || 'Erreur serveur';
                    if (err.errors) {
                        var errs = [];
                        Object.keys(err.errors).forEach(function(k) {
                            errs.push(err.errors[k].join(', '));
                        });
                        msg = errs.join(' | ');
                    }
                    throw new Error(msg);
                }).catch(function(e) {
                    if (e instanceof Error && e.message !== 'Erreur serveur') throw e;
                    throw new Error('Erreur ' + resp.status);
                });
            }
            return resp.json();
        });
    }

    function unwrap(resp) {
        if (resp && typeof resp === 'object' && 'success' in resp) {
            if (!resp.success) throw new Error(resp.message || 'Erreur');
            return resp.data;
        }
        if (resp && typeof resp === 'object' && 'data' in resp) {
            return resp.data;
        }
        return resp;
    }

    function apiGet(endpoint) {
        return fetchApi(endpoint, { method: 'GET' }).then(unwrap);
    }

    function apiPost(endpoint, data) {
        data = data || {};
        return fetchApi(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    function apiPut(endpoint, data) {
        data = data || {};
        return fetchApi(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    function apiDelete(endpoint, data) {
        return fetchApi(endpoint, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data || {})
        });
    }

    // ===================== UTILITAIRES =====================

    function esc(str) {
        if (str === null || str === undefined) return '';
        var div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }

    function formatDate(dateStr) {
        if (!dateStr) return '—';
        var d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function formatHours(h) {
        if (h === null || h === undefined) return '—';
        return parseFloat(h).toFixed(1) + 'h';
    }

    function applyDynamicRoles() {
        var role = (CFG.user && CFG.user.role) || CFG.role || 'client';
        document.querySelectorAll('.role-admin-only').forEach(function(el) {
            el.style.display = (role !== 'admin') ? 'none' : '';
        });
        document.querySelectorAll('.role-admin-collaborateur').forEach(function(el) {
            el.style.display = (role !== 'admin' && role !== 'collaborateur') ? 'none' : '';
        });
        document.querySelectorAll('.role-client-only').forEach(function(el) {
            el.style.display = (role !== 'client') ? 'none' : '';
        });
    }

    function bindOnce(el, event, handler) {
        if (!el || _boundElements.has(el)) return;
        _boundElements.add(el);
        el.addEventListener(event, handler);
    }

    function clearSelectOptions(sel) {
        if (!sel) return;
        while (sel.options.length > 1) {
            sel.remove(1);
        }
    }

    function mapFormData(entity, data) {
        var maps = {
            ticket: {},
            projet: {},
            contrat: {},
            user: {},
            temps: {
                'date_saisie': 'date',
                'heures': 'hours'
            }
        };
        var map = maps[entity] || {};
        var mapped = {};
        Object.keys(data).forEach(function(key) {
            mapped[map[key] || key] = data[key];
        });
        return mapped;
    }

    // ===================== BADGES =====================

    function statusBadge(status) {
        var map = {
            'new':            '<span class="badge badge-info">Nouveau</span>',
            'in-progress':    '<span class="badge badge-warning">En cours</span>',
            'waiting-client': '<span class="badge badge-secondary">En attente client</span>',
            'done':           '<span class="badge badge-success">Terminé</span>',
            'to-validate':    '<span class="badge badge-primary">À valider</span>',
            'validated':      '<span class="badge badge-success">Validé</span>',
            'refused':        '<span class="badge badge-danger">Refusé</span>'
        };
        return map[status] || '<span class="badge">' + esc(status) + '</span>';
    }

    function statusLabel(status) {
        var map = {
            'new': 'Nouveau', 'in-progress': 'En cours', 'waiting-client': 'En attente client',
            'done': 'Terminé', 'to-validate': 'À valider', 'validated': 'Validé', 'refused': 'Refusé'
        };
        return map[status] || status;
    }

    function priorityBadge(priority) {
        var map = {
            'low':      '<span class="badge badge-info">Faible</span>',
            'normal':   '<span class="badge">Normale</span>',
            'high':     '<span class="badge badge-warning">Élevée</span>',
            'critical': '<span class="badge badge-danger">Critique</span>'
        };
        return map[priority] || '<span class="badge">' + esc(priority) + '</span>';
    }

    function typeBadge(type) {
        return type === 'billable'
            ? '<span class="badge badge-warning">Facturable</span>'
            : '<span class="badge badge-success">Inclus</span>';
    }

    function projetStatusBadge(status) {
        var map = {
            'active':    '<span class="badge badge-success">Actif</span>',
            'paused':    '<span class="badge badge-warning">En pause</span>',
            'completed': '<span class="badge badge-secondary">Terminé</span>'
        };
        return map[status] || '<span class="badge">' + esc(status) + '</span>';
    }

    // ===================== MESSAGES FORMULAIRE =====================

    function showFormMessage(formEl, msg, type) {
        var msgEl = formEl.querySelector('.form-messages');
        if (!msgEl) return;
        msgEl.innerHTML = '<div class="alert alert-' + (type || 'danger') + '">' + esc(msg) + '</div>';
        msgEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function clearFormMessage(formEl) {
        var msgEl = formEl.querySelector('.form-messages');
        if (msgEl) msgEl.innerHTML = '';
    }

    // ===================== PAGE: DASHBOARD =====================

    function initDashboard() {
        if (document.body.getAttribute('data-page') !== 'dashboard') return;

        apiGet('dashboard/stats').then(function(data) {
            if (!data) return;
            setTextById('dash-tickets-open', data.tickets_open || 0);
            setTextById('dash-projets-active', data.projets_active || 0);
            setTextById('dash-validation-count', data.to_validate || 0);
            setTextById('dash-hours-month', formatHours(data.hours_month || 0));

            var gaugeEl = document.getElementById('dash-hours-gauge');
            if (gaugeEl) {
                var budget = parseFloat(data.hours_budget) || 0;
                var consumed = parseFloat(data.hours_consumed) || 0;
                var remaining = Math.max(0, budget - consumed);
                var pct = budget > 0 ? Math.round((consumed / budget) * 100) : 0;
                var gaugeValue = gaugeEl.querySelector('.dashboard-gauge-value');
                if (gaugeValue) gaugeValue.innerHTML = formatHours(consumed) + ' <span class="text-secondary">/ ' + formatHours(budget) + '</span>';
                var progressFill = gaugeEl.querySelector('.progress-fill');
                if (progressFill) progressFill.style.width = Math.min(pct, 100) + '%';
                var progressText = gaugeEl.querySelector('.progress-text');
                if (progressText) progressText.textContent = pct + '% consommé — ' + formatHours(remaining) + ' restantes';
            }
        }).catch(function() {});

        apiGet('dashboard/charts').then(function(data) {
            if (!data) return;

            // Tickets by status
            var container = document.getElementById('dash-tickets-by-status');
            if (container && data.tickets_by_status && data.tickets_by_status.length) {
                var items = data.tickets_by_status;
                var total = items.reduce(function(s, d) { return s + parseInt(d.count || 0); }, 0);
                var colors = ['dashboard-chart-bar-blue', 'dashboard-chart-bar-primary', 'dashboard-chart-bar-green', 'dashboard-chart-bar-amber', 'dashboard-chart-bar-gray'];
                var html = '<div class="dashboard-chart-bars">';
                items.forEach(function(item, i) {
                    var pct = total > 0 ? Math.round((item.count / total) * 100) : 0;
                    html += '<div class="dashboard-chart-row">';
                    html += '<span class="dashboard-chart-label">' + esc(statusLabel(item.status)) + '</span>';
                    html += '<div class="dashboard-chart-bar-wrap">';
                    html += '<div class="dashboard-chart-bar ' + (colors[i % colors.length]) + '" style="width: ' + pct + '%;"></div>';
                    html += '</div>';
                    html += '<span class="dashboard-chart-value">' + item.count + '</span>';
                    html += '</div>';
                });
                html += '</div>';
                container.innerHTML = html;
            }

            // Hours by project
            var containerHP = document.getElementById('dash-hours-by-project');
            if (containerHP && data.hours_by_project && data.hours_by_project.length) {
                var hpItems = data.hours_by_project;
                var hpTotal = hpItems.reduce(function(s, d) { return s + parseFloat(d.total || 0); }, 0);
                var hpMax = Math.max.apply(null, hpItems.map(function(d) { return parseFloat(d.total || 0); }));
                var hpColors = ['dashboard-chart-bar-primary', 'dashboard-chart-bar-blue', 'dashboard-chart-bar-green'];
                var hpHtml = '<div class="dashboard-chart-bars">';
                hpItems.forEach(function(item, i) {
                    var pct = hpMax > 0 ? Math.round((parseFloat(item.total || 0) / hpMax) * 100) : 0;
                    hpHtml += '<div class="dashboard-chart-row">';
                    hpHtml += '<span class="dashboard-chart-label">' + esc(item.name) + '</span>';
                    hpHtml += '<div class="dashboard-chart-bar-wrap">';
                    hpHtml += '<div class="dashboard-chart-bar ' + (hpColors[i % hpColors.length]) + '" style="width: ' + pct + '%;"></div>';
                    hpHtml += '</div>';
                    hpHtml += '<span class="dashboard-chart-value">' + formatHours(item.total) + '</span>';
                    hpHtml += '</div>';
                });
                hpHtml += '</div>';
                hpHtml += '<p class="dashboard-chart-total"><strong>Total : ' + formatHours(hpTotal) + '</strong></p>';
                containerHP.innerHTML = hpHtml;
            }
        }).catch(function() {});

        apiGet('dashboard/recent').then(function(data) {
            if (!data) return;

            // Recent tickets
            var tbody = document.getElementById('dash-recent-tickets');
            if (tbody && data.recent_tickets && data.recent_tickets.length) {
                var html = '';
                data.recent_tickets.forEach(function(t, i) {
                    html += '<tr data-status="' + esc(t.status) + '" data-row-index="' + i + '">';
                    html += '<td><a href="' + appUrl('tickets/' + t.id) + '">#' + t.id + '</a></td>';
                    html += '<td>' + esc(t.title) + '</td>';
                    html += '<td>' + statusBadge(t.status) + '</td>';
                    html += '<td>' + formatDate(t.created_at) + '</td>';
                    html += '</tr>';
                });
                tbody.innerHTML = html;
                applyDynamicRoles();
            }

            // Recent activity
            var activityContainer = document.getElementById('dash-recent-activity');
            if (activityContainer && data.recent_activity && data.recent_activity.length) {
                var aHtml = '<ul class="dashboard-activity" aria-label="Dernières activités">';
                data.recent_activity.forEach(function(a) {
                    var icon = a.type === 'temps' ? '⏱️' : '🎫';
                    var desc = a.type === 'temps'
                        ? (a.user_name || '') + ' — ' + formatHours(a.hours) + ' sur ' + (a.project_name || '')
                        : (a.user_name || '') + ' — ' + (a.label || '');
                    aHtml += '<li class="dashboard-activity-item">';
                    aHtml += '<span class="dashboard-activity-icon" aria-hidden="true">' + icon + '</span>';
                    aHtml += '<div class="dashboard-activity-content">';
                    aHtml += '<p>' + esc(desc) + '</p>';
                    aHtml += '<time class="dashboard-activity-time">' + formatDate(a.activity_date) + '</time>';
                    aHtml += '</div></li>';
                });
                aHtml += '</ul>';
                activityContainer.innerHTML = aHtml;
            }

            // Featured projects
            var projContainer = document.getElementById('dash-featured-projects');
            if (projContainer && data.featured_projects && data.featured_projects.length) {
                var pHtml = '<ul class="dashboard-projects">';
                data.featured_projects.forEach(function(p) {
                    pHtml += '<li class="dashboard-project-item">';
                    pHtml += '<div class="dashboard-project-info">';
                    pHtml += '<span class="dashboard-project-name"><a href="' + appUrl('projets/' + p.id) + '">' + esc(p.name) + '</a></span>';
                    pHtml += '<span class="dashboard-project-meta">' + esc(p.client_name || '') + ' · ' + (p.tickets_count || 0) + ' tickets</span>';
                    pHtml += '</div></li>';
                });
                pHtml += '</ul>';
                projContainer.innerHTML = pHtml;
            }
        }).catch(function() {});
    }

    // ===================== PAGE: TICKETS =====================

    function initTicketsList() {
        if (document.body.getAttribute('data-page') !== 'tickets') return;
        if (document.getElementById('ticket-form')) return;

        var sel = document.getElementById('filter-project');
        clearSelectOptions(sel);
        apiGet('projets').then(function(data) {
            if (sel && data && data.length) {
                data.forEach(function(p) {
                    var opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = p.name;
                    sel.appendChild(opt);
                });
            }
        }).catch(function() {});

        loadTickets();

        if (CFG.user && CFG.user.role === 'client') {
            loadClientValidationCards();
        }

        function loadTicketsResetPage() {
            _pagination['tickets'] = { page: 1 };
            loadTickets();
        }

        var search = document.getElementById('ticket-search');
        bindOnce(search, 'input', debounce(loadTicketsResetPage, 300));
        ['filter-status', 'filter-priority', 'filter-type', 'filter-project'].forEach(function(id) {
            var el = document.getElementById(id);
            bindOnce(el, 'change', loadTicketsResetPage);
        });
    }

    function loadTickets() {
        var params = [];
        var search = document.getElementById('ticket-search');
        if (search && search.value) params.push('search=' + encodeURIComponent(search.value));
        var status = document.getElementById('filter-status');
        if (status && status.value) params.push('status=' + encodeURIComponent(status.value));
        var priority = document.getElementById('filter-priority');
        if (priority && priority.value) params.push('priority=' + encodeURIComponent(priority.value));
        var type = document.getElementById('filter-type');
        if (type && type.value) params.push('type=' + encodeURIComponent(type.value));
        var project = document.getElementById('filter-project');
        if (project && project.value) params.push('project_id=' + encodeURIComponent(project.value));

        var url = 'tickets' + (params.length ? '?' + params.join('&') : '');

        apiGet(url).then(function(tickets) {
            var tbody = document.getElementById('tickets-tbody');
            var countEl = document.getElementById('tickets-count');
            if (!tbody) return;

            if (!tickets || !tickets.length) {
                tbody.innerHTML = '<tr class="table-empty table-empty-row"><td colspan="11">Aucun ticket pour le moment.</td></tr>';
                if (countEl) countEl.textContent = '0';
                var pg = document.getElementById('tickets-pagination');
                if (pg) pg.setAttribute('data-total-pages', '1');
                updatePaginationUI('tickets-pagination', 'tickets', loadTickets);
                return;
            }

            var paged = paginateData(tickets, 'tickets');
            if (countEl) countEl.textContent = paged.total;

            var html = '';
            paged.items.forEach(function(t, index) {
                html += '<tr class="ticket-row" data-status="' + esc(t.status) + '" data-priority="' + esc(t.priority) + '" data-type="' + esc(t.type) + '" data-project_id="' + (t.project_id || '') + '" data-row-index="' + index + '">';
                html += '<td><a href="' + appUrl('tickets/' + t.id) + '">#' + t.id + '</a></td>';
                html += '<td><a href="' + appUrl('tickets/' + t.id) + '">' + esc(t.title) + '</a></td>';
                html += '<td>' + esc(t.project_name || '—') + '</td>';
                html += '<td>' + esc(t.client_name || '—') + '</td>';
                html += '<td>' + statusBadge(t.status) + '</td>';
                html += '<td>' + priorityBadge(t.priority) + '</td>';
                html += '<td>' + typeBadge(t.type) + '</td>';
                html += '<td>' + esc(t.assignee_names || '—') + '</td>';
                html += '<td>' + formatHours(t.spent_hours) + '</td>';
                html += '<td>' + formatDate(t.created_at) + '</td>';
                html += '<td>';
                html += '<a href="' + appUrl('tickets/' + t.id) + '" class="btn btn-text btn-small">Voir</a>';
                html += '<a href="' + appUrl('tickets/' + t.id + '/edit') + '" class="btn btn-text btn-small role-admin-collaborateur">Éditer</a>';
                html += '</td>';
                html += '</tr>';
            });
            tbody.innerHTML = html;
            applyDynamicRoles();

            var pg = document.getElementById('tickets-pagination');
            if (pg) pg.setAttribute('data-total-pages', paged.totalPages);
            updatePaginationUI('tickets-pagination', 'tickets', loadTickets);
        }).catch(function() {
            var tbody = document.getElementById('tickets-tbody');
            if (tbody) tbody.innerHTML = '<tr><td colspan="11" class="table-empty">Erreur de chargement.</td></tr>';
        });
    }

    // ===================== PAGE: TICKET DETAIL =====================

    function initTicketDetail() {
        if (typeof window.TICKET_ID === 'undefined' || !window.TICKET_ID) return;

        var id = window.TICKET_ID;
        var allCollaborateurs = [];
        var currentAssignees = [];
        var userRole = (CFG.user && CFG.user.role) || CFG.role || 'client';
        var canManageAssignees = (userRole === 'admin' || userRole === 'collaborateur');

        function renderAssignees(assignees) {
            currentAssignees = assignees || [];
            var container = document.getElementById('ticket-assignees');
            if (!container) return;
            if (!currentAssignees.length) {
                container.innerHTML = '<div class="assignee-item"><span class="text-secondary">Aucun assigné</span></div>';
            } else {
                var ahtml = '';
                currentAssignees.forEach(function(u) {
                    ahtml += '<div class="assignee-item">'
                        + '<span>' + esc(u.first_name + ' ' + u.last_name) + '</span>';
                    if (canManageAssignees) {
                        ahtml += '<button type="button" class="btn-danger-icon btn-remove-assignee" data-user-id="' + u.id + '" title="Retirer">✕</button>';
                    }
                    ahtml += '</div>';
                });
                container.innerHTML = ahtml;
            }
            updateAssigneeSelect();
            bindRemoveAssigneeButtons();
        }

        function updateAssigneeSelect() {
            var sel = document.getElementById('add-assignee-select');
            if (!sel) return;
            var assignedIds = currentAssignees.map(function(u) { return u.id; });
            var html = '<option value="">+ Ajouter un collaborateur</option>';
            allCollaborateurs.forEach(function(c) {
                if (assignedIds.indexOf(c.id) === -1) {
                    html += '<option value="' + c.id + '">' + esc(c.first_name + ' ' + c.last_name) + '</option>';
                }
            });
            sel.innerHTML = html;
        }

        function bindRemoveAssigneeButtons() {
            var container = document.getElementById('ticket-assignees');
            if (!container) return;
            container.querySelectorAll('.btn-remove-assignee').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var userId = parseInt(this.getAttribute('data-user-id'));
                    if (!userId) return;
                    apiDelete('tickets/' + id + '/assignees', { user_id: userId })
                        .then(function(resp) {
                            var data = resp && resp.data ? resp.data : [];
                            renderAssignees(data);
                        })
                        .catch(function(err) { alert(err.message || 'Erreur'); });
                });
            });
        }

        apiGet('users/collaborateurs').then(function(collabs) {
            allCollaborateurs = collabs || [];
        }).catch(function() {});

        apiGet('tickets/' + id).then(function(t) {
            if (!t) return;
            setTextById('breadcrumb-ticket', '#' + t.id + ' - ' + (t.title || ''));
            setTextById('ticket-title', t.title);
            setHtmlById('ticket-id', '#' + t.id);
            setHtmlById('ticket-status-badge', statusBadge(t.status));
            setHtmlById('ticket-priority-badge', priorityBadge(t.priority));
            setHtmlById('ticket-type-badge', typeBadge(t.type));
            setHtmlById('ticket-description', '<p>' + esc(t.description || '—') + '</p>');
            setTextById('ticket-project', t.project_name || '—');
            setTextById('ticket-client', t.client_name || '—');
            setTextById('ticket-created', formatDate(t.created_at));
            setTextById('ticket-updated', formatDate(t.updated_at));
            setTextById('ticket-author', t.creator_name || '—');
            setTextById('ticket-time-spent', formatHours(t.spent_hours || 0));
            setTextById('ticket-time-est', formatHours(t.estimated_hours || 0));
            setHtmlById('ticket-time-estimated', '<strong>Temps estimé :</strong> ' + formatHours(t.estimated_hours || 0));
            document.title = 'Ticket #' + t.id + ' - Systicket';

            renderAssignees(t.assignees || []);

            if (t.comments) renderComments(t.comments);
            if (t.time_entries) renderTimeEntries(t.time_entries);
        }).catch(function() {});

        var addSel = document.getElementById('add-assignee-select');
        if (addSel) {
            addSel.addEventListener('change', function() {
                var userId = parseInt(this.value);
                if (!userId) return;
                this.value = '';
                apiPost('tickets/' + id + '/assignees', { user_id: userId })
                    .then(function(resp) {
                        var data = resp && resp.data ? resp.data : [];
                        renderAssignees(data);
                    })
                    .catch(function(err) { alert(err.message || 'Erreur'); });
            });
        }

        var commentForm = document.getElementById('comment-form');
        if (commentForm) {
            commentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                var textarea = commentForm.querySelector('textarea');
                var contenu = textarea ? textarea.value.trim() : '';
                if (!contenu) return;
                apiPost('tickets/' + id + '/comments', { content: contenu })
                    .then(function() {
                        if (textarea) textarea.value = '';
                        apiGet('tickets/' + id + '/comments').then(function(data) {
                            renderComments(data || []);
                        });
                    })
                    .catch(function(err) { alert(err.message); });
            });
        }

        var statusSelect = document.getElementById('ticket-status-change');
        if (statusSelect) {
            statusSelect.addEventListener('change', function() {
                var newStatus = this.value;
                if (!newStatus) return;
                apiPut('tickets/' + id, { status: newStatus })
                    .then(function() { location.reload(); })
                    .catch(function(err) { alert(err.message); });
            });
        }
    }

    function renderComments(comments) {
        var container = document.getElementById('ticket-comments');
        if (!container) return;
        if (!comments || !comments.length) {
            container.innerHTML = '<p class="text-secondary text-sm">Aucun commentaire.</p>';
            return;
        }
        var html = '';
        comments.forEach(function(c) {
            html += '<div class="comment">';
            html += '<div class="comment-header">';
            html += '<strong>' + esc(c.author_name || 'Utilisateur') + '</strong>';
            html += '<time>' + formatDate(c.created_at) + '</time>';
            html += '</div>';
            html += '<p>' + esc(c.content) + '</p>';
            html += '</div>';
        });
        container.innerHTML = html;
    }

    function renderTimeEntries(entries) {
        var container = document.getElementById('ticket-time-entries');
        if (!container) return;
        if (!entries || !entries.length) {
            container.innerHTML = '<p class="text-secondary text-sm">Aucune entrée de temps.</p>';
            return;
        }
        var total = entries.reduce(function(s, e) { return s + parseFloat(e.hours || 0); }, 0);
        setTextById('ticket-time-total', 'Total : ' + formatHours(total));
        var html = '';
        entries.forEach(function(e) {
            html += '<div class="time-entry">';
            html += '<span class="time-entry-date">' + formatDate(e.date) + '</span>';
            html += '<span class="time-entry-user">' + esc(e.user_name || '') + '</span>';
            html += '<span class="time-entry-hours">' + formatHours(e.hours) + '</span>';
            html += '<span class="time-entry-desc">' + esc(e.description || '') + '</span>';
            html += '</div>';
        });
        container.innerHTML = html;
    }

    // ===================== PAGE: TICKET FORM =====================

    function initTicketForm() {
        var form = document.getElementById('ticket-form');
        if (!form) return;

        var entityId = parseInt(form.getAttribute('data-id') || 0);
        var duplicateId = parseInt(form.getAttribute('data-duplicate') || 0);

        // En mode édition ou duplication, charger les données du ticket
        var loadId = entityId || duplicateId;
        if (loadId) {
            apiGet('tickets/' + loadId).then(function(t) {
                if (!t) return;
                setFieldValue('title', t.title);
                setFieldValue('description', t.description);
                setFieldValue('priority', t.priority);
                setFieldValue('estimated_hours', t.estimated_hours);
                setFieldValue('status', t.status);
                setFieldValue('project_id', t.project_id);
                setFieldValue('type', t.type);
                // Cocher les assignés
                if (t.assignees && t.assignees.length) {
                    var assigneeIds = t.assignees.map(function(a) { return a.id; });
                    form.querySelectorAll('input[name="assignees[]"]').forEach(function(cb) {
                        cb.checked = assigneeIds.indexOf(parseInt(cb.value)) !== -1;
                    });
                }
            });
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            clearFormMessage(form);

            var formData = new FormData(form);
            var data = {};
            formData.forEach(function(val, key) {
                if (key === 'assignees[]' || key === '_token') {
                    // Récupérés séparément
                } else {
                    data[key] = val;
                }
            });

            // Collecter les assignés depuis les checkboxes
            var assignees = [];
            form.querySelectorAll('input[name="assignees[]"]:checked').forEach(function(cb) {
                assignees.push(parseInt(cb.value));
            });
            data.assignees = assignees;

            data = mapFormData('ticket', data);

            var promise;
            if (entityId) {
                promise = apiPut('tickets/' + entityId, data);
            } else {
                promise = apiPost('tickets', data);
            }

            promise.then(function(result) {
                var newId = result.id || entityId;
                window.location.href = appUrl('tickets/' + newId);
            }).catch(function(err) {
                showFormMessage(form, err.message);
            });
        });
    }

    // ===================== PAGE: PROJETS =====================

    function initProjetsList() {
        if (document.body.getAttribute('data-page') !== 'projets') return;
        if (document.getElementById('projet-form')) return;

        var sel = document.getElementById('filter-client');
        clearSelectOptions(sel);
        apiGet('users/clients').then(function(data) {
            if (sel && data) {
                var clients = Array.isArray(data) ? data : [];
                clients.forEach(function(c) {
                    var opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.first_name + ' ' + c.last_name;
                    sel.appendChild(opt);
                });
            }
        }).catch(function() {});

        loadProjets();

        function loadProjetsResetPage() {
            _pagination['projets'] = { page: 1 };
            loadProjets();
        }

        var search = document.getElementById('projet-search');
        bindOnce(search, 'input', debounce(loadProjetsResetPage, 300));
        ['filter-status', 'filter-client'].forEach(function(id) {
            var el = document.getElementById(id);
            bindOnce(el, 'change', loadProjetsResetPage);
        });
    }

    function loadProjets() {
        var params = [];
        var search = document.getElementById('projet-search');
        if (search && search.value) params.push('search=' + encodeURIComponent(search.value));
        var status = document.getElementById('filter-status');
        if (status && status.value) params.push('status=' + encodeURIComponent(status.value));
        var client = document.getElementById('filter-client');
        if (client && client.value) params.push('client_id=' + encodeURIComponent(client.value));

        var url = 'projets' + (params.length ? '?' + params.join('&') : '');

        apiGet(url).then(function(projets) {
            var tbody = document.getElementById('projets-tbody');
            var countEl = document.getElementById('projets-count');
            if (!tbody) return;

            if (!projets || !projets.length) {
                tbody.innerHTML = '<tr class="table-empty-row"><td colspan="7">Aucun projet pour le moment.</td></tr>';
                if (countEl) countEl.textContent = '0';
                setTextById('projets-active', 0);
                setTextById('projets-paused', 0);
                setTextById('projets-completed', 0);
                var pg = document.getElementById('projets-pagination');
                if (pg) pg.setAttribute('data-total-pages', '1');
                updatePaginationUI('projets-pagination', 'projets', loadProjets);
                return;
            }

            var active = 0, paused = 0, completed = 0;
            projets.forEach(function(p) {
                if (p.status === 'active') active++;
                else if (p.status === 'paused') paused++;
                else if (p.status === 'completed') completed++;
            });

            var paged = paginateData(projets, 'projets');
            if (countEl) countEl.textContent = paged.total;

            var html = '';
            paged.items.forEach(function(p, index) {
                html += '<tr class="project-row" data-status="' + esc(p.status) + '" data-client="' + (p.client_id || '') + '" data-row-index="' + index + '">';
                html += '<td><a href="' + appUrl('projets/' + p.id) + '">' + esc(p.name) + '</a></td>';
                html += '<td>' + esc(p.client_name || '—') + '</td>';
                html += '<td>' + projetStatusBadge(p.status) + '</td>';
                html += '<td>' + (p.tickets_count || 0) + '</td>';
                html += '<td>' + formatHours(p.total_hours || 0) + '</td>';
                html += '<td>';
                var progPct = 0;
                if (p.contract_hours && parseFloat(p.contract_hours) > 0) {
                    progPct = Math.round((parseFloat(p.total_hours) || 0) / parseFloat(p.contract_hours) * 100);
                }
                var progColor = progPct > 100 ? ' progress-fill-danger' : (progPct > 80 ? ' progress-fill-warning' : '');
                html += '<div class="progress-bar"><div class="progress-fill' + progColor + '" style="width: ' + Math.min(progPct, 100) + '%;"></div></div>';
                html += '<span class="progress-text-small">' + progPct + '%</span>';
                html += '</td>';
                html += '<td>';
                html += '<a href="' + appUrl('projets/' + p.id) + '" class="btn btn-text btn-small">Voir</a>';
                html += '<a href="' + appUrl('projets/' + p.id + '/edit') + '" class="btn btn-text btn-small role-admin-collaborateur">Éditer</a>';
                html += '</td>';
                html += '</tr>';
            });
            tbody.innerHTML = html;
            applyDynamicRoles();

            setTextById('projets-active', active);
            setTextById('projets-paused', paused);
            setTextById('projets-completed', completed);

            var pg = document.getElementById('projets-pagination');
            if (pg) pg.setAttribute('data-total-pages', paged.totalPages);
            updatePaginationUI('projets-pagination', 'projets', loadProjets);
        }).catch(function() {
            var tbody = document.getElementById('projets-tbody');
            if (tbody) tbody.innerHTML = '<tr><td colspan="7" class="table-empty">Erreur de chargement.</td></tr>';
        });
    }

    // ===================== PAGE: PROJET DETAIL =====================

    function initProjetDetail() {
        if (typeof window.PROJET_ID === 'undefined' || !window.PROJET_ID) return;
        var id = window.PROJET_ID;
        var allCollabsProjet = [];
        var currentProjetAssignees = [];
        var userRolePD = (CFG.user && CFG.user.role) || CFG.role || 'client';
        var canManagePD = (userRolePD === 'admin' || userRolePD === 'collaborateur');

        function renderProjetAssignees(assignees) {
            currentProjetAssignees = assignees || [];
            var container = document.getElementById('projet-assignees');
            if (!container) return;
            if (!currentProjetAssignees.length) {
                container.innerHTML = '<div class="assignee-item"><span class="text-secondary">Aucun collaborateur assigné</span></div>';
            } else {
                var ahtml = '';
                currentProjetAssignees.forEach(function(u) {
                    ahtml += '<div class="assignee-item"><span>' + esc(u.first_name + ' ' + u.last_name) + '</span>';
                    if (canManagePD) {
                        ahtml += '<button type="button" class="btn-danger-icon btn-remove-projet-assignee" data-user-id="' + u.id + '" title="Retirer">✕</button>';
                    }
                    ahtml += '</div>';
                });
                container.innerHTML = ahtml;
            }
            updateProjetAssigneeSelect();
            bindRemoveProjetAssigneeButtons();
        }

        function updateProjetAssigneeSelect() {
            var sel = document.getElementById('add-projet-assignee-select');
            if (!sel) return;
            var assignedIds = currentProjetAssignees.map(function(u) { return u.id; });
            var html = '<option value="">+ Ajouter un collaborateur</option>';
            allCollabsProjet.forEach(function(c) {
                if (assignedIds.indexOf(c.id) === -1) {
                    html += '<option value="' + c.id + '">' + esc(c.first_name + ' ' + c.last_name) + '</option>';
                }
            });
            sel.innerHTML = html;
        }

        function bindRemoveProjetAssigneeButtons() {
            var container = document.getElementById('projet-assignees');
            if (!container) return;
            container.querySelectorAll('.btn-remove-projet-assignee').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var userId = parseInt(this.getAttribute('data-user-id'));
                    if (!userId) return;
                    apiDelete('projets/' + id + '/assignees', { user_id: userId })
                        .then(function(resp) {
                            renderProjetAssignees(resp && resp.data ? resp.data : []);
                        })
                        .catch(function(err) { alert(err.message || 'Erreur'); });
                });
            });
        }

        apiGet('users/collaborateurs').then(function(collabs) {
            allCollabsProjet = collabs || [];
        }).catch(function() {});

        var addSelPD = document.getElementById('add-projet-assignee-select');
        if (addSelPD) {
            addSelPD.addEventListener('change', function() {
                var userId = parseInt(this.value);
                if (!userId) return;
                this.value = '';
                apiPost('projets/' + id + '/assignees', { user_id: userId })
                    .then(function(resp) {
                        renderProjetAssignees(resp && resp.data ? resp.data : []);
                    })
                    .catch(function(err) { alert(err.message || 'Erreur'); });
            });
        }

        apiGet('projets/' + id).then(function(p) {
            if (!p) return;
            setTextById('breadcrumb-projet', p.name);
            setTextById('projet-name', p.name);
            setHtmlById('projet-status-badge', projetStatusBadge(p.status));
            setTextById('projet-client-name', 'Client : ' + (p.client_name || '—'));
            setTextById('projet-description', p.description || '—');
            setTextById('projet-start', formatDate(p.start_date));
            setTextById('projet-end', formatDate(p.end_date));
            setTextById('projet-manager', p.manager_name || '—');
            setTextById('projet-created', formatDate(p.created_at));
            document.title = (p.name || 'Projet') + ' - Systicket';

            renderProjetAssignees(p.assignees || []);

            if (p.tickets && p.tickets.length) {
                var tbody = document.getElementById('projet-tickets-tbody');
                if (tbody) {
                    var thtml = '';
                    p.tickets.forEach(function(t, idx) {
                        thtml += '<tr data-status="' + esc(t.status) + '" data-priority="' + esc(t.priority) + '" data-type="' + esc(t.type) + '" data-row-index="' + idx + '">';
                        thtml += '<td><a href="' + appUrl('tickets/' + t.id) + '">#' + t.id + '</a></td>';
                        thtml += '<td>' + esc(t.title) + '</td>';
                        thtml += '<td>' + statusBadge(t.status) + '</td>';
                        thtml += '<td>' + priorityBadge(t.priority) + '</td>';
                        thtml += '<td>' + typeBadge(t.type) + '</td>';
                        thtml += '<td>—</td>';
                        thtml += '<td>' + formatHours(t.spent_hours) + '</td>';
                        thtml += '<td><a href="' + appUrl('tickets/' + t.id) + '" class="btn btn-text btn-small">Voir</a></td>';
                        thtml += '</tr>';
                    });
                    tbody.innerHTML = thtml;
                    applyDynamicRoles();
                }
            } else {
                var tbody2 = document.getElementById('projet-tickets-tbody');
                if (tbody2) tbody2.innerHTML = '<tr><td colspan="8" class="table-empty">Aucun ticket pour ce projet.</td></tr>';
            }

            if (p.contrat) {
                var c = p.contrat;
                setTextById('projet-contrat-hours', formatHours(c.hours));
                setTextById('projet-contrat-used', formatHours(c.consumed_hours || 0));
                var remaining = (parseFloat(c.hours) || 0) - (parseFloat(c.consumed_hours) || 0);
                setTextById('projet-contrat-remaining', formatHours(Math.max(0, remaining)));
                setTextById('projet-contrat-rate', (c.rate || 0) + ' €/h');
                var consumed = parseFloat(c.consumed_hours) || 0;
                var contractH = parseFloat(c.hours) || 0;
                var rate = parseFloat(c.rate) || 0;
                var supplementary = Math.max(0, consumed - contractH);
                var amount = supplementary * rate;
                var amountText = amount.toFixed(2) + ' €';
                if (supplementary > 0) {
                    amountText += ' (' + formatHours(supplementary) + ' suppl.)';
                } else {
                    amountText = '0.00 € (dans l\'enveloppe)';
                }
                setTextById('projet-contrat-amount', amountText);
                setTextById('projet-contrat-period', formatDate(c.start_date) + ' — ' + formatDate(c.end_date));
                var pct = c.hours > 0 ? Math.round((parseFloat(c.consumed_hours) || 0) / c.hours * 100) : 0;
                var bar = document.getElementById('projet-contrat-progress');
                if (bar) bar.style.width = Math.min(pct, 100) + '%';
                setTextById('projet-contrat-progress-text', pct + '% consommé');
            }
        }).catch(function() {});
    }

    // ===================== PAGE: PROJET FORM =====================

    function initProjetForm() {
        var form = document.getElementById('projet-form');
        if (!form) return;

        var entityId = parseInt(form.getAttribute('data-id') || 0);

        // En mode édition, charger les données du projet
        if (entityId) {
            apiGet('projets/' + entityId).then(function(p) {
                if (!p) return;
                setFieldValue('name', p.name);
                setFieldValue('description', p.description);
                setFieldValue('start_date', p.start_date);
                setFieldValue('end_date', p.end_date);
                setFieldValue('client_id', p.client_id);
                setFieldValue('status', p.status);
                setFieldValue('manager_id', p.manager_id);
                // Cocher les membres assignés
                if (p.assignees && p.assignees.length) {
                    var assigneeIds = p.assignees.map(function(a) { return a.id; });
                    form.querySelectorAll('input[name="assignees[]"]').forEach(function(cb) {
                        cb.checked = assigneeIds.indexOf(parseInt(cb.value)) !== -1;
                    });
                }
            });
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            clearFormMessage(form);

            var formData = new FormData(form);
            var data = {};
            formData.forEach(function(val, key) {
                if (key === 'assignees[]' || key === '_token') {
                    // Récupérés séparément
                } else {
                    data[key] = val;
                }
            });

            // Collecter les membres depuis les checkboxes
            var assignees = [];
            form.querySelectorAll('input[name="assignees[]"]:checked').forEach(function(cb) {
                assignees.push(parseInt(cb.value));
            });
            data.assignees = assignees;

            data = mapFormData('projet', data);

            var promise = entityId
                ? apiPut('projets/' + entityId, data)
                : apiPost('projets', data);

            promise.then(function(result) {
                window.location.href = appUrl('projets/' + (result.id || entityId));
            }).catch(function(err) {
                showFormMessage(form, err.message);
            });
        });
    }

    // ===================== PAGE: CONTRATS =====================

    function initContratsList() {
        if (document.body.getAttribute('data-page') !== 'contrats') return;
        if (document.getElementById('contrat-form')) return;

        loadContrats();

        function loadContratsResetPage() {
            _pagination['contrats'] = { page: 1 };
            loadContrats();
        }

        var search = document.getElementById('contrat-search');
        bindOnce(search, 'input', debounce(loadContratsResetPage, 300));
        ['filter-status'].forEach(function(id) {
            var el = document.getElementById(id);
            bindOnce(el, 'change', loadContratsResetPage);
        });
    }

    function loadContrats() {
        var params = [];
        var search = document.getElementById('contrat-search');
        if (search && search.value) params.push('search=' + encodeURIComponent(search.value));
        var status = document.getElementById('filter-status');
        if (status && status.value) params.push('status=' + encodeURIComponent(status.value));

        var url = 'contrats' + (params.length ? '?' + params.join('&') : '');

        apiGet(url).then(function(contrats) {
            var tbody = document.getElementById('contrats-tbody');
            var countEl = document.getElementById('contrats-count');
            if (!tbody) return;

            if (!contrats || !contrats.length) {
                tbody.innerHTML = '<tr class="table-empty-row"><td colspan="10" class="table-empty">Aucun contrat pour le moment.</td></tr>';
                if (countEl) countEl.textContent = '0';
                setTextById('contrats-total-hours', formatHours(0));
                setTextById('contrats-used-hours', formatHours(0));
                setTextById('contrats-remaining-hours', formatHours(0));
                var pg = document.getElementById('contrats-pagination');
                if (pg) pg.setAttribute('data-total-pages', '1');
                updatePaginationUI('contrats-pagination', 'contrats', loadContrats);
                return;
            }

            var totalH = 0, usedH = 0;
            contrats.forEach(function(c) {
                totalH += parseFloat(c.hours || 0);
                usedH += parseFloat(c.consumed_hours || 0);
            });

            var paged = paginateData(contrats, 'contrats');
            if (countEl) countEl.textContent = paged.total;

            var contratStatusMap = {
                'active': '<span class="badge badge-success">Actif</span>',
                'expired': '<span class="badge badge-warning">Expiré</span>',
                'cancelled': '<span class="badge badge-danger">Annulé</span>'
            };

            var html = '';
            paged.items.forEach(function(c, index) {
                var remaining = (parseFloat(c.hours) || 0) - (parseFloat(c.consumed_hours) || 0);
                html += '<tr class="contrat-row" data-status="' + esc(c.status) + '" data-client="' + (c.client_id || '') + '" data-row-index="' + index + '">';
                html += '<td>' + esc(c.reference || '—') + '</td>';
                html += '<td><a href="' + appUrl('contrats/' + c.id) + '">' + esc(c.project_name || '—') + '</a></td>';
                html += '<td>' + esc(c.client_name || '—') + '</td>';
                html += '<td>' + (contratStatusMap[c.status] || esc(c.status)) + '</td>';
                html += '<td>' + formatHours(c.hours) + '</td>';
                html += '<td>' + formatHours(c.consumed_hours || 0) + '</td>';
                html += '<td>' + formatHours(Math.max(0, remaining)) + '</td>';
                html += '<td>' + (c.rate || 0) + ' €/h</td>';
                html += '<td>' + formatDate(c.start_date) + ' — ' + formatDate(c.end_date) + '</td>';
                html += '<td>';
                html += '<a href="' + appUrl('contrats/' + c.id) + '" class="btn btn-text btn-small">Voir</a>';
                html += '<a href="' + appUrl('contrats/' + c.id + '/edit') + '" class="btn btn-text btn-small role-admin-only">Éditer</a>';
                html += '</td>';
                html += '</tr>';
            });
            tbody.innerHTML = html;
            applyDynamicRoles();

            setTextById('contrats-total-hours', formatHours(totalH));
            setTextById('contrats-used-hours', formatHours(usedH));
            setTextById('contrats-remaining-hours', formatHours(Math.max(0, totalH - usedH)));

            var pg = document.getElementById('contrats-pagination');
            if (pg) pg.setAttribute('data-total-pages', paged.totalPages);
            updatePaginationUI('contrats-pagination', 'contrats', loadContrats);
        }).catch(function() {
            var tbody = document.getElementById('contrats-tbody');
            if (tbody) tbody.innerHTML = '<tr><td colspan="10" class="table-empty">Erreur de chargement.</td></tr>';
        });
    }

    function initContratDetail() {
        if (typeof window.CONTRAT_ID === 'undefined' || !window.CONTRAT_ID) return;
        var id = window.CONTRAT_ID;

        apiGet('contrats/' + id).then(function(c) {
            if (!c) return;
            setTextById('breadcrumb-contrat', 'Contrat ' + (c.project_name || ''));
            setTextById('contrat-title', 'Contrat — ' + (c.project_name || ''));
            setTextById('contrat-client', 'Client : ' + (c.client_name || '—'));
            setTextById('contrat-projet', 'Projet : ' + (c.project_name || '—'));
            setTextById('contrat-reference', c.reference || '—');
            var contratStatusMap = {
                'active': '<span class="badge badge-success">Actif</span>',
                'expired': '<span class="badge badge-warning">Expiré</span>',
                'cancelled': '<span class="badge badge-danger">Annulé</span>'
            };
            setHtmlById('contrat-contract-status', contratStatusMap[c.status] || esc(c.status));
            setHtmlById('contrat-status-badge', contratStatusMap[c.status] || esc(c.status));
            setTextById('contrat-hours', formatHours(c.hours));
            setTextById('contrat-used', formatHours(c.consumed_hours || 0));
            var remaining = (parseFloat(c.hours) || 0) - (parseFloat(c.consumed_hours) || 0);
            setTextById('contrat-remaining', formatHours(Math.max(0, remaining)));
            setTextById('contrat-rate', (c.rate || 0) + ' €/h');
            setTextById('contrat-period', formatDate(c.start_date) + ' — ' + formatDate(c.end_date));
            var pct = c.hours > 0 ? Math.round((parseFloat(c.consumed_hours) || 0) / c.hours * 100) : 0;
            var bar = document.getElementById('contrat-progress');
            if (bar) bar.style.width = Math.min(pct, 100) + '%';
            setTextById('contrat-progress-text', pct + '% consommé');
            setTextById('contrat-sidebar-projet', c.project_name || '—');
            setTextById('contrat-sidebar-client', c.client_name || '—');
            setTextById('contrat-sidebar-start', formatDate(c.start_date));
            setTextById('contrat-sidebar-end', formatDate(c.end_date));
            setTextById('contrat-notes', c.notes || '—');

            if (c.linked_tickets && c.linked_tickets.length) {
                var tbody = document.getElementById('contrat-tickets-tbody');
                if (tbody) {
                    var html = '';
                    c.linked_tickets.forEach(function(t) {
                        html += '<tr>';
                        html += '<td><a href="' + appUrl('tickets/' + t.id) + '">#' + t.id + '</a></td>';
                        html += '<td>' + esc(t.title) + '</td>';
                        html += '<td>' + statusBadge(t.status) + '</td>';
                        html += '<td>' + formatHours(t.spent_hours || 0) + '</td>';
                        html += '<td><a href="' + appUrl('tickets/' + t.id) + '" class="btn btn-text btn-small">Voir</a></td>';
                        html += '</tr>';
                    });
                    tbody.innerHTML = html;
                    applyDynamicRoles();
                }
            }
        }).catch(function() {});
    }

    function initContratForm() {
        var form = document.getElementById('contrat-form');
        if (!form) return;

        var entityId = parseInt(form.getAttribute('data-id') || 0);

        // En mode édition, charger les données du contrat
        if (entityId) {
            apiGet('contrats/' + entityId).then(function(c) {
                if (!c) return;
                setFieldValue('hours', c.hours);
                setFieldValue('rate', c.rate);
                setFieldValue('start_date', c.start_date);
                setFieldValue('end_date', c.end_date);
                setFieldValue('reference', c.reference || '');
                setFieldValue('status', c.status || 'active');
                setFieldValue('notes', c.notes || '');
                setFieldValue('project_id', c.project_id);
                setFieldValue('client_id', c.client_id);
            });
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            clearFormMessage(form);
            var formData = new FormData(form);
            var data = {};
            formData.forEach(function(val, key) {
                if (key === '_token') return;
                data[key] = val;
            });

            data = mapFormData('contrat', data);

            var promise = entityId
                ? apiPut('contrats/' + entityId, data)
                : apiPost('contrats', data);

            promise.then(function(result) {
                window.location.href = appUrl('contrats/' + (result.id || entityId));
            }).catch(function(err) {
                showFormMessage(form, err.message);
            });
        });
    }

    // ===================== PAGE: UTILISATEURS =====================

    function initUsersList() {
        if (document.body.getAttribute('data-page') !== 'utilisateurs') return;
        if (document.getElementById('user-form')) return;

        loadUsers();

        function loadUsersResetPage() {
            _pagination['users'] = { page: 1 };
            loadUsers();
        }

        var search = document.getElementById('user-search');
        bindOnce(search, 'input', debounce(loadUsersResetPage, 300));
        ['filter-role', 'filter-status'].forEach(function(id) {
            var el = document.getElementById(id);
            bindOnce(el, 'change', loadUsersResetPage);
        });
    }

    function loadUsers() {
        var params = [];
        var search = document.getElementById('user-search');
        if (search && search.value) params.push('search=' + encodeURIComponent(search.value));
        var role = document.getElementById('filter-role');
        if (role && role.value) params.push('role=' + encodeURIComponent(role.value));
        var status = document.getElementById('filter-status');
        if (status && status.value) params.push('status=' + encodeURIComponent(status.value));

        var url = 'users' + (params.length ? '?' + params.join('&') : '');

        apiGet(url).then(function(users) {
            var tbody = document.getElementById('users-tbody');
            var countEl = document.getElementById('users-count');
            if (!tbody) return;

            if (!users || !users.length) {
                tbody.innerHTML = '<tr class="table-empty-row"><td colspan="7">Aucun utilisateur trouvé.</td></tr>';
                if (countEl) countEl.textContent = '0';
                var pg = document.getElementById('users-pagination');
                if (pg) pg.setAttribute('data-total-pages', '1');
                updatePaginationUI('users-pagination', 'users', loadUsers);
                return;
            }

            var paged = paginateData(users, 'users');
            if (countEl) countEl.textContent = paged.total;

            var html = '';
            paged.items.forEach(function(u, index) {
                var roleBadge = u.role === 'admin' ? '<span class="badge badge-info">Admin</span>'
                    : u.role === 'collaborateur' ? '<span class="badge badge-warning">Collaborateur</span>'
                    : '<span class="badge">Client</span>';
                var sBadge = u.status === 'active' ? '<span class="badge badge-success">Actif</span>' : '<span class="badge badge-info">Inactif</span>';

                html += '<tr class="user-row" data-user-id="' + u.id + '" data-role="' + esc(u.role) + '" data-status="' + esc(u.status) + '" data-row-index="' + index + '">';
                html += '<td>' + esc(u.first_name + ' ' + u.last_name) + '</td>';
                html += '<td>' + esc(u.email) + '</td>';
                html += '<td>' + esc(u.phone || '—') + '</td>';
                html += '<td>' + roleBadge + '</td>';
                html += '<td>' + sBadge + '</td>';
                html += '<td>' + formatDate(u.last_login || u.last_activity) + '</td>';
                html += '<td>';
                html += '<a href="' + appUrl('utilisateurs/' + u.id + '/edit') + '" class="btn btn-text btn-small">Voir</a>';
                html += '</td>';
                html += '</tr>';
            });
            tbody.innerHTML = html;
            applyDynamicRoles();

            var pg = document.getElementById('users-pagination');
            if (pg) pg.setAttribute('data-total-pages', paged.totalPages);
            updatePaginationUI('users-pagination', 'users', loadUsers);
        }).catch(function() {
            var tbody = document.getElementById('users-tbody');
            if (tbody) tbody.innerHTML = '<tr><td colspan="7" class="table-empty">Erreur de chargement.</td></tr>';
        });
    }

    function initUserForm() {
        var form = document.getElementById('user-form');
        if (!form) return;

        var entityId = parseInt(form.getAttribute('data-id') || 0);

        if (entityId) {
            apiGet('users/' + entityId).then(function(u) {
                if (!u) return;
                setFieldValue('last_name', u.last_name);
                setFieldValue('first_name', u.first_name);
                setFieldValue('email', u.email);
                setFieldValue('phone', u.phone);
                setFieldValue('role', u.role);
                setFieldValue('status', u.status);
            });
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            clearFormMessage(form);
            var formData = new FormData(form);
            var data = {};
            formData.forEach(function(val, key) {
                if (key === '_token') return;
                data[key] = val;
            });

            data = mapFormData('user', data);

            var promise = entityId
                ? apiPut('users/' + entityId, data)
                : apiPost('users', data);

            promise.then(function() {
                window.location.href = appUrl('utilisateurs');
            }).catch(function(err) {
                showFormMessage(form, err.message);
            });
        });

        var deleteBtn = document.getElementById('user-delete-btn');
        if (deleteBtn && entityId) {
            deleteBtn.addEventListener('click', function() {
                if (confirm('Supprimer cet utilisateur ?')) {
                    apiDelete('users/' + entityId).then(function() {
                        window.location.href = appUrl('utilisateurs');
                    }).catch(function(err) { alert(err.message); });
                }
            });
        }
    }

    // ===================== PAGE: TEMPS =====================

    function initTemps() {
        if (document.body.getAttribute('data-page') !== 'temps') return;

        var ticketSel = document.getElementById('time-ticket');
        clearSelectOptions(ticketSel);
        apiGet('tickets').then(function(tickets) {
            if (!ticketSel) return;
            if (tickets && tickets.length) {
                tickets.forEach(function(t) {
                    var opt = document.createElement('option');
                    opt.value = t.id;
                    opt.textContent = '#' + t.id + ' - ' + (t.title || '');
                    ticketSel.appendChild(opt);
                });
            }
        });

        var filterSel = document.getElementById('filter-project');
        clearSelectOptions(filterSel);
        apiGet('projets').then(function(data) {
            if (filterSel && data) {
                var projets = Array.isArray(data) ? data : [];
                projets.forEach(function(p) {
                    var opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = p.name;
                    filterSel.appendChild(opt);
                });
            }
        });

        loadTemps();

        var tempsSearch = document.getElementById('temps-search');
        bindOnce(tempsSearch, 'input', debounce(loadTemps, 300));
        var tempsFilterProject = document.getElementById('filter-project');
        bindOnce(tempsFilterProject, 'change', loadTemps);

        apiGet('temps/month-total').then(function(data) {
            if (!data) return;
            setTextById('temps-month', formatHours(data.total || 0));
            setTextById('temps-total-month', formatHours(data.total || 0));
        }).catch(function() {});

        apiGet('projets').then(function(projets) {
            if (!projets || !projets.length) return;
            var totalContract = 0;
            var totalConsumed = 0;
            projets.forEach(function(p) {
                totalContract += parseFloat(p.contract_hours) || 0;
                totalConsumed += parseFloat(p.total_hours) || 0;
            });
            var remaining = Math.max(0, totalContract - totalConsumed);
            setTextById('temps-remaining', formatHours(remaining));
        }).catch(function() {});

        // Week navigation
        var currentWeekStart = getMonday(new Date());

        function getMonday(d) {
            var dt = new Date(d);
            var day = dt.getDay();
            var diff = dt.getDate() - day + (day === 0 ? -6 : 1);
            dt.setDate(diff);
            return dt;
        }

        function toISODate(d) {
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }

        function loadWeekSummary() {
            var weekStartStr = toISODate(currentWeekStart);
            apiGet('temps/week-summary?week_start=' + weekStartStr).then(function(data) {
                if (!data) return;
                setTextById('temps-week', formatHours(data.total_hours || 0));
                if (data.week_start && data.week_end) {
                    setTextById('week-label', formatDate(data.week_start) + ' — ' + formatDate(data.week_end));
                }
            }).catch(function() {});
        }

        loadWeekSummary();

        var prevBtn = document.getElementById('week-prev');
        var nextBtn = document.getElementById('week-next');
        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                currentWeekStart.setDate(currentWeekStart.getDate() - 7);
                loadWeekSummary();
            });
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                currentWeekStart.setDate(currentWeekStart.getDate() + 7);
                loadWeekSummary();
            });
        }

        var form = document.getElementById('time-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                clearFormMessage(form);
                var formData = new FormData(form);
                var data = {};
                formData.forEach(function(val, key) { data[key] = val; });

                var h = parseFloat(data.heures || data.hours || 0);
                var m = parseFloat(data.minutes || 0);
                data.heures = h + (m / 60);
                delete data.minutes;

                data = mapFormData('temps', data);

                var ticketSel2 = document.getElementById('time-ticket');
                if (ticketSel2 && ticketSel2.value) {
                    data.ticket_id = ticketSel2.value;
                }

                apiPost('temps', data).then(function() {
                    form.reset();
                    var dateField = document.getElementById('time-date');
                    if (dateField) dateField.value = new Date().toISOString().split('T')[0];
                    loadTemps();
                    showFormMessage(form, 'Entrée de temps enregistrée.', 'success');
                    apiGet('temps/month-total').then(function(data) {
                        if (data) {
                            setTextById('temps-month', formatHours(data.total || 0));
                            setTextById('temps-total-month', formatHours(data.total || 0));
                        }
                    });
                }).catch(function(err) {
                    showFormMessage(form, err.message);
                });
            });
        }
    }

    function loadTemps() {
        var params = [];
        var tempsSearch = document.getElementById('temps-search');
        var filterProject = document.getElementById('filter-project');
        if (filterProject && filterProject.value) params.push('project_id=' + encodeURIComponent(filterProject.value));

        var url = 'temps' + (params.length ? '?' + params.join('&') : '');

        apiGet(url).then(function(entries) {
            var tbody = document.getElementById('temps-tbody');
            if (!tbody) return;

            if (tempsSearch && tempsSearch.value) {
                var q = tempsSearch.value.toLowerCase();
                entries = (entries || []).filter(function(e) {
                    return (e.ticket_title || '').toLowerCase().indexOf(q) !== -1
                        || (e.project_name || '').toLowerCase().indexOf(q) !== -1
                        || (e.user_name || '').toLowerCase().indexOf(q) !== -1
                        || (e.description || '').toLowerCase().indexOf(q) !== -1;
                });
            }

            if (!entries || !entries.length) {
                tbody.innerHTML = '<tr class="table-empty-row"><td colspan="7">Aucune entrée de temps.</td></tr>';
                return;
            }
            var html = '';
            entries.forEach(function(e, index) {
                html += '<tr class="time-row" data-project="' + (e.project_id || '') + '" data-row-index="' + index + '">';
                html += '<td>' + formatDate(e.date) + '</td>';
                html += '<td>' + esc(e.user_name || '—') + '</td>';
                html += '<td><a href="' + appUrl('tickets/' + e.ticket_id) + '">' + esc(e.ticket_title || ('#' + e.ticket_id)) + '</a></td>';
                html += '<td>' + esc(e.project_name || '—') + '</td>';
                html += '<td>' + formatHours(e.hours) + '</td>';
                html += '<td>' + esc(e.description || '—') + '</td>';
                html += '<td>';
                html += '<button class="btn btn-text btn-small btn-danger" onclick="deleteTemps(' + e.id + ')">Supprimer</button>';
                html += '</td>';
                html += '</tr>';
            });
            tbody.innerHTML = html;
            applyDynamicRoles();
        }).catch(function() {
            var tbody = document.getElementById('temps-tbody');
            if (tbody) tbody.innerHTML = '<tr><td colspan="7" class="table-empty">Erreur de chargement.</td></tr>';
        });
    }

    window.deleteTemps = function(id) {
        if (confirm('Supprimer cette entrée ?')) {
            apiDelete('temps/' + id).then(function() { loadTemps(); });
        }
    };

    // ===================== PAGE: VALIDATION =====================

    function initValidation() {
        if (document.body.getAttribute('data-page') !== 'ticket-validation') return;

        apiGet('validations').then(function(tickets) {
            var container = document.getElementById('validation-cards');
            var countEl = document.getElementById('validation-count');
            if (!container) return;

            var pending = (tickets || []).filter(function(t) { return t.status === 'to-validate' && t.type === 'billable'; });
            var validated = (tickets || []).filter(function(t) { return (t.status === 'validated' || t.status === 'refused') && t.type === 'billable'; });

            if (countEl) countEl.textContent = pending.length;

            if (!pending.length) {
                container.innerHTML = '<p class="text-secondary">Aucun ticket en attente de validation.</p>';
            } else {
                var totalAmount = 0;
                var html = '';
                pending.forEach(function(t) {
                    var rate = parseFloat(t.contract_rate) || 0;
                    var amount = (parseFloat(t.spent_hours) || 0) * rate;
                    totalAmount += amount;
                    html += '<article class="project-card">';
                    html += '<header class="project-card-header">';
                    html += '<h3>' + esc(t.title) + '</h3>';
                    html += '<span class="badge badge-warning">À valider</span>';
                    html += '</header>';
                    html += '<div class="project-card-body">';
                    html += '<p><strong>Projet :</strong> ' + esc(t.project_name || '—') + '</p>';
                    html += '<p><strong>Description :</strong> ' + esc(t.description || '—') + '</p>';
                    html += '<div class="card-body-spacing">';
                    html += '<p><strong>Temps passé :</strong> ' + formatHours(t.spent_hours) + '</p>';
                    html += '<p><strong>Montant estimé :</strong> <span class="text-amount">' + amount.toFixed(2) + ' €</span></p>';
                    html += '</div></div>';
                    html += '<footer class="project-card-footer card-footer-flex">';
                    html += '<button class="btn btn-success flex-1" onclick="validateTicket(' + t.id + ')">✅ Valider</button>';
                    html += '<button class="btn btn-danger flex-1" onclick="refuseTicket(' + t.id + ')">❌ Refuser</button>';
                    html += '<a href="' + appUrl('tickets/' + t.id) + '" class="btn btn-text">Voir détails</a>';
                    html += '</footer></article>';
                });
                container.innerHTML = html;
                setTextById('validation-total', totalAmount.toFixed(2) + ' €');
            }

            var historyTbody = document.getElementById('validation-history');
            if (historyTbody && validated.length) {
                var hhtml = '';
                validated.forEach(function(t) {
                    var rate = parseFloat(t.contract_rate) || 0;
                    var amount = (parseFloat(t.spent_hours) || 0) * rate;
                    hhtml += '<tr>';
                    hhtml += '<td><a href="' + appUrl('tickets/' + t.id) + '">' + esc(t.title) + '</a></td>';
                    hhtml += '<td>' + esc(t.project_name || '—') + '</td>';
                    hhtml += '<td>' + formatHours(t.spent_hours) + '</td>';
                    hhtml += '<td>' + amount.toFixed(2) + ' €</td>';
                    hhtml += '<td>' + statusBadge(t.status) + '</td>';
                    hhtml += '<td>' + formatDate(t.created_at) + '</td>';
                    hhtml += '<td><a href="' + appUrl('tickets/' + t.id) + '" class="btn btn-text btn-small">Voir</a></td>';
                    hhtml += '</tr>';
                });
                historyTbody.innerHTML = hhtml;
            }
        }).catch(function() {
            var container = document.getElementById('validation-cards');
            if (container) container.innerHTML = '<p class="text-secondary">Erreur de chargement.</p>';
        });
    }

    function loadClientValidationCards() {
        var section = document.getElementById('client-validation-section');
        var container = document.getElementById('tickets-validation-cards');
        if (!container) return;

        apiGet('validations').then(function(tickets) {
            var pending = (tickets || []).filter(function(t) { return t.status === 'to-validate' && t.type === 'billable'; });
            var countEl = document.getElementById('tickets-validation-count');
            if (countEl) countEl.textContent = pending.length;

            if (!pending.length) {
                container.innerHTML = '<p class="text-secondary">Aucun ticket en attente de validation.</p>';
                if (section) section.style.display = 'block';
                return;
            }

            var totalAmount = 0;
            var html = '';
            pending.forEach(function(t) {
                var rate = parseFloat(t.contract_rate) || 0;
                var amount = (parseFloat(t.spent_hours) || 0) * rate;
                totalAmount += amount;
                html += '<article class="project-card">';
                html += '<header class="project-card-header">';
                html += '<h3>' + esc(t.title) + '</h3>';
                html += '<span class="badge badge-warning">À valider</span>';
                html += '</header>';
                html += '<div class="project-card-body">';
                html += '<p><strong>Projet :</strong> ' + esc(t.project_name || '—') + '</p>';
                html += '<p><strong>Description :</strong> ' + esc(t.description || '—') + '</p>';
                html += '<div class="card-body-spacing">';
                html += '<p><strong>Temps passé :</strong> ' + formatHours(t.spent_hours) + '</p>';
                html += '<p><strong>Montant estimé :</strong> <span class="text-amount">' + amount.toFixed(2) + ' €</span></p>';
                html += '</div></div>';
                html += '<footer class="project-card-footer card-footer-flex">';
                html += '<button class="btn btn-success flex-1" onclick="validateTicket(' + t.id + ')">✅ Valider</button>';
                html += '<button class="btn btn-danger flex-1" onclick="refuseTicket(' + t.id + ')">❌ Refuser</button>';
                html += '<a href="' + appUrl('tickets/' + t.id) + '" class="btn btn-text">Voir détails</a>';
                html += '</footer></article>';
            });
            container.innerHTML = html;
            setTextById('tickets-validation-total', totalAmount.toFixed(2) + ' €');
            if (section) section.style.display = 'block';
        }).catch(function() {
            container.innerHTML = '<p class="text-secondary">Erreur de chargement.</p>';
            if (section) section.style.display = 'block';
        });
    }

    window.validateTicket = function(id) {
        apiPost('validations/' + id, { status: 'validated' })
            .then(function() {
                if (document.body.getAttribute('data-page') === 'tickets') {
                    loadClientValidationCards();
                    loadTickets();
                } else {
                    location.reload();
                }
            })
            .catch(function(err) { alert(err.message); });
    };

    window.refuseTicket = function(id) {
        var motif = prompt('Motif du refus :');
        if (motif === null) return;
        apiPost('validations/' + id, { status: 'refused', comment: motif })
            .then(function() {
                if (document.body.getAttribute('data-page') === 'tickets') {
                    loadClientValidationCards();
                    loadTickets();
                } else {
                    location.reload();
                }
            })
            .catch(function(err) { alert(err.message); });
    };

    // ===================== PAGE: PROFIL =====================

    function initProfil() {
        if (document.body.getAttribute('data-page') !== 'profil') return;

        var userId = CFG.user ? CFG.user.id : 0;

        var profileForm = document.getElementById('profile-form');
        if (profileForm) {
            profileForm.addEventListener('submit', function(e) {
                e.preventDefault();
                clearFormMessage(profileForm);
                var formData = new FormData(profileForm);
                var data = {};
                formData.forEach(function(val, key) {
                    if (key !== 'photo') data[key] = val;
                });

                apiPut('users/profil', data).then(function(result) {
                    showFormMessage(profileForm, result.message || 'Profil mis à jour.', 'success');
                }).catch(function(err) {
                    showFormMessage(profileForm, err.message);
                });
            });
        }

        var passwordForm = document.getElementById('password-form');
        if (passwordForm) {
            passwordForm.addEventListener('submit', function(e) {
                e.preventDefault();
                clearFormMessage(passwordForm);
                var formData = new FormData(passwordForm);
                var data = {};
                formData.forEach(function(val, key) { data[key] = val; });

                if (data.new_password !== data.confirm_password) {
                    showFormMessage(passwordForm, 'Les mots de passe ne correspondent pas.');
                    return;
                }

                apiPost('change-password', data).then(function(result) {
                    showFormMessage(passwordForm, result.message || 'Mot de passe modifié.', 'success');
                    passwordForm.reset();
                }).catch(function(err) {
                    showFormMessage(passwordForm, err.message);
                });
            });
        }
    }

    // ===================== PAGE: RAPPORTS =====================

    function initRapports() {
        if (document.body.getAttribute('data-page') !== 'rapports') return;

        var projSel = document.getElementById('report-project');
        clearSelectOptions(projSel);
        apiGet('projets').then(function(data) {
            if (projSel && data) {
                (Array.isArray(data) ? data : []).forEach(function(p) {
                    var opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = p.name;
                    projSel.appendChild(opt);
                });
            }
        });
        var clientSel = document.getElementById('report-client');
        clearSelectOptions(clientSel);
        apiGet('users/clients').then(function(data) {
            if (clientSel && data) {
                (Array.isArray(data) ? data : []).forEach(function(c) {
                    var opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.first_name + ' ' + c.last_name;
                    clientSel.appendChild(opt);
                });
            }
        });

        loadReportData();

        window.updateReports = loadReportData;
    }

    function loadReportData() {
        var params = [];
        var dateFrom = document.getElementById('report-date-from');
        var dateTo = document.getElementById('report-date-to');
        var projectSel = document.getElementById('report-project');
        var clientSel = document.getElementById('report-client');

        if (dateFrom && dateFrom.value) params.push('date_from=' + encodeURIComponent(dateFrom.value));
        if (dateTo && dateTo.value) params.push('date_to=' + encodeURIComponent(dateTo.value));
        if (projectSel && projectSel.value) params.push('project_id=' + encodeURIComponent(projectSel.value));
        if (clientSel && clientSel.value) params.push('client_id=' + encodeURIComponent(clientSel.value));

        var url = 'rapports' + (params.length ? '?' + params.join('&') : '');

        apiGet(url).then(function(data) {
            if (!data) return;

            setTextById('kpi-tickets', data.total_tickets || 0);
            setTextById('kpi-hours', formatHours(data.total_hours || 0));
            setTextById('kpi-projects', data.total_projects || 0);

            var totalTickets = 0, validatedTickets = 0;
            if (data.tickets_by_status) {
                data.tickets_by_status.forEach(function(s) {
                    totalTickets += parseInt(s.count || 0);
                    if (s.status === 'validated' || s.status === 'done') validatedTickets += parseInt(s.count || 0);
                });
            }
            var validRate = totalTickets > 0 ? Math.round((validatedTickets / totalTickets) * 100) : 0;
            setTextById('kpi-validation', validRate + '%');

            var totalRevenue = 0;
            if (data.billing) {
                data.billing.forEach(function(b) {
                    totalRevenue += parseFloat(b.consumed_hours || 0) * parseFloat(b.rate || 0);
                });
            }
            setTextById('kpi-revenue', totalRevenue.toLocaleString('fr-FR', { minimumFractionDigits: 0 }) + ' €');

            var execEl = document.getElementById('report-executive');
            if (execEl) {
                execEl.innerHTML = 'Sur la période sélectionnée : <strong>' + (data.total_tickets || 0) + '</strong> tickets traités, <strong>' + formatHours(data.total_hours || 0) + '</strong> heures enregistrées, taux de validation <strong>' + validRate + '%</strong>, chiffre d\'affaires <strong>' + totalRevenue.toLocaleString('fr-FR', { minimumFractionDigits: 0 }) + ' €</strong>.';
            }

            var chartHP = document.getElementById('chart-hours-project');
            if (chartHP && data.hours_by_project) {
                if (!data.hours_by_project.length) {
                    chartHP.innerHTML = '<p class="text-secondary">Aucune donnée pour cette période.</p>';
                } else {
                    var maxHP = Math.max.apply(null, data.hours_by_project.map(function(d) { return parseFloat(d.total || 0); }));
                    var totalHP = data.hours_by_project.reduce(function(s, d) { return s + parseFloat(d.total || 0); }, 0);
                    var colorsHP = ['dashboard-chart-bar-primary', 'dashboard-chart-bar-blue', 'dashboard-chart-bar-green', 'dashboard-chart-bar-amber', 'dashboard-chart-bar-gray'];
                    var hpHtml = '<div class="dashboard-chart-bars">';
                    data.hours_by_project.forEach(function(item, i) {
                        var pct = maxHP > 0 ? Math.round((parseFloat(item.total || 0) / maxHP) * 100) : 0;
                        hpHtml += '<div class="dashboard-chart-row">';
                        hpHtml += '<span class="dashboard-chart-label">' + esc(item.name) + '</span>';
                        hpHtml += '<div class="dashboard-chart-bar-wrap">';
                        hpHtml += '<div class="dashboard-chart-bar ' + (colorsHP[i % colorsHP.length]) + '" style="width:' + pct + '%;"></div>';
                        hpHtml += '</div>';
                        hpHtml += '<span class="dashboard-chart-value">' + formatHours(item.total) + '</span>';
                        hpHtml += '</div>';
                    });
                    hpHtml += '</div>';
                    chartHP.innerHTML = hpHtml;
                    setTextById('chart-hours-project-total', 'Total : ' + formatHours(totalHP));
                }
            }

            var chartTS = document.getElementById('chart-tickets-status');
            if (chartTS && data.tickets_by_status) {
                if (!data.tickets_by_status.length) {
                    chartTS.innerHTML = '<p class="text-secondary">Aucune donnée.</p>';
                } else {
                    var totalTS = data.tickets_by_status.reduce(function(s, d) { return s + parseInt(d.count || 0); }, 0);
                    var colorsTS = ['dashboard-chart-bar-blue', 'dashboard-chart-bar-primary', 'dashboard-chart-bar-green', 'dashboard-chart-bar-amber', 'dashboard-chart-bar-gray'];
                    var tsHtml = '<div class="dashboard-chart-bars">';
                    data.tickets_by_status.forEach(function(item, i) {
                        var pct = totalTS > 0 ? Math.round((parseInt(item.count || 0) / totalTS) * 100) : 0;
                        tsHtml += '<div class="dashboard-chart-row">';
                        tsHtml += '<span class="dashboard-chart-label">' + statusLabel(item.status) + '</span>';
                        tsHtml += '<div class="dashboard-chart-bar-wrap">';
                        tsHtml += '<div class="dashboard-chart-bar ' + (colorsTS[i % colorsTS.length]) + '" style="width:' + pct + '%;"></div>';
                        tsHtml += '</div>';
                        tsHtml += '<span class="dashboard-chart-value">' + item.count + '</span>';
                        tsHtml += '</div>';
                    });
                    tsHtml += '</div>';
                    chartTS.innerHTML = tsHtml;
                }
            }

            var chartHU = document.getElementById('chart-hours-user');
            if (chartHU && data.hours_by_user) {
                if (!data.hours_by_user.length) {
                    chartHU.innerHTML = '<p class="text-secondary">Aucune donnée.</p>';
                } else {
                    var maxHU = Math.max.apply(null, data.hours_by_user.map(function(d) { return parseFloat(d.total || 0); }));
                    var totalHU = data.hours_by_user.reduce(function(s, d) { return s + parseFloat(d.total || 0); }, 0);
                    var colorsHU = ['dashboard-chart-bar-primary', 'dashboard-chart-bar-blue', 'dashboard-chart-bar-green'];
                    var huHtml = '';
                    data.hours_by_user.forEach(function(item, i) {
                        var pct = maxHU > 0 ? Math.round((parseFloat(item.total || 0) / maxHU) * 100) : 0;
                        huHtml += '<div class="dashboard-chart-row">';
                        huHtml += '<span class="dashboard-chart-label">' + esc(item.name) + '</span>';
                        huHtml += '<div class="dashboard-chart-bar-wrap">';
                        huHtml += '<div class="dashboard-chart-bar ' + (colorsHU[i % colorsHU.length]) + '" style="width:' + pct + '%;"></div>';
                        huHtml += '</div>';
                        huHtml += '<span class="dashboard-chart-value">' + formatHours(item.total) + '</span>';
                        huHtml += '</div>';
                    });
                    chartHU.innerHTML = huHtml;
                    setTextById('chart-hours-user-total', 'Total : ' + formatHours(totalHU));
                }
            }

            var billingTbody = document.getElementById('report-billing-tbody');
            if (billingTbody && data.billing) {
                if (!data.billing.length) {
                    billingTbody.innerHTML = '<tr><td colspan="5" class="table-empty">Aucune donnée de facturation.</td></tr>';
                } else {
                    var bHtml = '';
                    var totalBillingHours = 0, totalBillingAmount = 0;
                    data.billing.forEach(function(b) {
                        var consumed = parseFloat(b.consumed_hours || 0);
                        var rate = parseFloat(b.rate || 0);
                        var amount = consumed * rate;
                        totalBillingHours += consumed;
                        totalBillingAmount += amount;
                        bHtml += '<tr>';
                        bHtml += '<td>' + esc(b.client_name || '—') + '</td>';
                        bHtml += '<td>' + esc(b.project_name || '—') + '</td>';
                        bHtml += '<td>' + formatHours(consumed) + '</td>';
                        bHtml += '<td>' + rate.toFixed(0) + ' €/h</td>';
                        bHtml += '<td>' + amount.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €</td>';
                        bHtml += '</tr>';
                    });
                    billingTbody.innerHTML = bHtml;
                    setTextById('report-billing-hours', formatHours(totalBillingHours));
                    setTextById('report-billing-total', totalBillingAmount.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €');
                }
            }

            var detailTbody = document.getElementById('report-detail-tbody');
            if (detailTbody) {
                var tempsParams = [];
                if (dateFrom && dateFrom.value) tempsParams.push('date_from=' + encodeURIComponent(dateFrom.value));
                if (dateTo && dateTo.value) tempsParams.push('date_to=' + encodeURIComponent(dateTo.value));
                var tempsUrl = 'temps' + (tempsParams.length ? '?' + tempsParams.join('&') : '');
                apiGet(tempsUrl).then(function(entries) {
                    if (!entries || !entries.length) {
                        detailTbody.innerHTML = '<tr><td colspan="6" class="table-empty">Aucune entrée de temps.</td></tr>';
                        return;
                    }
                    var dHtml = '';
                    entries.slice(0, 20).forEach(function(e) {
                        dHtml += '<tr>';
                        dHtml += '<td>' + formatDate(e.date) + '</td>';
                        dHtml += '<td>' + esc(e.project_name || '—') + '</td>';
                        dHtml += '<td>' + esc(e.ticket_title || '—') + '</td>';
                        dHtml += '<td>' + esc(e.user_name || '—') + '</td>';
                        dHtml += '<td>' + formatHours(e.hours) + '</td>';
                        dHtml += '<td>' + esc(e.description || '—') + '</td>';
                        dHtml += '</tr>';
                    });
                    detailTbody.innerHTML = dHtml;
                }).catch(function() {
                    detailTbody.innerHTML = '<tr><td colspan="6" class="table-empty">Erreur de chargement.</td></tr>';
                });
            }

            var periodInfo = document.getElementById('report-period-info');
            if (periodInfo) {
                var fromVal = dateFrom ? dateFrom.value : '';
                var toVal = dateTo ? dateTo.value : '';
                periodInfo.innerHTML = '<strong>Période affichée :</strong> ' + (fromVal ? formatDate(fromVal) : 'Début') + ' — ' + (toVal ? formatDate(toVal) : '—');
            }
        }).catch(function() {
            var execEl = document.getElementById('report-executive');
            if (execEl) execEl.textContent = 'Erreur de chargement des données du rapport.';
        });
    }

    // ===================== HELPERS =====================

    function setTextById(id, text) {
        var el = document.getElementById(id);
        if (el) el.textContent = text;
    }

    function setHtmlById(id, html) {
        var el = document.getElementById(id);
        if (el) el.innerHTML = html;
    }

    function setFieldValue(id, value) {
        var el = document.getElementById(id);
        if (el) el.value = value || '';
    }

    function debounce(fn, delay) {
        var timer;
        return function() {
            var args = arguments;
            var ctx = this;
            clearTimeout(timer);
            timer = setTimeout(function() { fn.apply(ctx, args); }, delay);
        };
    }

    // ===================== INITIALISATION =====================

    function initAll() {
        initDashboard();
        initTicketsList();
        initTicketDetail();
        initTicketForm();
        initProjetsList();
        initProjetDetail();
        initProjetForm();
        initContratsList();
        initContratDetail();
        initContratForm();
        initUsersList();
        initUserForm();
        initTemps();
        initValidation();
        initProfil();
        initRapports();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

    document.addEventListener('systicket:contentLoaded', initAll);

    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            initAll();
        }
    });

})();
