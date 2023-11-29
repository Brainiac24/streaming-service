<?php

namespace App\Constants;

class TinkoffPaymentStatuses
{

    const INIT = 1;
    const NEW = 2;
    const FORM_SHOWED = 3;
    const DEADLINE_EXPIRED = 4;
    const CANCELED = 5;
    const PREAUTHORIZING = 6;
    const AUTHORIZING = 7;
    const AUTH_FAIL = 8;
    const REJECTED = 9;
    const THREE_DS_CHECKING = 10;
    const THREE_DS_CHECKED = 11;
    const PAY_CHECKING = 12;
    const AUTHORIZED = 13;
    const REVERSING = 14;
    const PARTIAL_REVERSED = 15;
    const REVERSED = 16;
    const CONFIRMING = 17;
    const CONFIRM_CHECKING = 18;
    const CONFIRMED = 19;
    const REFUNDING = 20;
    const PARTIAL_REFUNDED = 21;
    const REFUNDED = 22;


    const INIT_NAME = 'INIT';
    const NEW_NAME = 'NEW';
    const FORM_SHOWED_NAME = 'FORM_SHOWED';
    const DEADLINE_EXPIRED_NAME = 'DEADLINE_EXPIRED';
    const CANCELED_NAME = 'CANCELED';
    const PREAUTHORIZING_NAME = 'PREAUTHORIZING';
    const AUTHORIZING_NAME = 'AUTHORIZING';
    const AUTH_FAIL_NAME = 'AUTH_FAIL';
    const REJECTED_NAME = 'REJECTED';
    const THREE_DS_CHECKING_NAME = 'THREE_DS_CHECKING';
    const THREE_DS_CHECKED_NAME = 'THREE_DS_CHECKED';
    const PAY_CHECKING_NAME = 'PAY_CHECKING';
    const AUTHORIZED_NAME = 'AUTHORIZED';
    const REVERSING_NAME = 'REVERSING';
    const PARTIAL_REVERSED_NAME = 'PARTIAL_REVERSED';
    const REVERSED_NAME = 'REVERSED';
    const CONFIRMING_NAME = 'CONFIRMING';
    const CONFIRM_CHECKING_NAME = 'CONFIRM_CHECKING';
    const CONFIRMED_NAME = 'CONFIRMED';
    const REFUNDING_NAME = 'REFUNDING';
    const PARTIAL_REFUNDED_NAME = 'PARTIAL_REFUNDED';
    const REFUNDED_NAME = 'REFUNDED';

}
