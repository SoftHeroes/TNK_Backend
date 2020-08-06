<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $table = 'chat';

    protected $primaryKey = 'PID';

    protected $fillable = [
        'portalProviderID',
        'chatType',
        'gameID',
        'adminID',
        'userID',
        'message'
    ];


    public function getChat($portalProviderUUID, $gameUUID, $chatType){
        return $this->select('userID','message','createdAt')
            ->where('portalProviderID',$portalProviderUUID)
            ->where('gameID',$gameUUID)
            ->where('chatType',$chatType)
            ->get();
    }


}
