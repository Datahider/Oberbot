# Oberbot AI Handoff Contract

## Role

Oberbot собирает журнал заявки и по команде `/ai` однократно передаёт её в отдельный AI transport.

## Ticket log

- Журнал собирается с момента создания заявки.
- Для заявки, созданной до включения функции, допустим неполный журнал.
- В журнал попадают только содержательные Telegram messages клиентов и агентов.
- Telegram commands, bot messages и service messages не попадают в журнал.
- Правки и удаления Telegram messages игнорируются.
- Запись содержит timestamp, роль `Клиент` или `Агент`, text/caption и метаданные attachments.
- После успешного `/ai` новые записи не добавляются.
- Журнал и его attachment metadata удаляются после архивации заявки.

## Attachment preparation

- Telegram attachment скачивается через API и token Oberbot.
- Каждый attachment загружается в `files.ioannidis.ru`.
- В текстовый журнал записывается URL, вернутый file exchange.
- Ошибка скачивания или upload прерывает `/ai` без частично успешной отметки.

## `/ai`

- `/ai` работает только в ticket forum topic.
- Команда разрешена только пользователю с ролью agent. Остальные права и диагностика задаются существующим `AbstractAuthCommand`.
- Первая успешная команда формирует log file и синхронно передаёт его AI transport.
- Ответ при успехе: `Ок`.
- Повтор после успеха не вызывает AI transport и отвечает `Повторная команда /ai запрещена`.
- При ошибке ответ: `Ошибка выполнения команды`.
- Ошибка не запрещает повторную попытку.

## Local handoff request

Oberbot передаёт AI transport JSON job и локальный log file. Job содержит:

- `ticket_id`
- `source_chat_id`
- `source_thread_id`
- `ticket_title`
- `log_path`

AI transport возвращает success только после создания/восстановления скрытой темы, публикации log document и приёма initial ingress в Router.

## Handoff identity

- Успешная передача уникальна по `ticket_id`.
- Сохраняются hidden topic ID и source chat ID, необходимые для учёта времени.
- Синхронизация messages, status и lifecycle между заявкой и hidden topic запрещена.
