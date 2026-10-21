# AGENTS.md

## Проект

- `Oberbot` — Telegram helpdesk/task-manager.
- Forum topic в клиентской группе является заявкой.
- Источник истины — фактический код и production state.

## Правила изменений

- Перед кодовым шагом проверять чистоту дерева.
- Изменение контракта начинать с документации, затем добавлять fail-first тесты, затем код.
- Не добавлять fallback, compatibility path и эвристики.
- Для доступа к БД использовать `losthost/db`; raw SQL через `DB::prepare()` без согласования запрещён.

## AI handoff

- Контракт интеграции задан в `AI_HANDOFF_CONTRACT.md`.
- Изменения Oberbot для AI handoff должны быть минимальными.

## Production deployment

- Сначала изменения публикуются как GitHub Release в `Datahider/Oberbot`.
- Затем в production-директории Oberbot запускается штатный `update-bot.sh <release-tag>`.
- `update-bot.sh` скачивает release archive с GitHub и копирует его содержимое поверх production-директории; `composer` и restart сервиса он не выполняет.
- Локального deploy-скрипта в репозитории нет и добавлять его не нужно.
