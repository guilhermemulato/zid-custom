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
          const res = await fetch('/api/do_update.php', { method: 'POST', body: form });
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
          const res = await fetch('/api/service_action.php', { method: 'POST', body: form });
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
        const res = await fetch(`/api/dhcp_config.php?interface=${encodeURIComponent(iface)}`);
        const json = await res.json();
        if (json.ok && json.data) {
          dhcpForm.querySelector('input[name="range_from"]').value = json.data.range_from || '';
          dhcpForm.querySelector('input[name="range_to"]').value = json.data.range_to || '';
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
          const res = await fetch('/api/dhcp_config.php', { method: 'POST', body: form });
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

  }

  document.addEventListener('DOMContentLoaded', initWidgets);
})();
