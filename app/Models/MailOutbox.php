<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailOutbox extends Model
{
    protected $table = 'mail_outbox';
    protected $fillable = [
        'mailable','cliente_id','factura_id','to','subject','status',
        'attempts','message_id','last_error','payload','queued_at',
        'sent_at','failed_at',
    ];
    protected $casts = [
        'payload'  => 'array',
        'queued_at'=> 'datetime',
        'sent_at'  => 'datetime',
        'failed_at'=> 'datetime',
    ];
}
