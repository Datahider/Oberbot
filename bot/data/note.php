<?php
namespace losthost\Oberbot\data;

use losthost\DB\DB;
use losthost\DB\DBObject;
use Ramsey\Uuid\Uuid;
use losthost\Oberbot\data\note;
use losthost\Oberbot\service\Service;
use losthost\DB\DBList;

class note extends DBObject {

    const METADATA = [
        'uuid' => 'CHAR(36) NOT NULL',
        'note' => 'VARCHAR(4096) NOT NULL',
        'chat_id' => 'BIGINT(20) NOT NULL',
        'user_id' => 'BIGINT(20) NOT NULL',
        'topic_id' => 'BIGINT(20)',
        'time' => 'BIGINT(20) NOT NULL',
        'PRIMARY KEY' => 'uuid',
    ];
    
    public static function tableName() {
        return DB::$prefix. 'notes';
    }
    
    static public function create(string $text, int $chat_id, ?int $thread_id, int $user_id, array $mentioned_ids = []) {
        $note = new note();
        $note->uuid = Uuid::uuid4();
        $note->note = $text;
        $note->chat_id = $chat_id;
        $note->topic_id = $thread_id;
        $note->user_id = $user_id;
        $note->time = \time();
        
        try {
            DB::beginTransaction();
            $note->write();

            foreach ($mentioned_ids as $mentioned_id) {
                $note_mention = new note_mention();
                $note_mention->user_id = $mentioned_id;
                $note_mention->note_uuid = $note->uuid;
                $note_mention->write();
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
        return $note;
    }
    
    public function canView(int $user_id) : bool {
        
        if (Service::isAgent($user_id, $this->chat_id) || $user_id == $this->user_id) {
            return true;
        }
        
        $mentions = new DBList(note_mention::class, ['note_uuid' => $this->uuid]);
        
        while ($mention = $mentions->next()) {
            if ($mention->user_id == $user_id) {
                return true;
            }
        }
        
        return false;
    }
}
