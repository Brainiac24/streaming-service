<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::group([], function () {
    require_once (__DIR__ . '/ApiRoutes/AuthRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/PermissionRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/RoleRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/AccessGroupRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/SaleStatusRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/EventSessionStatusRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/EventStatusRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/EventTicketStatusRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/ProjectStatusRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/StreamStatusRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/EventRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/ProjectRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/FareRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/FareTypeRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/EventTicketRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/BanRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/UserRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/LanguageRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/PaymentRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/EventSessionRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/TransactionRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/EventAccessRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/StreamRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/EventSessionVisitRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/StreamStatRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/EventDataCollectionDictionaryRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/EventDataCollectionTemplateRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/EventDataCollectionRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/ChatMessageRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/ChatMessageLikeRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/ChatMessageTypeRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/PollStatusRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/PollTypeRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/PollRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/SaleRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/NimbleRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/AdminRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/MailingRequisiteRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/ContactGroupRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/ContactRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/MessageTemplateRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/MailingRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/MailingStatusRoutes.php');
    require_once (__DIR__ . '/ApiRoutes/MessageBrokerRoutes.php');
});
