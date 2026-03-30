<?php

namespace App\Enums;

enum LeadStage: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Proposal = 'proposal';
    case Meeting = 'meeting';
    case Won = 'won';
    case Lost = 'lost';
}
