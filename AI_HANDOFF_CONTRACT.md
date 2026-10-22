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
- Timestamp передаётся в `losthost/db` как `DateTimeImmutable`; предварительное преобразование в строку запрещено.
- Timestamp, прочитанный из `losthost/db`, уже является `DateTimeImmutable` и используется без преобразования в строку.
- После успешного `/ai` новые записи не добавляются.
- Журнал и его attachment metadata удаляются после архивации заявки.
- Идентификатор Telegram message уникален внутри source chat; повторная доставка update не создаёт дубль записи.
- Сборщик является final message handler, поэтому видит первое содержательное сообщение заявки после её создания, даже если основной handler уже обработал update.

## Attachment preparation

- Telegram attachment скачивается через API и token Oberbot.
- Каждый attachment загружается в `files.ioannidis.ru`.
- В текстовый журнал записывается URL, вернутый file exchange.
- Ошибка скачивания или upload прерывает `/ai` без частично успешной отметки.
- Поддерживаются `photo` (наибольший размер), `document`, `audio`, `video`, `voice`, `animation`, `video_note` и `sticker`.
- В журнале хранятся только Telegram `file_id`, исходное имя, MIME type и размер; бинарные данные до `/ai` локально не сохраняются.

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

Локальный вызов выполняется синхронно без shell-интерпретации аргументов:

```text
php <ai_transport_submit_script> <ai_transport_config_path> <job-path>
```

Job дополнительно содержит `secret` из `ai_transport_submit_secret`. Временные job и log files удаляются после завершения вызова как при успехе, так и при ошибке.

## Runtime parameters

Обязательные DB bot parameters:

- `ai_file_exchange_base_url`
- `ai_file_exchange_token`
- `ai_transport_submit_script`
- `ai_transport_config_path`
- `ai_transport_submit_secret`
- `ai_timer_subject`

Параметр `ai_temp_dir` необязателен; значение по умолчанию `/home/web/tmp` применяется только в development/tests. Production обязан задать собственный writable path.

## Ticket card

- Основная карточка заявки показывает накопленное время AI отдельной строкой `ИИ: HH:MM:SS`.
- AI time — сумма закрытых timer events заявки, принадлежащих timer subject из `ai_timer_subject`.
- Создание AI timer event само по себе не обновляет карточку. Новое значение появляется при следующем штатном обновлении карточки заявки.

## Handoff identity

- Успешная передача уникальна по `ticket_id`.
- Сохраняются hidden topic ID и source chat ID, необходимые для учёта времени.
- Синхронизация messages, status и lifecycle между заявкой и hidden topic запрещена.
