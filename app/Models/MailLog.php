<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailLog extends Model
{
    const CREATED_AT = 'createdAt';
    protected $table = 'mailLog';
}
