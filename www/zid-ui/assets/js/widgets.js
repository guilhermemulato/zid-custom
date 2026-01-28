(function () {
  const state = {
    widgets: {},
  };

  function formatTime(ts) {
    if (!ts) return '--';
    const date = new Date(ts * 1000);
    return date.toLocaleTimeString('pt-BR');
  }

  function updateWidgetFooter(el, ts, status) {
    const footer = el.querySelector('.widget-footer');
    if (!footer) return;
    const label = ts ? `last: ${formatTime(ts)}` : 'last: --';
    footer.textContent = status ? `${label} • ${status}` : label;
  }

  function setWidgetBody(el, text) {
    const body = el.querySelector('.widget-body');
    if (body) body.textContent = text;
  }

  function setKpi(id, value, meta) {
    const card = document.getElementById(id);
    if (!card) return;
    const valEl = card.querySelector('.card-value');
    const metaEl = card.querySelector('.card-meta');
    if (valEl) valEl.textContent = value;
    if (metaEl) metaEl.textContent = meta;
  }

  function formatCpu(cpu) {
    if (Array.isArray(cpu)) {
      return cpu.map((v) => Number(v).toFixed(2)).join(' / ');
    }
    if (typeof cpu === 'string') return cpu;
    if (typeof cpu === 'number') return cpu.toFixed(2);
    return '--';
  }

  function formatMem(mem) {
    if (!mem) return '--';
    if (mem.memUsed !== undefined && mem.memTotal !== undefined) {
      const used = Math.round(mem.memUsed / (1024 * 1024));
      const total = Math.round(mem.memTotal / (1024 * 1024));
      return `${used}MB / ${total}MB`;
    }
    if (mem.physmem) {
      const total = Math.round(mem.physmem / (1024 * 1024 * 1024));
      return `${total}GB total`;
    }
    return JSON.stringify(mem);
  }

  async function pollWidget(widget) {
    if (widget.controller) {
      widget.controller.abort();
    }
    widget.controller = new AbortController();

    try {
      const res = await fetch(widget.url, {
        signal: widget.controller.signal,
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin',
      });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const json = await res.json();
      widget.failCount = 0;
      widget.lastOk = Date.now();
      widget.render(json);
      updateWidgetFooter(widget.el, json.data && json.data.timestamp ? json.data.timestamp : Math.floor(Date.now() / 1000), 'ok');
    } catch (err) {
      widget.failCount = (widget.failCount || 0) + 1;
      setWidgetBody(widget.el, 'Offline ou erro ao atualizar.');
      updateWidgetFooter(widget.el, null, `falha ${widget.failCount}`);
    } finally {
      scheduleWidget(widget);
    }
  }

  function scheduleWidget(widget) {
    const base = widget.interval || 5000;
    const backoff = Math.min(30000, base * Math.max(1, widget.failCount || 0));
    clearTimeout(widget.timer);
    widget.timer = setTimeout(() => pollWidget(widget), backoff);
  }

  function initWidgets() {
    const widgets = [
      {
        id: 'widget-health',
        url: '/api/health.php',
        interval: 2000,
        render: (json) => {
          const body = json.data && json.data.status ? json.data.status : 'ok';
          setWidgetBody(state.widgets['widget-health'].el, `Status: ${body}`);
          setKpi('kpi-health', body.toUpperCase(), `user: ${json.data && json.data.user ? json.data.user : '--'}`);
        },
      },
      {
        id: 'widget-metrics',
        url: '/api/metrics.php',
        interval: 5000,
        render: (json) => {
          const data = json.data || {};
          setWidgetBody(state.widgets['widget-metrics'].el, 'Metricas atualizadas.');
          if (data.cpu) setKpi('kpi-cpu', formatCpu(data.cpu), 'load');
          if (data.mem) setKpi('kpi-mem', formatMem(data.mem), 'mem');
          if (data.disk) {
            const free = data.disk.free ? Math.round(data.disk.free / (1024 * 1024 * 1024)) : '--';
            const total = data.disk.total ? Math.round(data.disk.total / (1024 * 1024 * 1024)) : '--';
            setKpi('kpi-disk', `${free}G / ${total}G`, 'livre/total');
          }
        },
      },
      {
        id: 'widget-log',
        url: '/api/log_tail.php',
        interval: 10000,
        render: (json) => {
          const lines = json.data && json.data.lines ? json.data.lines : [];
          setWidgetBody(state.widgets['widget-log'].el, lines.slice(-3).join(' | ') || 'Sem logs');
        },
      },
      {
        id: 'widget-map',
        url: '/api/map_events.php',
        interval: 8000,
        render: (json) => {
          const events = json.data && json.data.events ? json.data.events : [];
          setWidgetBody(state.widgets['widget-map'].el, `${events.length} eventos`);
        },
      },
      {
        id: 'widget-services',
        url: '/api/services.php',
        interval: 5000,
        render: (json) => {
          const data = json.data && json.data.dhcp ? json.data.dhcp : null;
          if (!data) {
            setWidgetBody(state.widgets['widget-services'].el, 'Sem dados');
            return;
          }
          const status = data.running ? 'ativo' : 'parado';
          const enabled = data.enabled ? 'habilitado' : 'desabilitado';
          const ifs = data.interfaces && data.interfaces.length ? data.interfaces.join(', ') : 'nenhuma';
          setWidgetBody(state.widgets['widget-services'].el, `DHCP ${status} • ${enabled} • if: ${ifs}`);
          setKpi('kpi-dhcp', status.toUpperCase(), enabled);
        },
      },
    ];

    widgets.forEach((widget) => {
      const el = document.getElementById(widget.id);
      if (!el) return;
      widget.el = el;
      state.widgets[widget.id] = widget;
      pollWidget(widget);
    });

    const btnUpdate = document.getElementById('btn-update');
    const updateOutput = document.getElementById('update-output');
    if (btnUpdate && window.ZID_UI && window.ZID_UI.isAdmin) {
      btnUpdate.addEventListener('click', async () => {
        btnUpdate.disabled = true;
        if (updateOutput) updateOutput.textContent = 'Executando update...';
        try {
          const form = new FormData();
          form.append('csrf', window.ZID_UI.csrfToken || '');
          const res = await fetch('/api/do_update.php', { method: 'POST', body: form, credentials: 'same-origin' });
          const json = await res.json();
          if (!res.ok || !json.ok) {
            throw new Error(json.error || 'Falha');
          }
          if (updateOutput) {
            updateOutput.textContent = (json.data && json.data.output) ? json.data.output.join('\n') : 'OK';
          }
          setTimeout(() => window.location.reload(), 1500);
        } catch (err) {
          if (updateOutput) updateOutput.textContent = `Erro: ${err.message}`;
          btnUpdate.disabled = false;
        }
      });
    }

    const serviceButtons = document.querySelectorAll('[data-service][data-action]');
    serviceButtons.forEach((btn) => {
      btn.addEventListener('click', async () => {
        btn.disabled = true;
        try {
          const form = new FormData();
          form.append('csrf', window.ZID_UI.csrfToken || '');
          form.append('service', btn.dataset.service || '');
          form.append('action', btn.dataset.action || '');
          const res = await fetch('/api/service_action.php', { method: 'POST', body: form, credentials: 'same-origin' });
          const json = await res.json();
          if (!res.ok || !json.ok) {
            throw new Error(json.error || 'Falha');
          }
        } catch (err) {
          alert(`Erro: ${err.message}`);
        } finally {
          btn.disabled = false;
        }
      });
    });

    const dhcpForm = document.getElementById('dhcp-config-form');
    const dhcpStatus = document.getElementById('dhcp-config-status');
    if (dhcpForm) {
      const loadConfig = async () => {
        const iface = dhcpForm.querySelector('select[name="interface"]').value;
        const res = await fetch(`/api/dhcp_config.php?interface=${encodeURIComponent(iface)}`, { credentials: 'same-origin' });
        const json = await res.json();
        if (json.ok && json.data) {
          dhcpForm.querySelector('input[name="range_from"]').value = json.data.range_from || '';
          dhcpForm.querySelector('input[name="range_to"]').value = json.data.range_to || '';
          dhcpForm.querySelector('input[name="gateway"]').value = json.data.gateway || '';
          const dns = Array.isArray(json.data.dnsservers) ? json.data.dnsservers : [];
          dhcpForm.querySelector('input[name="dns1"]').value = dns[0] || '';
          dhcpForm.querySelector('input[name="dns2"]').value = dns[1] || '';
          dhcpForm.querySelector('input[name="default_leasetime"]').value = json.data.default_leasetime || '';
          dhcpForm.querySelector('input[name="max_leasetime"]').value = json.data.max_leasetime || '';
          dhcpForm.querySelector('input[name="enable"]').checked = !!json.data.enable;
          if (dhcpStatus) dhcpStatus.textContent = 'Configuracao carregada.';
        }
      };

      dhcpForm.addEventListener('change', (ev) => {
        if (ev.target && ev.target.name === 'interface') {
          loadConfig();
        }
      });

      dhcpForm.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        if (dhcpStatus) dhcpStatus.textContent = 'Salvando...';
        try {
          const form = new FormData(dhcpForm);
          form.append('csrf', window.ZID_UI.csrfToken || '');
          form.set('enable', dhcpForm.querySelector('input[name="enable"]').checked ? '1' : '0');
          const res = await fetch('/api/dhcp_config.php', { method: 'POST', body: form, credentials: 'same-origin' });
          const json = await res.json();
          if (!res.ok || !json.ok) {
            throw new Error(json.error || 'Falha');
          }
          if (dhcpStatus) dhcpStatus.textContent = 'Salvo com sucesso.';
        } catch (err) {
          if (dhcpStatus) dhcpStatus.textContent = `Erro: ${err.message}`;
        }
      });

      loadConfig();
    }

    const staticForm = document.getElementById('dhcp-static-form');
    const staticStatus = document.getElementById('dhcp-static-status');
    const staticTable = document.getElementById('dhcp-static-table');
    if (staticForm && staticTable) {
      const renderStaticRows = (items) => {
        const body = staticTable.querySelector('tbody');
        body.innerHTML = '';
        if (!items.length) {
          const row = document.createElement('tr');
          row.innerHTML = '<td colspan="3">Nenhum mapeamento encontrado.</td>';
          body.appendChild(row);
          return;
        }
        items.forEach((item) => {
          const row = document.createElement('tr');
          row.innerHTML = `
            <td>${item.mac || '--'}</td>
            <td>${item.ipaddr || '--'}</td>
            <td>${item.descr || '--'}</td>
          `;
          body.appendChild(row);
        });
      };

      const loadStatic = async () => {
        const iface = staticForm.querySelector('select[name="interface"]').value;
        const res = await fetch(`/api/dhcp_static.php?interface=${encodeURIComponent(iface)}`, { credentials: 'same-origin' });
        const json = await res.json();
        if (json.ok && json.data) {
          renderStaticRows(json.data.items || []);
          if (staticStatus) staticStatus.textContent = 'Mapeamentos carregados.';
        }
      };

      staticForm.addEventListener('change', (ev) => {
        if (ev.target && ev.target.name === 'interface') {
          loadStatic();
        }
      });

      staticForm.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        if (staticStatus) staticStatus.textContent = 'Salvando...';
        try {
          const form = new FormData(staticForm);
          form.append('csrf', window.ZID_UI.csrfToken || '');
          const res = await fetch('/api/dhcp_static.php', { method: 'POST', body: form, credentials: 'same-origin' });
          const json = await res.json();
          if (!res.ok || !json.ok) {
            throw new Error(json.error || 'Falha');
          }
          if (staticStatus) staticStatus.textContent = 'Salvo com sucesso.';
          staticForm.querySelector('input[name="mac"]').value = '';
          staticForm.querySelector('input[name="ipaddr"]').value = '';
          staticForm.querySelector('input[name="descr"]').value = '';
          loadStatic();
        } catch (err) {
          if (staticStatus) staticStatus.textContent = `Erro: ${err.message}`;
        }
      });

      loadStatic();
    }

    const fwList = document.getElementById('fw-list');
    const fwTabs = document.getElementById('fw-tabs');
    const fwFilter = document.getElementById('fw-filter');
    const fwAddToggle = document.getElementById('fw-add-toggle');
    const fwAddPanel = document.getElementById('fw-add-panel');
    const fwAddForm = document.getElementById('fw-add-form');
    const fwAddStatus = document.getElementById('fw-add-status');

    if (fwList && fwTabs) {
      let currentIface = (fwTabs.querySelector('.fw-tab.active') || fwTabs.querySelector('.fw-tab'))?.dataset.iface || 'lan';
      let rulesCache = [];
      let draggingId = null;

      const setAddInterface = (iface) => {
        if (fwAddForm) {
          const select = fwAddForm.querySelector('select[name="interface"]');
          if (select) select.value = iface;
        }
      };

      const fetchRules = async () => {
        fwList.innerHTML = '<div class="fw-empty">Carregando regras...</div>';
        const res = await fetch(`/api/firewall_rules.php?interface=${encodeURIComponent(currentIface)}`, { credentials: 'same-origin' });
        const json = await res.json();
        if (json.ok && json.data) {
          rulesCache = json.data.items || [];
          renderRules();
        } else {
          fwList.innerHTML = '<div class="fw-empty">Falha ao carregar.</div>';
        }
      };

      const renderRules = () => {
        const filterValue = fwFilter ? fwFilter.value.trim().toLowerCase() : '';
        fwList.innerHTML = '';
        const filtered = rulesCache.filter((rule) => {
          if (!filterValue) return true;
          const text = `${rule.action} ${rule.protocol} ${rule.source} ${rule.destination} ${rule.port} ${rule.descr}`.toLowerCase();
          return text.includes(filterValue);
        });

        if (!filtered.length) {
          fwList.innerHTML = '<div class="fw-empty">Nenhuma regra encontrada.</div>';
          return;
        }

        filtered.forEach((rule) => {
          const row = document.createElement('div');
          row.className = 'fw-row';
          row.setAttribute('draggable', 'true');
          row.dataset.id = rule.id;

          const toggle = document.createElement('label');
          toggle.className = 'switch';
          toggle.innerHTML = `<input type="checkbox" ${rule.enabled ? 'checked' : ''} /><span class="slider"></span>`;

          const toggleCell = document.createElement('div');
          toggleCell.appendChild(toggle);

          const badge = document.createElement('span');
          badge.className = `fw-badge ${rule.action}`;
          badge.textContent = rule.protocol;

          const actionCell = document.createElement('div');
          actionCell.appendChild(badge);

          const srcCell = document.createElement('div');
          srcCell.textContent = rule.source || '*';

          const dstCell = document.createElement('div');
          dstCell.textContent = rule.destination || '*';

          const portCell = document.createElement('div');
          portCell.textContent = rule.port || '*';

          const descrCell = document.createElement('div');
          descrCell.textContent = rule.descr || '--';

          const actionsCell = document.createElement('div');
          actionsCell.className = 'fw-actions';
          const copyBtn = document.createElement('button');
          copyBtn.className = 'btn-icon';
          copyBtn.textContent = 'Copy';
          copyBtn.dataset.action = 'copy';
          actionsCell.appendChild(copyBtn);

          row.appendChild(toggleCell);
          row.appendChild(actionCell);
          row.appendChild(srcCell);
          row.appendChild(dstCell);
          row.appendChild(portCell);
          row.appendChild(descrCell);
          row.appendChild(actionsCell);

          toggle.querySelector('input').addEventListener('change', async (ev) => {
            const enabled = ev.target.checked ? '1' : '0';
            try {
              const form = new FormData();
              form.append('csrf', window.ZID_UI.csrfToken || '');
              form.append('action', 'toggle');
              form.append('id', rule.id);
              form.append('enabled', enabled);
              const res = await fetch(`/api/firewall_rules.php?interface=${encodeURIComponent(currentIface)}`, { method: 'POST', body: form, credentials: 'same-origin' });
              const json = await res.json();
              if (!res.ok || !json.ok) {
                throw new Error(json.error || 'Falha');
              }
            } catch (err) {
              ev.target.checked = !ev.target.checked;
              alert(`Erro: ${err.message}`);
            }
          });

          copyBtn.addEventListener('click', async () => {
            copyBtn.disabled = true;
            try {
              const form = new FormData();
              form.append('csrf', window.ZID_UI.csrfToken || '');
              form.append('action', 'copy');
              form.append('id', rule.id);
              const res = await fetch(`/api/firewall_rules.php?interface=${encodeURIComponent(currentIface)}`, { method: 'POST', body: form, credentials: 'same-origin' });
              const json = await res.json();
              if (!res.ok || !json.ok) {
                throw new Error(json.error || 'Falha');
              }
              fetchRules();
            } catch (err) {
              alert(`Erro: ${err.message}`);
            } finally {
              copyBtn.disabled = false;
            }
          });

          row.addEventListener('dragstart', (ev) => {
            draggingId = rule.id;
            row.classList.add('dragging');
            ev.dataTransfer.effectAllowed = 'move';
          });
          row.addEventListener('dragend', () => {
            draggingId = null;
            row.classList.remove('dragging');
          });

          fwList.appendChild(row);
        });
      };

      const reorderRules = async () => {
        const ids = Array.from(fwList.querySelectorAll('.fw-row')).map((row) => row.dataset.id);
        if (!ids.length) return;
        const form = new FormData();
        form.append('csrf', window.ZID_UI.csrfToken || '');
        form.append('action', 'reorder');
        form.append('order', JSON.stringify(ids));
        await fetch(`/api/firewall_rules.php?interface=${encodeURIComponent(currentIface)}`, { method: 'POST', body: form, credentials: 'same-origin' });
      };

      fwList.addEventListener('dragover', (ev) => {
        ev.preventDefault();
        const row = ev.target.closest('.fw-row');
        if (!row || row.dataset.id === draggingId) return;
        const dragging = fwList.querySelector(`.fw-row[data-id="${draggingId}"]`);
        if (!dragging) return;
        const rect = row.getBoundingClientRect();
        const shouldInsertBefore = (ev.clientY - rect.top) < rect.height / 2;
        fwList.insertBefore(dragging, shouldInsertBefore ? row : row.nextSibling);
      });

      fwList.addEventListener('drop', async (ev) => {
        ev.preventDefault();
        if (!draggingId) return;
        try {
          await reorderRules();
          fetchRules();
        } catch (err) {
          console.error(err);
        }
      });

      fwTabs.querySelectorAll('.fw-tab').forEach((tab) => {
        tab.addEventListener('click', () => {
          fwTabs.querySelectorAll('.fw-tab').forEach((el) => el.classList.remove('active'));
          tab.classList.add('active');
          currentIface = tab.dataset.iface;
          setAddInterface(currentIface);
          fetchRules();
        });
      });

      if (fwFilter) {
        fwFilter.addEventListener('input', renderRules);
      }

      if (fwAddToggle && fwAddPanel) {
        fwAddToggle.addEventListener('click', () => {
          fwAddPanel.classList.toggle('is-hidden');
        });
      }

      if (fwAddForm) {
        fwAddForm.addEventListener('submit', async (ev) => {
          ev.preventDefault();
          if (fwAddStatus) fwAddStatus.textContent = 'Salvando...';
          try {
            const form = new FormData(fwAddForm);
            form.append('csrf', window.ZID_UI.csrfToken || '');
            form.append('action', 'add');
            const res = await fetch(`/api/firewall_rules.php?interface=${encodeURIComponent(currentIface)}`, { method: 'POST', body: form, credentials: 'same-origin' });
            const json = await res.json();
            if (!res.ok || !json.ok) {
              throw new Error(json.error || 'Falha');
            }
            if (fwAddStatus) fwAddStatus.textContent = 'Salvo com sucesso.';
            fwAddForm.reset();
            setAddInterface(currentIface);
            fetchRules();
          } catch (err) {
            if (fwAddStatus) fwAddStatus.textContent = `Erro: ${err.message}`;
          }
        });
      }

      setAddInterface(currentIface);
      fetchRules();
    }

  }

  document.addEventListener('DOMContentLoaded', initWidgets);
})();
