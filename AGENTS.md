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

## Обработка исключений

- Тип в `catch` внутри namespace должен разрешаться в существующий класс: либо полностью квалифицированное имя (`\Exception`), либо импорт через `use`.
- Неквалифицированный `catch (Exception ...)` без `use Exception;` запрещён: PHP разрешает его в `<текущий namespace>\Exception`, такой catch ничего не ловит, и открытая перед ним транзакция остаётся неоткатанной в соединении воркера.
- Проверяется тестом `tests/CatchTypesTest.php`.
- Каждый `DB::beginTransaction()` в `bot/` закрывается блоком `try`, чей `catch` ловит `\Throwable` и вызывает `DB::rollBack()`. Ошибка пишется в лог через `Bot::logThrowable()` (либо пробрасывается дальше). Проверяется тестом `tests/TransactionRollbackTest.php`.

## AI handoff

- Контракт интеграции задан в `AI_HANDOFF_CONTRACT.md`.
- Изменения Oberbot для AI handoff должны быть минимальными.

## Production deployment

- Сначала изменения публикуются как GitHub Release в `Datahider/Oberbot`.
- Затем в production-директории Oberbot запускается штатный `update-bot.sh <release-tag>`.
- `update-bot.sh` скачивает release archive с GitHub и копирует его содержимое поверх production-директории; `composer` и restart сервиса он не выполняет.
- Локального deploy-скрипта в репозитории нет и добавлять его не нужно.
