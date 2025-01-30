<?php

use losthost\Oberbot\controller\action\ActionInvoice;

$translations = [
    "Can't link ticket's agent as a customer" => 'Не могу присоединить вас как пользователя, т.к. вы являетесь исполнителем по этой заявке. Используйте /unlink',
    'Customer is already linked.' => 'Вы уже присоединились.',
    '%mention%, you are not allowed to run this command.' => '%mention%, вы не можете использовать эту команду.',
    'вы присоединились к заявке.' => "вы присоединились к заявке.\n\nИспользуйте /unlink для отсоединения.",
    'all' => 'Все группы',
    'Описание команды /hid' => <<<FIN
                    Воспользуйтесь командой /hid для отправки скрытых сообщений в чат (например для отправки паролей и другой секретной информации).
                    
                    <u>Пример №1</u>:
                    <code>/hid &lt;секретный пароль&gt;</code>
    
                    <u>Пример №2</u>:
                    <code>/hid Список паролей
                    &lt;секретный пароль #1&gt;
                    &lt;секретный пароль #2&gt;
                    &lt;секретный пароль #3&gt;</code>
    
                    Во втором примере заголовок <b>Список паролей</b> останется видимым и будет скрыта только информация расположенная в следующий строках того же сообщения.
    
                    <i><u>Скрытые сообщения смогут просмотреть только агенты поддержки и отправивший сообщение пользователь.</u></i>
                    FIN,
    'Описание команды /del' => <<<FIN
                    ℹ️ <b>Информация</b>
    
                    Команда /del предназначена для удаления выбранного сообщения агентом, не являющимся администратором группы. Например сообщения не относящегося к теме заявки.
    
                    Для удаления сообщения нужно в ответ на выбранное сообщение отправить команду /del. Бот удалит это сообщение.
                    FIN,
    'Проверка группы завершена.' => <<<FIN
                    Проверка завершена. Вы успешно настроили группу.
                        
                    Теперь вы можете создавать задачи в этой группе («Одна тема → одна задача»), а я помогу их удобно организовать.
                    
                    <a href="https://oberdesk.ru/howto/how-to-create-task/">Инструкция по созданию задач</a>
    
                    <b>Важно!</b>
                    Сотрудники, решающие задачи в группах называются агенты. Чтобы задачи решались, добавьте в группу хотя бы одного агента. Например вы можете добавить себя командой:
                    <b>/agent %mention%</b>
   
                    Для получения дополнительной помощи нажмите или введите /help
                    FIN,
    
    // Bot messages in tickets
    // 
    'Can not archive non-closed ticket.' => <<<FIN
                    Не могу отправить в архив не закрытую заявку.
                    FIN,
    'Эта заявка перенеcена в архив.' => <<<FIN
                    Эта заявка перенесена в архив.
    
                    Ответы в этой заявке запрещены. Переоткрытие не возможно.
                    FIN,
    'Это таймер другого агента.' => 'Это таймер другого агента. Используйте свою кнопку паузы или введите /pause.',
    // Instructions after /start
    // 
    'Приветственное сообщение' => <<<FIN
                    Привет! Я <b>@Oberbot</b> — бот для организации работы по задачам сотрудников или клиентов. 

                    <b>Удобный инструмент</b> для фрилансеров, сервисных компаний, а так же для организации внутренней службы технической поддержки.

                    <b>При использовании</b> бота всё взаимодействие между пользователями происходит в привычном интерфейсе Telegram. 
                        
                    <b>Нет необходимости</b> ставить сторонние приложения и ваша информация всегда остается с вами в специально созданных для этого группах.
    
                    <b>Бесплатно навсегда</b> для фрилансеров и частного использования. Подробнее можно узнать в разделе <a href="https://oberdesk.ru/main/prices/">Тарифы</a>.
                    FIN,
    'Сообщение по кнопке Дальше' => <<<FIN
                    <b>Как я уже сказал</b>, все ваши задачи будут располагаться в группах Telegram. Не хочу вдаваться в пространные объяснения. Сами знаете, лучше один раз увидеть... Поэтому первое что вам нужно сделать — это создать новую группу и добавить меня туда. 

                    ➡️ <b>Дальнейшие инструкции</b> по использованию я дам вам прямо там, чтобы вам не пришлось возвращаться в этот чат. Хотя мы с вами и здесь ещё увидимся 😉
    
                    ❓ <b>Если у вас есть вопрос</b> — напишите его прямо в этом чате. Специалист ответит вам не более чем через 15 минут.
                    FIN,
    'Инструкции как создать группу' => <<<FIN
                    Для того чтобы создать группу нажмите на значок нового чата в правом верхнем углу и затем Создать группу.

                    Выберите кого вы хотите добавить в эту группу и не забудьте меня (@oberbot) и нажмите Далее.

                    Придумайте название группы и нажмите Создать. Я напишу вам туда, как только всё будет готово.
                    FIN,
    'Инструкции как создать группу 1' => 'Для того чтобы создать группу нажмите на значок нового чата в правом верхнем углу и затем Создать группу.',
    'Инструкции как создать группу 2' => 'Выберите кого вы хотите добавить в эту группу и не забудьте меня (@Oberbot) и нажмите Далее.',
    'Инструкции как создать группу 3' => 'Придумайте название группы и нажмите Создать. Я напишу вам туда, как только всё будет готово.',
    'Инструкции для руководителя' => <<<FIN
                    #REVIEW
                    <b>Для руководителя.</b>

                    Приятно, что вы задумались над организацией службы технической поддержки в вашей компании. Я могу помочь сделать это удобным для ваших пользователей и технический специалистов. Ведь им не придётся устанавливать дополнительных программ. Всё взаимодействие будет вестись через Telegram, в специально созданной группе или группах. 

                    Вы можете создать одну общую группу технической поддержки для всей компании или сделать отдельные группы по отделам или как-то иначе. Пользователи одной группы будут видеть заявки друг-друга и смогут присоединяться к ним, чтобы получать уведомления о решении или уточняющие вопросы специалистов. 

                    Вы можете даже создать отдельную группу только для себя или всего высшего руководства компании, чтобы другие сотрудники не видели ваших заявок. Всё на ваше усмотрение.

                    FIN,
    'Инструкции для руководителя - стоимость' => <<<FIN
                    #REVIEW
                    <b>Стоимость</b>

                    Если в вашей компании пока только один технический специалист, то для вас использование всех моих (напоминаю, что я — бот) функций будет бесплатным. Добавление каждого дополнительного специалиста стоит 1999 руб. в месяц, которые вы можете оплачивать так же не выходя из Telegram.                    

                    FIN,
    'Инструкции для руководителя - возможности' => '#TODO - описание возможностей, в том числе отчетов и кнопка "Пример отчета"',
    'Инструкции для руководителя - что дальше' => '#TODO - Написать что надо самому создать группу и добавить меня туда или поручить это техническому специалисту.',
    'Инструкции технического специалиста' => <<<FIN
                    #REVIEW
                    <b>Для технического специалиста.</b>
    
                    Если вы здесь, вероятно вам надоело вести заявки пользователей или клиентов на бумажке, в электронной почте или в собственной голове и вы ищете более удобный и приспособленный для этого инструмент.
    
                    И вы его нашли. Этот инструмент — Я! (на всякий случай напомню, что я бот). 
                    #TODO - придумать какие кнопки
                    FIN,
    'Инструкции сервисной компании' => '#TODO - описать преимущества ведения задач клиентов для сервисной компании. Кнопки думаю как у руководителя (Стоимость, Возможности, Что дальше)',

    /// Кнопки
    //
    '️Кнопка Что дальше?' => 'Прикольно. Что дальше? ➡',
    'Возможности' => null,
    'Стоимость' => null,
    'Что дальшe?' => null,
    'Пригласить специалиста' => '🛎 Пригласить сюда',
    'Написать в поддержку' => '➡️ В чат поддержки',
    'Открыть справку на сайте' => '📓 Открыть справку',
    
    /// Help
    //
    'Сообщение помощи в чате с ботом' => <<<FIN
            <b>Если у вас есть вопрос</b> — вы можете написать его прямо в этом чате. Он будет перенаправлен специалистам технической поддержки, которые ответят вам не более чем через 15 минут.
    
            <b>Остальное можно не читать</b>
            Основная работа с ботом происходит в групповых чатах где расположены ваши задачи. В этом чате доступны только следующие команды:

            <b>/help</b> - этот текст

            <b>/start</b> - начальное приветствие бота и краткое описание работы 

            <b>/next</b> - получение ссылки на следующую задачу из групп, где вы назначены агентом. Можно указать список групп, например <u><b>/next work</b></u> (см. команду /list)
    
            <b>/list</b> - вывод групп, входящих в текущий активный список и кнопок для переключения между списками. Подробнее о списках см. команду /list в группах

            Полная документация на сайте oberdesk.ru            
            FIN,
    'Сообщение помощи в группе' => <<<FIN
                    <b>Команды администратора</b>
                    <b>/agent</b> - присвоить роль агента. <a href="https://oberdesk.ru/help/command/agent"><b><u>_?_</u></b></a>
                    <b>/customer</b> - забрать роль агента. <a href="https://oberdesk.ru/help/command/customer"><b><u>_?_</u></b></a>
    
                    <b>Основные команды агентов</b>
                    <b>/take</b> - взятие задачи и запуск учета времени. <a href="https://oberdesk.ru/help/command/take"><b><u>_?_</u></b></a>
                    <b>/wait</b> - постановка задачи на ожидание. <a href="https://oberdesk.ru/help/command/wait"><b><u>_?_</u></b></a>
                    <b>/off</b>  - блокировка пользователя. <a href="https://oberdesk.ru/help/command/off"><b><u>_?_</u></b></a>

                    <b>Общие команды</b>
                    <b>/hid</b>  - отправка скрытого комментария (например пароля). <a href="https://oberdesk.ru/help/command/hid"><b><u>_?_</u></b></a>
                    <b>/new</b>  - создание подзадачи из комментария в текущей задаче. <a href="https://oberdesk.ru/help/command/new"><b><u>_?_</u></b></a>
                    
                    <i><b>P.S.</b> Нажмите </i><a href="https://oberdesk.ru/help/command"><b><u>_?_</u></b></a><i> после описания команды для получений дополнительной справки или ознакомьтесь с <a href="https://oberdesk.ru/help"><b><u>полной документацией</u></b></a>.
                        
                    <b>Если у вас остались вопросы</b>, вы можете пригласить нашего специалиста в этот чат или написать в наш чат поддержки нажав одну из кнопок:</i>
                    FIN,

    'Помощь по команде /agent' => <<<FIN
                    <b>Агенты</b> — это специально назначенные администратором пользователи, которые имеют возможность учитывать время, затраченное на решение задачи, получать следующую ожидающую задачу командой /next, а так же некоторые другие возможности. Агенты так же могут создавать задачи как и обычные пользователи. Подробно это описано в <b><a href="https://oberdesk.ru/help">документации</a></b>.
    
                    <b>В группе</b> может быть любое количество агентов, при этом только один из них может не иметь лицензии на использование @oberbot. Подробнее об этом в разделе <b><a href="https://oberdesk.ru/pricing">цены</a></b>.
                    FIN,
    
    'Помощь по команде /customer' => <<<FIN
                    Роль <b>пользователь</b> — это роль по умолчанию. 
                        
                    Все пользователи группы могут создавать задачи в группе по принципу: «Одна тема → одна задача», принимать участие в их решении и отмечать задачи как решенные c помощью команды /done. 
                        
                    Подробнее о сценариях использования в разделе <b><a href="https://oberdesk.ru/help">возможности</a></b>.
                    FIN,
    'Перейдите в нашу группу технической поддержки %link%' => <<<FIN
                    Мы так же используем @Oberbot для работы нашей службы поддержки.

                    Для получения помощи перейдите в нашу группу технической поддержки по ссылке: %link% и создайте там тему с вашим вопросом. 
                    FIN,
    'Создал чат для поддержки вашей группы: %link%' => <<<FIN
                    Я создал чат для поддержки вашей группы и пригласил туда наших специалистов. Перейдите в него по ссылке %link% и создайте там тему с вашим вопросом.
    
                    Помощь не заставит себя долго ждать 😊
                    FIN,
    'Отправил приглашение нашим специалистам.' => <<<FIN
                    Отправил приглашение в эту группу нашим специалистам. Ожидайте. Помощь уже в пути. 
    
                    Вы можете отозвать приглашение в любой момент в настройках группы.
                    FIN,
    'Чат зарезервирован для технической поддержки' => <<<FIN
                    Мы создали этот чат для техподдержки вашей группы. Создайте новую тему с вашим вопросом и мы вам поможем.
                    FIN,
    'Просто черновики' => <<<FIN
                    /pause, ⏸ - приостановка выполнения задачи. <a href="https://oberdesk.ru/help/command/pause"><b><u>_?_</u></b></a>
                    /continue, ▶ - продолжение работы над задачей. <a href="https://oberdesk.ru/help/command/continue"><b><u>_?_</u></b></a>
                    /done, ✅ - отметить задачу как выполненную. <a href="https://oberdesk.ru/help/command/agent"><b><u>_?_</u></b></a>
                    /notify, 🛎 - уведомить (упомянуть) пользователей задачи. <a href="https://oberdesk.ru/help/command/agent"><b><u>_?_</u></b></a>

                    FIN,
    
    /// Оплаты
    //
    ActionInvoice::PERIOD_1_MONTH   => '1 месяц',
    ActionInvoice::PERIOD_3_MONTHS  => '3 месяца',
    ActionInvoice::PERIOD_6_MONTHS  => '6 месяцев',
    ActionInvoice::PERIOD_12_MONTHS => '12 месяцев',
        
    'Название счета %period%' => "Оплата подписки на %period%",
    'Описание счета %period% %quantity% %users%' => 'для %users%',
    '%quantity% агент(а,ов) на %period%' => '%quantity% агент(а,ов) на %period%',
    /// Приватный чат с ботом
    //
    'Ожидайте ответа оператора.' => 'Ваше сообщение направлено в службу поддержки. Ожидайте ответа оператора.',
    /// Tips
    'AddAgentTip' => <<<FIN
                    ⚠️ <b>Важно</b>
                    Не забудьте добавить в группу специалистов технической поддержки (агентов), которые будут решать заявки пользователей.
    
                    Например добавьте себя командой: 
                    /agent %s
                    FIN,
    
    /// URL
    'URL справки на сайте' => 'https://oberdesk.ru/help',
];

if (empty($translations[$text])) {
    echo $text;
} else {
    echo $translations[$text];
}