# Itransition - Symfony pricing task environment

## Requirements

- Docker
- docker-compose (v1.29+)
- Make

## Stack

- PHP 8.4-FPM
- Nginx
- Symfony 7.4
- SQLite (`var/data/app.db`)
- PHPUnit, PHP-CS-Fixer, PHPStan, Xdebug

## Quick start

```bash
make build
make up
make install
```

App: http://localhost:8088

## Make targets

- `make up` / `make down` / `make restart`
- `make shell` - bash as user `app`
- `make install` / `make composer ARGS="..."`
- `make console ARGS="..."`
- `make cs` / `make cs-fix`
- `make phpstan`
- `make test` / `make test-coverage`
- `make logs`
- `make cache-clear`
- `make permissions`

## Xdebug

Set `XDEBUG_MODE=debug` in `.env` or when starting containers. Client host: `host.docker.internal`, port `9003`, IDE key `PHPSTORM`, server name `itransition`.

Coverage:

```bash
make test-coverage
```

Report: `var/coverage/index.html`

## Notes

- Containers run PHP-FPM pool as user `app` (UID/GID from host via `HOST_UID` / `HOST_GID`).
- Project files are bind-mounted; `vendor/` and `var/` stay on the host after restart.
- If recreate fails with `ContainerConfig`, run `make down` then `make up`.
