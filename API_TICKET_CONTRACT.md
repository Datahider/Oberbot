# Oberbot API Ticket Contract

## Role

Внешняя система (например, 1С) создаёт заявку через `POST https://api.oberdesk.ru/v1/tickets`.
API только принимает запрос и ставит его в очередь Oberbot. Заявку создаёт Oberbot в своём
фоновом процессе, в полном контексте бота: тема форума, trackers (`TicketCreating`,
`TicketCustomerLink` и т. д.) и уведомления работают так же, как для заявки, созданной вручную.

## Data

### `apikey` (`[apikey]`)

| Поле | Тип | Значение |
|---|---|---|
| `id` | BIGINT, PK | |
| `secret` | VARCHAR(64), UNIQUE | Секрет, передаваемый в `Authorization: Bearer <secret>`. |
| `user_id` | BIGINT | Telegram ID пользователя, от имени которого создаются заявки (автор и клиент заявки). |
| `comment` | VARCHAR(255) | Назначение ключа. |

### `chat_apikey` (`[chat_apikey]`)

Связь ключа с группой. `permissions` — `SET("report","create_ticket")`.
Создавать заявку ключ может только в группе, для которой у него есть строка с `create_ticket`.
Строка для пары ключ + группа уникальна.

### `api_ticket_request` (`[api_ticket_request]`)

| Поле | Тип | Значение |
|---|---|---|
| `id` | BIGINT, PK | `request_id`, возвращаемый API. |
| `apikey_id` | BIGINT | Ключ, которым создан запрос. |
| `chat_id` | BIGINT | Группа заявки. |
| `user_id` | BIGINT | `apikey.user_id` на момент запроса. |
| `title` | VARCHAR(128) | Заголовок заявки. |
| `text` | TEXT | Первое сообщение заявки. |
| `type` | INT | Тип заявки, константа `topic::TYPE_*`. |
| `created` | DATETIME | Время приёма запроса. |
| `ticket_id` | BIGINT NULL | Созданная заявка. |
| `error` | VARCHAR(500) NULL | Текст ошибки создания. |

Таблицы `apikey`, `chat_apikey`, `api_ticket_request` создаются при старте Oberbot (`initDataStructure`).

## Queue

API после записи `api_ticket_request` создаёт `DBPendingJob` (`[telle_pending_jobs]`):
`start_time` — текущий момент, `start_in_background` — `true`,
`job_class` — `losthost\Oberbot\background\CreateTicketFromApi`, `job_args` — `request_id`.

## `CreateTicketFromApi`

- Загружает `api_ticket_request` по `job_args`.
- Если у запроса уже заполнен `ticket_id`, бросает исключение: повторное создание запрещено.
- Создаёт заявку `ActionCreateTicket::do(null, chat_id, user_id, title, [text], type)`.
- Тип задаётся при создании: `ticket::create(..., type)` записывает его в первой же вставке. Последующий `setType()` не используется: его трекер требует `Bot::$user`.
- `ActionCreateTicket::do` и `ticket::create` без `type` создают `TYPE_REGULAR_TASK`, как раньше.
- Фоновый процесс не имеет `Bot::$chat` и `Bot::$user`: код создания заявки не должен к ним обращаться, если группа передана явно (в частности, `sendMessage()` с явным `chat_id`).
- Записывает `ticket_id` созданной заявки.
- Любая ошибка записывается в `error` (до 500 символов) и пробрасывается дальше. Повтора нет.
- Логика вынесена в `ApiTicketRequestProcessor` с интерфейсами хранилища и создателя заявки; фоновый процесс только связывает их с БД и `ActionCreateTicket`.
