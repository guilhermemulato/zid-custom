# ZID Canvas Theme - pfSense CE 2.8.1

Tema dark premium para pfSense CE 2.8.1 com design moderno, tipografia customizada e componentes visuais avançados.

![Version](https://img.shields.io/badge/version-0.2.7-blue)
![pfSense](https://img.shields.io/badge/pfSense-2.8.1-orange)
![License](https://img.shields.io/badge/license-MIT-green)

## 🎨 Características

### Design System
- ✅ **Paleta dark premium** com cores modernas e contrastantes
- ✅ **Tipografia local**: Plus Jakarta Sans + JetBrains Mono
- ✅ **Grid background sutil** com efeito radial
- ✅ **Scrollbar customizada** integrada ao tema
- ✅ **Componentes completos**: panels, tabelas, botões, formulários, alerts, modals
- ✅ **Responsivo**: mobile, tablet e desktop

### ZID UI (porta 8444)
- ✅ **UI separada** com nginx/php-fpm do pfSense
- ✅ **Autenticação pfSense** reaproveitada
- ✅ **Widgets vivos** via polling
- ✅ **Mapa Leaflet local**
- ✅ **Botão Update seguro** (admin + CSRF + sudoers)

## 📦 Instalação

### Pré-requisitos
- pfSense CE 2.8.1
- Acesso SSH ao firewall
- Permissões de root

### Instalação da ZID UI

Copie o bundle para qualquer diretório no pfSense, extraia e rode o `install.sh` que já copia tudo para os lugares corretos:

```bash
# Copiar bundle para o pfSense
scp zid-cavas-latest.tar.gz root@<IP-PFSENSE>:/tmp/

# SSH no pfSense
ssh root@<IP-PFSENSE>

# Extrair bundle (em qualquer lugar)
mkdir -p /tmp/zid-ui-install
cd /tmp/zid-ui-install

tar -xzf /tmp/zid-cavas-latest.tar.gz

# Rodar instalador
cd zid-ui
chmod +x etc/zid-ui/install.sh
./etc/zid-ui/install.sh
```

Acesso:
```
https://<IP-PFSENSE>:8444
```

### Atualização manual

```bash
sudo /usr/local/etc/zid-ui/update.sh
```

### Atualização via dashboard
- Clique no botão **Update** (admin only)

## 🔧 Depuração

### Logs
```bash
# Log geral
cat /var/log/zid-ui.log

# Log de erros PHP da UI
cat /var/log/nginx/zid-ui.php.log
```

### Erros de login
- Verifique se a sessão do pfSense está ativa
- Limpe cookies e tente novamente

## 🔁 Desabilitar / Rollback

```bash
# Desabilitar UI (remove include do nginx)
rm -f /usr/local/etc/zid-ui/nginx/zid-ui.conf
service nginx reload

# Voltar versão antiga (se backup existir)
ls /var/db/zid-ui/backups/
```

## ✅ Checklist de testes

1. webConfigurator original na porta original OK
2. https://HOST:8444 abre e exige login se não logado
3. logado no pfSense -> abre sem relogar
4. dashboard atualiza widgets e mapa automaticamente sem F5
5. botão Update:
   - só admin
   - CSRF obrigatório
   - executa update.sh via sudoers restrito
6. desligar zid-ui -> webConfigurator original continua OK
7. reboot -> zid-ui volta e nginx include continua funcionando
8. sem internet -> mapa funciona em “modo sem tiles”

## 📝 Changelog

Veja `CHANGELOG.md`.

## 📄 Licença

MIT License - Livre para uso pessoal e comercial.
