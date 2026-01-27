# Plan

Vamos implementar a ZID UI em porta separada reutilizando nginx/php-fpm e auth do pfSense, seguindo a referência visual, com endpoints, widgets live, mapa e scripts de instalação/atualização. O plano abaixo separa por fases para facilitar desenvolvimento e testes incrementais.

## Scope
- In: UI em /usr/local/www/zid-ui, auth/CSRF pfSense, nginx include persistente, endpoints API, mapa Leaflet local, scripts install/update, botão Update seguro, checklist de testes.
- Out: Alterações no core do webConfigurator além de include, integração real de logs geo (MVP com dados fake), SSE (opcional fase 2).

## Action items
[x] Fase 1 — Descoberta e base: mapear configs do nginx do pfSense para achar `fastcgi_pass`, definir estrutura de pastas e arquivos base (app/, pages/, api/, assets/) e criar layout/roteador mínimo com auth obrigatória.
[x] Fase 2 — Nginx + bootstrap: implementar `zid-ui.conf` do nginx com `server {}` na porta 8444 e o mecanismo idempotente de include (rc + ensure-include), validando reload sem quebrar o webConfigurator.
[x] Fase 3 — Core PHP: implementar `app/bootstrap.php`, `auth.php`, `csrf.php`, `response.php`, `router.php`, e integração com sessão/config do pfSense; garantir JSON padrão nos endpoints.
[x] Fase 4 — Dashboard e widgets: construir `pages/dashboard.php`, CSS/JS base, widgets com polling (health/metrics/log_tail/map_events) e UI de estados (loading/offline).
[x] Fase 5 — Mapa Leaflet: adicionar assets locais do Leaflet, `map.js` com init e atualização por eventos, fallback sem tiles, e endpoint `api/map_events.php` com dataset MVP.
[x] Fase 6 — Update seguro: implementar `api/do_update.php` (admin + CSRF + sudoers) e botão Update com output; criar `install.sh` e `update.sh` conforme spec, além de sudoers e rc script.
[x] Fase 7 — Persistência e auditoria: criar sqlite (`ui_preferences`, `audit_log`) e registrar ações críticas (ex.: update) com payload/rc.
[x] Fase 8 — Documentação e testes: atualizar README com instalação/atualização/debug/rollback e executar checklist obrigatório (login, porta 8444, polling, update seguro, reboot, sem internet).
