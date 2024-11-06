<?php

use PHPUnit\Framework\TestCase;

use losthost\Oberbot\data\ticket;
use losthost\timetracker\Timer;

class ticketTest extends TestCase {
    
    const TEST_GROUP = -12;
    const TEST_THREAD = 234;
    const TEST_TITLE = "It's a test title for a ticket";
    const TEST_USER_ID = 18; // TODO - заменить на TEST_AGENT_ID
    const TEST_CUSTOMER_ID = 111;
    
    public function testTicketCreationAndGetting() {
        
        $ticket = ticket::create(static::TEST_GROUP, static::TEST_THREAD, static::TEST_TITLE, static::TEST_CUSTOMER_ID);
        
        $this->assertNotEmpty($ticket->id);
        $this->assertEquals(static::TEST_TITLE, $ticket->title);
        $this->assertEquals(ticket::STATUS_CREATING, $ticket->status);
        $this->assertEquals(static::TEST_CUSTOMER_ID, $ticket->ticket_creator);
        
        $ticket2 = $ticket::getById($ticket->id);
        $this->assertEquals($ticket->id, $ticket2->id);
        $this->assertEquals($ticket->status, $ticket2->status);
        $this->assertEquals($ticket->ticket_creator, $ticket2->ticket_creator);
        $this->assertEquals($ticket->chat_id, $ticket2->chat_id);
        $this->assertEquals($ticket->topic_id, $ticket2->topic_id);
        
        $ticket3 = $ticket::getByGroupThread($ticket->chat_id, $ticket->topic_id);
        $this->assertEquals($ticket->id, $ticket3->id);
        $this->assertEquals($ticket->status, $ticket3->status);
        $this->assertEquals($ticket->ticket_creator, $ticket3->ticket_creator);
        $this->assertEquals($ticket->chat_id, $ticket3->chat_id);
        $this->assertEquals($ticket->topic_id, $ticket3->topic_id);
        
    }
    
    public function testTicketAccepting() {
        
        $ticket = ticket::getByGroupThread(static::TEST_GROUP, static::TEST_THREAD)->accept();
        
        $this->assertEquals(ticket::STATUS_NEW, $ticket->status);
        
    }
    
    public function testTicketTouching() {
        
        sleep(1);
        $now = time();
        $ticket = ticket::getByGroupThread(static::TEST_GROUP, static::TEST_THREAD)->touchUser();
        
        $this->assertEquals($now, $ticket->last_activity);
        
        sleep(1);
        $now = time();
        $ticket->touchAdmin();
        
        $this->assertEquals($now, $ticket->last_admin_activity);
    }

    public function testTicketType() {
        
        $ticket = ticket::getByGroupThread(static::TEST_GROUP, static::TEST_THREAD)->toTask();
        $this->assertEquals(true, $ticket->is_task);

        $ticket->toTicket();
        $this->assertEquals(false, $ticket->is_task);
        
    }
    
    public function testTicketUrgent() {
        
        $ticket = ticket::getByGroupThread(static::TEST_GROUP, static::TEST_THREAD)->setUrgent();
        $this->assertTrue($ticket->is_urgent);
        
        $ticket->resetUrgent();
        $this->assertFalse($ticket->is_urgent);
    }
    
    public function testTimerStartStop() {
        $ticket = ticket::timerStart(static::TEST_GROUP, static::TEST_THREAD, static::TEST_USER_ID);
        $this->assertEquals(ticket::STATUS_IN_PROGRESS, $ticket->status);
        $timer = new Timer(static::TEST_USER_ID);
        $this->assertTrue($timer->isStarted());
        sleep(2);
        
        $ticket = ticket::timerStop(static::TEST_GROUP, static::TEST_THREAD, static::TEST_USER_ID);
        $timer = new Timer(static::TEST_USER_ID);
        $this->assertFalse($timer->isStarted());
    }

    public function testTicketStatusChanging() {
        
        ticket::timerStart(static::TEST_GROUP, static::TEST_THREAD, static::TEST_USER_ID);
        
        $ticket = ticket::close(static::TEST_GROUP, static::TEST_THREAD);
        $this->assertEquals(ticket::STATUS_CLOSED, $ticket->status);
        $timer = new Timer(static::TEST_USER_ID);
        $this->assertFalse($timer->isStarted());
        
        $ticket = ticket::reopen(static::TEST_GROUP, static::TEST_THREAD);
        $this->assertEquals(ticket::STATUS_REOPEN, $ticket->status);
        
        $this->expectExceptionMessageMatches("/Can not archive/");
        $ticket = ticket::archive(static::TEST_GROUP, static::TEST_THREAD);
        $this->assertEquals(ticket::STATUS_REOPEN, $ticket->status);
        
        $ticket = ticket::close(static::TEST_GROUP, static::TEST_THREAD);
        $ticket = ticket::archive(static::TEST_GROUP, static::TEST_THREAD);
        $this->assertEquals(ticket::STATUS_ARCHIVED, $ticket->status);
        
        $this->expectExceptionMessageMatches("/Can not change .* status/");
        $ticket = ticket::reopen(static::TEST_GROUP, static::TEST_THREAD);
        
    }
    
}
