# CODEX — ZID UI (pfSense) — FULL SPEC (Opção B: UI em outra porta + reaproveita stack/auth do pfSense)

## 0) Objetivo
Implementar uma “Nova UI” do pfSense chamada **ZID UI**, rodando em **outra porta (default 8444)**, mas:
- Reaproveitando **nginx + php-fpm** já existentes no pfSense (stack do webConfigurator)
- Reaproveitando **autenticação/sessão/permissões** do pfSense (mesmo login, sem auth paralela)
- Mantendo o **webConfigurator original intacto** (fallback sempre disponível)
- Sem depender de CDN/Internet para libs (Leaflet local, etc.)

Além disso:
- UI deve seguir **paleta e estilo** do link (referência visual):
  https://firewall-canvas--guilhermemulato.replit.app/
- Dashboard deve ter **Mapa (Leaflet)**
- Dashboard deve ter **widgets que atualizam automaticamente** (sem F5), como vários widgets independentes.
  - MVP: polling por widget (2–10s)
  - (Opcional fase 2): SSE (Server-Sent Events)

E ainda:
- Criar `install.sh` que configura tudo (nginx include, rc, permissões, etc.)
- Criar `update.sh` que baixa um tar.gz do S3 e atualiza a UI de forma segura (backup + deploy atômico)
  URL fixa do pacote:
  https://s3.soulsolucoes.com.br/soul/portal/zid-canvas-latest.tar.gz
- Colocar um **botão “Update”** no dashboard que inicia o processo chamando `update.sh`
  - Esse botão precisa ser **admin-only + CSRF + sudoers restrito**, para não virar brecha.

**ALVO:** pfSense (FreeBSD). Use /bin/sh, fetch, service.

---

## 1) Restrições e Regras de Ouro (SEGURANÇA / MANUTENÇÃO)
1) **NUNCA** mexer no core do webConfigurator original além do necessário para incluir um bloco nginx adicional.
2) Evitar dependências externas:
   - Sem CDN para Leaflet/Chart/etc. Tudo deve ser servido localmente em `assets/js/vendor/...`.
3) **Autenticação pfSense obrigatória** em TODAS as páginas e endpoints API:
   - se não logado, redirecionar para login do pfSense
   - respeitar permissões (admin-only onde for crítico)
4) **CSRF obrigatório** para qualquer ação sensível (Update, Start/Stop serviços no futuro).
5) Update deve ser seguro:
   - permitir somente executar `/usr/local/etc/zid-ui/update.sh` via sudoers
   - nenhum “sudo sh -c …”
6) Performance:
   - widgets polling com intervalos razoáveis
   - endpoints leves e cache curto
7) Tudo idempotente e reversível:
   - `install.sh` rodar N vezes sem quebrar
   - `update.sh` com lock e deploy atômico
   - se a nova UI falhar, UI original continua funcionando

---

## 2) Layout / Estilo Visual (Canvas Reference)
**MANDATÓRIO:** UI deve seguir paleta e estética do canvas:
https://firewall-canvas--guilhermemulato.replit.app/

Tarefas:
- Abrir o link e replicar:
  - fundo (bg), painéis, cards, bordas, sombras
  - tipografia, tamanhos, espaçamentos
  - botões, badges, inputs
  - sidebar + topbar
- Implementar CSS com variáveis:
  ```css
  :root{
    --bg: ...;
    --panel: ...;
    --panel2: ...;
    --border: ...;
    --text: ...;
    --muted: ...;
    --primary: ...; /* accent */
    --success: ...;
    --warning: ...;
    --danger: ...;
    --radius: 14px;
    --shadow: ...;
  }
````

* Tema dark moderno (NGFW), responsivo.

---

## 3) Estrutura de arquivos (CRIAR)

### Web root

`/usr/local/www/zid-ui/`

* `index.php`
* `README.md`
* `app/`

  * `bootstrap.php`  (carrega ambiente pfSense, config, sessão)
  * `router.php`     (whitelist de rotas)
  * `auth.php`       (require_login, require_admin)
  * `csrf.php`       (token e validação)
  * `layout.php`     (render base HTML)
  * `response.php`   (helpers JSON ok/error)
  * `metrics.php`    (coleta CPU/MEM/DISK/interfaces)
  * `services.php`   (status serviços)
  * `geo.php`        (MVP: dataset fake, futuro: plugar logs geo)
  * `audit.php`      (gravar audit_log em sqlite)
* `pages/`

  * `dashboard.php`
  * `firewall_rules.php`
  * `system_settings.php`
  * `zid_packages.php`
* `api/`

  * `health.php`
  * `metrics.php`
  * `log_tail.php`
  * `map_events.php`
  * `do_update.php`  (botão Update -> chama update.sh)
* `assets/`

  * `css/app.css`
  * `js/app.js`
  * `js/widgets.js`
  * `js/map.js`
  * `js/vendor/leaflet/` (leaflet.js, leaflet.css, images)

### Dados e config

* `/var/db/zid-ui/`

  * `zid-ui.db`
  * `cache/`
  * `locks/`
  * `backups/`
* `/var/log/zid-ui.log`
* `/usr/local/etc/zid-ui/`

  * `zid-ui.conf`
  * `nginx/zid-ui.conf`
  * `update.sh`
* `/usr/local/etc/rc.d/zid-ui` (rc script)
* `/usr/local/etc/sudoers.d/zid-ui` (sudoers restrito)
* (opcional) `/usr/local/sbin/zid-ui-ensure-include` (helper)

---

## 4) NGINX: server block em outra porta reaproveitando o PHP-FPM do pfSense

### 4.1 Descobrir upstream fastcgi do webConfigurator (NÃO chutar)

* Parse do nginx config efetivo do pfSense (ex.: `/var/etc/nginx-webConfigurator.conf` ou equivalente).
* Localizar o `fastcgi_pass` usado pelo webConfigurator.
* Reutilizar exatamente o mesmo no server block da ZID UI.

### 4.2 Arquivo de include persistente

Criar:
`/usr/local/etc/zid-ui/nginx/zid-ui.conf` contendo um `server { ... }`

Regras do server:

* listen 8444 ssl http2 (porta configurável)
* root `/usr/local/www/zid-ui`
* index `index.php`
* deny arquivos sensíveis (.inc, .xml, .db, .sqlite, .log)
* `try_files` -> `/index.php?$query_string`
* `location ~ \.php$` -> `fastcgi_pass` do pfSense (descoberto dinamicamente)

TLS:

* Ideal: reutilizar cert do webConfigurator
* Se não possível: permitir setar cert/key em `/usr/local/etc/zid-ui/zid-ui.conf`

### 4.3 Persistência do include (pfSense regenera configs)

Como alguns arquivos em `/var/etc` são regenerados, implemente mecanismo robusto:

* `zid-ui` rc script, no `start_precmd`:

  * garante que exista uma linha:
    `include /usr/local/etc/zid-ui/nginx/zid-ui.conf;`
  * em algum arquivo nginx que seja carregado pelo webConfigurator.
* Se não houver arquivo “estável” para include, aplicar patch idempotente no arquivo gerado e:

  * re-aplicar a cada boot/start/reload
  * sempre evitar duplicar
* Depois `service nginx reload` (ou restart se necessário).

**IMPORTANTE:** o mecanismo de include precisa funcionar após:

* reboot
* salvar configs da GUI
* updates do pfSense
  Ele deve ser tolerante e logar em `/var/log/zid-ui.log`.

---

## 5) Auth/Sessão/Permissões pfSense (MANDATÓRIO)

Em TODAS as páginas (`pages/*`) e endpoints (`api/*`):

* carregar includes do pfSense necessários para:

  * sessão e usuário atual
  * acesso ao `$config` (config.xml)
  * funções de validação e write_config
* exigir login:

  * se não logado -> redirect login webConfigurator
* admin-only:

  * `do_update.php` deve ser admin-only
* CSRF:

  * `do_update.php` exige CSRF token válido

**Observação:** cookies funcionam em outra porta no mesmo host, então deve reaproveitar sessão sem re-login.

---

## 6) Dashboard + Widgets vivos (sem F5)

### 6.1 Dashboard obrigatório

`/pages/dashboard.php`

* Top KPI cards (CPU/MEM/DISK, interfaces, serviços)
* Uma área de “Widgets” em grid (cards)
* Um mapa Leaflet (div #map) grande
* Um botão “Update” visível (apenas para admin)
* Exibir “last updated” por widget

### 6.2 Atualização automática (MVP: polling por widget)

Implementar `assets/js/widgets.js` com:

* lista de widgets (cada um com endpoint e intervalo)
* `AbortController` para evitar empilhar requests
* backoff em falha
* update de UI (loading/offline)

Exemplo de intervalos:

* health: 2s
* metrics: 5s
* map_events: 5–10s
* log_tail: 10s

### 6.3 Opcional (fase 2): SSE

Se implementar SSE:

* endpoint `api/stream.php` (text/event-stream)
* EventSource no JS
* se for instável, manter polling como default

---

## 7) Mapa Leaflet (MANDATÓRIO)

* Leaflet local em `assets/js/vendor/leaflet`
* `assets/js/map.js`:

  * init map
  * tiles configurável (e fallback sem tiles se sem internet)
  * markers atualizados por polling (diff: add/update/remove)
* Endpoint:

  * `GET /api/map_events.php?since=...` retorna:

    ```json
    {
      "ts": 1700000000,
      "events": [
        {"id":"...", "lat":-23.55, "lng":-46.63, "label":"BR / ASN 15169", "count":12, "country":"BR", "severity":"warning"},
        ...
      ]
    }
    ```
* MVP pode usar dataset fake/placeholder, mas estrutura deve estar pronta para plugar:

  * logs do zid-proxy / zid-geolocation no futuro

---

## 8) API endpoints (MVP)

* `GET /api/health.php` -> ok + timestamp + user
* `GET /api/metrics.php` -> cpu/mem/disk/interfaces/services
* `GET /api/log_tail.php?lines=50` -> últimas linhas (somente leitura)
* `GET /api/map_events.php?since=ts` -> eventos pro mapa
* `POST /api/do_update.php` -> dispara update (admin + CSRF)

Todos os endpoints:

* exigem login
* retornam JSON padronizado:

  * `{ "ok": true, "data": ... }`
  * `{ "ok": false, "error": "...", "details": ... }`

---

## 9) SQLite (preferências + auditoria)

Criar DB:
`/var/db/zid-ui/zid-ui.db`

Tabelas:

* `ui_preferences(user TEXT PRIMARY KEY, theme TEXT, sidebar_collapsed INTEGER, refresh_profile TEXT, updated_at INTEGER)`
* `audit_log(id INTEGER PRIMARY KEY AUTOINCREMENT, ts INTEGER, user TEXT, action TEXT, route TEXT, payload_json TEXT)`

Use sqlite apenas para metadados da UI / auditoria (não duplicar config.xml).

---

## 10) install.sh e update.sh (MANDATÓRIO)

### 10.1 install.sh (setup completo)

* Criar diretórios
* Criar config `/usr/local/etc/zid-ui/zid-ui.conf` se não existir
* Garantir `/usr/local/etc/zid-ui/nginx/zid-ui.conf`
* Instalar rc script `/usr/local/etc/rc.d/zid-ui`
* Criar sudoers `/usr/local/etc/sudoers.d/zid-ui` permitindo apenas:
  `www ALL=(root) NOPASSWD: /usr/local/etc/zid-ui/update.sh`
* Habilitar `zid_ui_enable="YES"` em `/etc/rc.conf.local` se necessário
* `service zid-ui start` (que garante include e recarrega nginx)

### 10.2 update.sh (S3 -> backup -> deploy atômico)

URL:
[https://s3.soulsolucoes.com.br/soul/portal/zid-canvas-latest.tar.gz](https://s3.soulsolucoes.com.br/soul/portal/zid-canvas-latest.tar.gz)

Requisitos update:

* usar `fetch` (FreeBSD) para baixar
* extrair em staging
* validar estrutura esperada:

  * preferir `www/zid-ui/...` OU aceitar `zid-ui/...`
* backup do diretório atual antes de trocar
* deploy atômico:

  * copiar para temp
  * swap (rename)
* lock file para evitar updates simultâneos
* log em `/var/log/zid-ui.log`
* `service nginx reload` no final

---

## 11) Botão “Update” no Dashboard

* No `dashboard.php`, criar botão “Update”
* Só aparece para admin (ou aparece desabilitado com tooltip)
* Ao clicar:

  * faz POST para `/api/do_update.php`
  * exibe output em um `<pre id="update-output">`
  * opcional: reload após sucesso

Segurança:

* `/api/do_update.php`:

  * require_login
  * require_admin
  * require_csrf
  * executa: `sudo /usr/local/etc/zid-ui/update.sh`
  * loga auditoria

---

## 12) Config `zid-ui.conf` (exemplo)

Formato key=value (ini simples):

* port=8444
* tiles_url=https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png
* refresh_default_seconds=5
* enable_sse=false
* allow_from_lan_only=true
* update_url=[https://s3.soulsolucoes.com.br/soul/portal/zid-canvas-latest.tar.gz](https://s3.soulsolucoes.com.br/soul/portal/zid-canvas-latest.tar.gz)

---

## 13) CHECKLIST de testes (OBRIGATÓRIO)

1. webConfigurator original na porta original OK
2. [https://HOST:8444](https://HOST:8444) abre e exige login se não logado
3. logado no pfSense -> abre sem relogar
4. dashboard atualiza widgets e mapa automaticamente sem F5
5. botão Update:

   * só admin
   * CSRF obrigatório
   * executa update.sh via sudoers restrito
6. desligar zid-ui -> webConfigurator original continua OK
7. reboot -> zid-ui volta e nginx include continua funcionando
8. sem internet -> mapa funciona em “modo sem tiles” (fundo neutro) sem quebrar layout

---

# IMPLEMENTAÇÃO — ARQUIVOS PRONTOS (VOCÊ DEVE GERAR)

## A) /usr/local/etc/zid-ui/update.sh  (EXATO)

```sh
#!/bin/sh
set -eu

APP_NAME="zid-ui"
WWW_ROOT="/usr/local/www/zid-ui"
ETC_DIR="/usr/local/etc/zid-ui"
CONF_FILE="${ETC_DIR}/zid-ui.conf"
LOG_FILE="/var/log/zid-ui.log"
LOCK_FILE="/var/run/zid-ui-update.lock"

cfg_get() {
  key="$1"
  awk -F= -v k="$key" 'BEGIN{v=""} $0 ~ "^[[:space:]]*"k"=" {sub("^[^=]*=",""); gsub("\r",""); v=$0} END{print v}' "${CONF_FILE}" 2>/dev/null || true
}

URL="$(cfg_get update_url)"
[ -n "${URL}" ] || URL="https://s3.soulsolucoes.com.br/soul/portal/zid-canvas-latest.tar.gz"

TMP_DIR="/tmp/${APP_NAME}-update.$$"
ARCHIVE="${TMP_DIR}/pkg.tar.gz"
STAGE="${TMP_DIR}/stage"
BACKUP_BASE="/var/db/zid-ui/backups"
TS="$(date +%Y%m%d-%H%M%S)"
BACKUP_DIR="${BACKUP_BASE}/${TS}"

mkdir -p "${BACKUP_BASE}"

log() {
  echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*" >> "${LOG_FILE}"
}

cleanup() {
  rm -rf "${TMP_DIR}" 2>/dev/null || true
  rm -f "${LOCK_FILE}" 2>/dev/null || true
}
trap cleanup EXIT INT TERM

if [ -e "${LOCK_FILE}" ]; then
  log "Update already running (lock exists)."
  echo "Update already running."
  exit 1
fi
touch "${LOCK_FILE}"
chmod 600 "${LOCK_FILE}"

log "Starting update from ${URL}"

mkdir -p "${TMP_DIR}" "${STAGE}"

fetch -q -o "${ARCHIVE}" "${URL}"

if [ ! -s "${ARCHIVE}" ]; then
  log "ERROR: downloaded archive is empty"
  exit 1
fi

tar -xzf "${ARCHIVE}" -C "${STAGE}"

NEW_ROOT=""
if [ -d "${STAGE}/www/zid-ui" ]; then
  NEW_ROOT="${STAGE}/www/zid-ui"
elif [ -d "${STAGE}/zid-ui" ]; then
  NEW_ROOT="${STAGE}/zid-ui"
else
  log "ERROR: archive missing expected root folder (www/zid-ui or zid-ui)"
  exit 1
fi

mkdir -p "${BACKUP_DIR}"
if [ -d "${WWW_ROOT}" ]; then
  log "Backing up ${WWW_ROOT} to ${BACKUP_DIR}/www/${APP_NAME}.tar"
  mkdir -p "${BACKUP_DIR}/www"
  (cd /usr/local/www && tar -cf "${BACKUP_DIR}/www/${APP_NAME}.tar" "${APP_NAME}") || true
fi

TMP_TARGET="/usr/local/www/.${APP_NAME}.new.${TS}"
rm -rf "${TMP_TARGET}" 2>/dev/null || true
mkdir -p "${TMP_TARGET}"

( cd "${NEW_ROOT}" && tar -cf - . ) | ( cd "${TMP_TARGET}" && tar -xpf - )

if [ ! -f "${TMP_TARGET}/index.php" ]; then
  log "ERROR: new build missing index.php"
  exit 1
fi

OLD_TARGET="/usr/local/www/.${APP_NAME}.old.${TS}"
if [ -d "${WWW_ROOT}" ]; then
  mv "${WWW_ROOT}" "${OLD_TARGET}"
fi
mv "${TMP_TARGET}" "${WWW_ROOT}"

log "Deployed new version to ${WWW_ROOT}"

service nginx reload >/dev/null 2>&1 || service nginx restart >/dev/null 2>&1 || true

log "Update finished OK"
echo "OK"
```

## B) /usr/local/www/zid-ui/install.sh  (EXATO, e idempotente)

```sh
#!/bin/sh
set -eu

APP_NAME="zid-ui"
WWW_ROOT="/usr/local/www/zid-ui"
DB_DIR="/var/db/zid-ui"
LOG_FILE="/var/log/zid-ui.log"
ETC_DIR="/usr/local/etc/zid-ui"
NGINX_DIR="${ETC_DIR}/nginx"
NGINX_CONF="${NGINX_DIR}/zid-ui.conf"
RC_SCRIPT="/usr/local/etc/rc.d/zid-ui"
SUDOERS_FILE="/usr/local/etc/sudoers.d/zid-ui"
UPDATE_SH="${ETC_DIR}/update.sh"
CONFIG_FILE="${ETC_DIR}/zid-ui.conf"
PORT_DEFAULT="8444"

echo "[+] Installing ${APP_NAME}..."

mkdir -p "${WWW_ROOT}" "${DB_DIR}/cache" "${DB_DIR}/locks" "${DB_DIR}/backups" "${NGINX_DIR}" "${ETC_DIR}"

touch "${LOG_FILE}"
chmod 600 "${LOG_FILE}"

if [ ! -f "${CONFIG_FILE}" ]; then
cat > "${CONFIG_FILE}" <<EOF
# zid-ui config
port=${PORT_DEFAULT}
tiles_url=https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png
refresh_default_seconds=5
enable_sse=false
allow_from_lan_only=true
update_url=https://s3.soulsolucoes.com.br/soul/portal/zid-canvas-latest.tar.gz
EOF
chmod 600 "${CONFIG_FILE}"
fi

if [ ! -f "${UPDATE_SH}" ]; then
  echo "[-] Missing ${UPDATE_SH}. Put update.sh in place first."
  exit 1
fi
chmod 700 "${UPDATE_SH}"

if [ ! -f "${NGINX_CONF}" ]; then
cat > "${NGINX_CONF}" <<'EOF'
# zid-ui nginx include
# IMPORTANT: Codex must generate a full server { } using the discovered fastcgi_pass from webConfigurator.
# This file must be included by nginx config (ensure-include mechanism).
EOF
fi

if [ ! -f "${RC_SCRIPT}" ]; then
cat > "${RC_SCRIPT}" <<'EOF'
#!/bin/sh
#
# PROVIDE: zid-ui
# REQUIRE: LOGIN nginx php-fpm
# KEYWORD: shutdown
#
. /etc/rc.subr

name="zid_ui"
rcvar="zid_ui_enable"

load_rc_config $name
: ${zid_ui_enable:="NO"}

command="/usr/sbin/daemon"
command_args="-p /var/run/zid-ui.pid -t zid-ui -o /var/log/zid-ui.log /bin/sleep 2147483647"

start_precmd="zid_ui_precmd"

ZID_UI_ETC="/usr/local/etc/zid-ui"
NGINX_INCLUDE="${ZID_UI_ETC}/nginx/zid-ui.conf"
LOG="/var/log/zid-ui.log"

zid_ui_precmd() {
  echo "[zid-ui] ensuring nginx include..." >> "${LOG}"

  if [ ! -f "${NGINX_INCLUDE}" ]; then
    echo "[zid-ui] ERROR: missing ${NGINX_INCLUDE}" >> "${LOG}"
    return 1
  fi

  # Codex MUST implement an idempotent include injection.
  # Suggestion: create /usr/local/sbin/zid-ui-ensure-include and call it here:
  # /usr/local/sbin/zid-ui-ensure-include >> "${LOG}" 2>&1 || return 1

  service nginx reload >/dev/null 2>&1 || service nginx restart >/dev/null 2>&1 || true
}

run_rc_command "$1"
EOF
chmod 755 "${RC_SCRIPT}"
fi

if [ ! -f "${SUDOERS_FILE}" ]; then
cat > "${SUDOERS_FILE}" <<EOF
www ALL=(root) NOPASSWD: ${UPDATE_SH}
EOF
chmod 440 "${SUDOERS_FILE}"
fi

if [ ! -f /etc/rc.conf.local ]; then
  touch /etc/rc.conf.local
fi
if ! grep -q '^zid_ui_enable=' /etc/rc.conf.local; then
  echo 'zid_ui_enable="YES"' >> /etc/rc.conf.local
fi

service zid-ui start >/dev/null 2>&1 || true

echo "[+] Done."
echo "    UI:     ${WWW_ROOT}"
echo "    Config: ${CONFIG_FILE}"
echo "    Update: ${UPDATE_SH}"
```

## C) Endpoint de Update (admin-only + CSRF) — /usr/local/www/zid-ui/api/do_update.php

Você deve implementar usando as libs de auth/CSRF do pfSense (reaproveitar padrão do pfSense).
Comportamento:

* POST only
* require_login
* require_admin
* require_csrf
* executa `sudo /usr/local/etc/zid-ui/update.sh`
* retorna JSON com ok/error e output

Pseudo-código esperado:

* exec via `exec()` ou `proc_open`
* log audit_log no sqlite com rc e tail do output (até 2000 chars)
* ao sucesso, opcionalmente recomendar reload da página

## D) Botão Update no Dashboard

No `pages/dashboard.php`:

* renderizar botão `Update` somente para admin
* `<pre id="update-output">` para mostrar output
  No JS:
* função `triggerUpdate()` -> POST /api/do_update.php + csrf_token
* mostrar progress
* ao fim, `location.reload()` opcional

---

# IMPLEMENTAÇÃO — REQUIREMENTS TÉCNICOS IMPORTANTES (VOCÊ DEVE RESOLVER)

## 1) Como descobrir `fastcgi_pass` do pfSense

Você deve implementar uma rotina (shell ou PHP) que:

* encontre o arquivo nginx efetivo do webConfigurator (provavelmente em /var/etc/...)
* extraia o `fastcgi_pass` e/ou upstream
* injete isso no `server { }` do `/usr/local/etc/zid-ui/nginx/zid-ui.conf`

## 2) Como garantir include nginx de forma tolerante

Você deve implementar `ensure-include` idempotente:

* localizar ponto seguro de include
* inserir `include /usr/local/etc/zid-ui/nginx/zid-ui.conf;` apenas uma vez
* reexecutar no boot e no start do serviço
* logar tudo em /var/log/zid-ui.log

---

# ENTREGÁVEIS FINAIS (VOCÊ DEVE PRODUZIR)

1. UI completa em /usr/local/www/zid-ui (PHP/CSS/JS)
2. Leaflet local (sem CDN)
3. Dashboard com mapa + widgets live
4. API endpoints funcionando
5. install.sh e update.sh conforme acima
6. botão Update disparando update.sh com segurança
7. README.md com:

   * como instalar (sh install.sh)
   * como atualizar manualmente (sudo update.sh)
   * como depurar (logs)
   * como desabilitar/rollback
8. Checklist de testes executável

FIM.
