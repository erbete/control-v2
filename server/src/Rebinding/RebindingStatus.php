<?php

namespace Control\Rebinding;

enum RebindingStatus: string
{
    case NONE = 'NONE';
    case DONE = 'DONE';
    case CONTINUE_NO_BINDING = 'CONTINUE_NO_BINDING';
    case NEGOTIATING = 'NEGOTIATING';
    case UNDER_PROCESS = 'UNDER_PROCESS';
    case AWAITING_SALE = 'AWAITING_SALE';
    case AWAITING_DEALER = 'AWAITING_DEALER';
    case LOST_CAUSE = 'LOST_CAUSE';
    case NO_CONTACT = 'NO_CONTACT';
    case MANUAL_CHECK = 'MANUAL_CHECK';
    case REBINDED = 'REBINDED';
}
