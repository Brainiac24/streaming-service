<?php

namespace App\Constants;

use App\Services\Cache\CacheServiceFacade;

class CacheKeys
{

    public static function setEventSessionKeys($eventSessionId, $cacheKey)
    {
        $eventSessionKeys = CacheServiceFacade::get(self::eventSessionKeysByIdKey($eventSessionId)) ?? [];
        $eventSessionKeys[] = $cacheKey;

        return CacheServiceFacade::set(self::eventSessionKeysByIdKey($eventSessionId), $eventSessionKeys, config('cache.ttl'));
    }

    public static function forgetEventSessionKeys($eventSessionId)
    {
        $eventSessionKeys = CacheServiceFacade::get(self::eventSessionKeysByIdKey($eventSessionId)) ?? [];
        foreach ($eventSessionKeys as $eventSessionKey) {
            CacheServiceFacade::forget($eventSessionKey);
        }
    }



    /**
     * ---------------------- TAGS ----------------------
     */

    public static function projectIdTag($id)
    {
        return "project_id:{$id}:tag";
    }

    public static function eventIdTag($id)
    {
        return "event_id:{$id}:tag";
    }
    public static function accessGroupIdTag($accessGroupId)
    {
        return "access_group_id:{$accessGroupId}:tag";
    }

    public static function eventSessionIdTag($id)
    {
        return "event_session_id:{$id}:tag";
    }

    public static function chatIdTag($id)
    {
        return "chat_id:{$id}:tag";
    }

    public static function pollIdTag($id)
    {
        return "poll_id:{$id}:tag";
    }

    public static function saleIdTag($id)
    {
        return "sale_id:{$id}:tag";
    }

    public static function streamIdTag($id)
    {
        return "stream_id:{$id}:tag";
    }

    public static function contactIdTag($id)
    {
        return "contact_id:{$id}:tag";
    }

    public static function eventDataCollectionTemplateIdTag($id)
    {
        return "event_data_collection_template_id:{$id}:tag";
    }

    public static function fareIdTag($id)
    {
        return "fare_id:{$id}:tag";
    }

    public static function eventTicketIdTag($id)
    {
        return "event_ticket_id:{$id}:tag";
    }


    public static function setEventSessionIdTags($eventSessionIds)
    {
        $eventSessionIdTags = [];
        foreach ($eventSessionIds as $eventSessionId) {
            $eventSessionIdTags[] = self::eventSessionIdTag($eventSessionId);
        }
        return $eventSessionIdTags;
    }

    public static function setPollIdTags($pollIds)
    {
        $pollIdTags = [];
        foreach ($pollIds as $pollId) {
            $pollIdTags[] = self::pollIdTag($pollId);
        }
        return $pollIdTags;
    }

    public static function setSaleIdTags($saleIds)
    {
        $saleIdTags = [];
        foreach ($saleIds as $saleId) {
            $saleIdTags[] = self::saleIdTag($saleId);
        }
        return $saleIdTags;
    }

    public static function setStreamIdTags($streamIds)
    {
        $streamIdTags = [];
        foreach ($streamIds as $streamId) {
            $streamIdTags[] = self::streamIdTag($streamId);
        }
        return $streamIdTags;
    }

    public static function setEventDataCollectionTemplateIdTags($eventDataCollectionTemplateIds)
    {
        $eventDataCollectionTemplateIdTags = [];
        foreach ($eventDataCollectionTemplateIds as $eventDataCollectionTemplateId) {
            $eventDataCollectionTemplateIdTags[] = self::eventDataCollectionTemplateIdTag($eventDataCollectionTemplateId);
        }
        return $eventDataCollectionTemplateIdTags;
    }

    public static function setContactIdTags($contactIds)
    {
        $contactIdTags = [];
        foreach ($contactIds as $contactId) {
            $contactIdTags[] = self::streamIdTag($contactId);
        }
        return $contactIdTags;
    }

    /**
     * ---------------------- KEYS ----------------------
     */

    public static function blacklistTokenKey($token)
    {
        return "blacklisted_token:{$token}";
    }

    public static function userIdKey($id)
    {
        return "user:{$id}";
    }

    public static function userEmailKey($email)
    {
        return "user:{$email}";
    }

    public static function userTokenKey($token)
    {
        return "user:{$token}";
    }

    public static function userPermissionKey($userId, $permissionName)
    {
        return "user:{$userId}:permission_name:{$permissionName}";
    }

    public static function userPermissionForAccessGroupKey($userId, $permissionName, $accessGroupId)
    {
        return "user:{$userId}:permission_name:{$permissionName}:access_group:$accessGroupId";
    }

    public static function authKey($authKey)
    {
        return "auth_key:{$authKey}";
    }

    public static function OTPKey($email, $OTPKey)
    {
        return "user:{$email}:otp_key:{$OTPKey}";
    }

    public static function OTPSentCountKey($email)
    {
        return "user:{$email}:otp_sent_count";
    }

    public static function OTPTryCountKey($email)
    {
        return "user:{$email}:otp_try_count";
    }

    public static function userRoleKey($userId, $roleId)
    {
        return "user:{$userId}:role_id:{$roleId}";
    }

    public static function receptionByEventIdKey($eventId)
    {
        return "reception_by_event_id:{$eventId}";
    }

    public static function chatMessagesByChatIdAndChatMessageTypeIdKey($chatId, $chatMessageTypeId){
        return "chat_messages_by_chat_id:{$chatId}:chat_message_type_id:{$chatMessageTypeId}";
    }

    public static function eventSessionVisitByEventSessionIdKey($eventSessionId){
        return "event_sessiion_visits_by_event_session_id:{$eventSessionId}";
    }

    public static function contactListByEventIdKey($eventId){
        return "contact_list_by_event_id:{$eventId}";
    }

    public static function contactListByEventIdAndContactGroupIdKey($eventId,$contactGroupId){
        return "contact_list_by_event_id:{$eventId}:contact_group_id:{$contactGroupId}";
    }


    public static function eventTicketsByEventIdKey($eventId){
        return "event_tickets_by_event_id:{$eventId}";
    }

    public static function eventTicketsByEventIdAndStatusKey($eventId,$status){
        return "event_tickets_by_event_id:{$eventId}:and_status:{$status}";
    }

    public static function eventTicketsByEventIdAndStatusWithSearchKey($eventId,$status,$search){
        return "event_tickets_by_event_id:{$eventId}:and_status:{$status}:with_search:{$search}";
    }


    public static function eventSessionVisitStatsByStreamIdKey($streamId){
        return "event_sessiion_visits_stats_by_stream_id:{$streamId}";
    }
    public static function templateByEventIdKey($eventId)
    {
        return "template:{$eventId}";
    }

    public static function eventKey($eventId)
    {
        return "event:{$eventId}";
    }

    public static function rolesByUserIdKey($userId)
    {
        return "roles_by_user_id:{$userId}";
    }

    public static function rolesByChatIdKey($chatId, $userId)
    {
        return "roles_by_chat_id:{$chatId}:user_id:{$userId}";
    }

    public static function rolesByEventSessionIdKey($eventSessionId, $userId)
    {
        return "roles_by_event_session_id:{$eventSessionId}:user_id:{$userId}";
    }

    public static function rolesByEventIdKey($eventId, $userId)
    {
        return "roles_by_event_id:{$eventId}:user_id:{$userId}";
    }

    public static function rolesByPollIdKey($pollId, $userId)
    {
        return "roles_by_poll_id:{$pollId}:user_id:{$userId}";
    }

    public static function rolesBySaleIdKey($saleId, $userId)
    {
        return "roles_by_sale_id:{$saleId}:user_id:{$userId}";
    }

    public static function rolesByBanIdKey($banId, $userId)
    {
        return "roles_by_ban_id:{$banId}:user_id:{$userId}";
    }

    public static function accessGroupBySaleIdKey($saleId)
    {
        return "access_group_by_sale_id:{$saleId}";
    }

    public static function accessGroupByPollIdKey($pollId)
    {
        return "access_group_by_poll_id:{$pollId}";
    }

    public static function accessGroupByBanIdKey($banId)
    {
        return "access_group_by_ban_id:{$banId}";
    }

    public static function accessGroupByEventSessionIdKey($eventSessionId)
    {
        return "access_group_by_event_session_id:{$eventSessionId}";
    }

    public static function accessGroupByChatIdKey($chatId)
    {
        return "access_group_by_chat_id:{$chatId}";
    }

    public static function salesByEventSessionIdKey($eventSessionId)
    {
        return "sales_by_event_session_id:{$eventSessionId}";
    }

    public static function chatMessageTypesKey()
    {
        return "chat_message_types";
    }
    public static function pollStatusesKey()
    {
        return "poll_statuses";
    }
    public static function pollTypesKey()
    {
        return "poll_types";
    }
    public static function saleStatusesKey()
    {
        return "sale_statuses";
    }
    public static function streamStatusesKey()
    {
        return "stream_statuses";
    }
    public static function eventSessionByIdForRoomKey($eventSessionId)
    {
        return "event_session_by_id_for_room:{$eventSessionId}";
    }
    public static function eventSessionCountByEventIdKey($eventId)
    {
        return "event_session_count_by_event_id:{$eventId}";
    }

    public static function hasEventSessionByProjectLink($eventSessionId, $projectLink)
    {
        return "has_event_session_id:{$eventSessionId}:by_project_link:{$projectLink}";
    }
    public static function banByEventIdAndUserId($eventId, $userId)
    {
        return "ban_by_event_id:{$eventId}:and_user_id:{$userId}";
    }

    public static function chatByEventSessionIdKey($eventSessionId)
    {
        return "chat_by_event_session_id:{$eventSessionId}";
    }

    public static function chatMessagesByChatIdAndChatMessageTypeIdAndIsPinned($chatId, $chatMessageTypeId, $isPinnedOnly)
    {
        return "chat_messages_by_chat_id:{$chatId}:chat_message_type_id:{$chatMessageTypeId}:is_pinned:{$isPinnedOnly}";
    }

    public static function eventSessionByKey($eventSessionKey)
    {
        return "event_session_by_key:{$eventSessionKey}";
    }

    public static function hasEventSessionByCodeAndEventLinkAndProjectLink($code, $eventLink, $projectLink)
    {
        return "has_event_session_by_code:{$code}:event_link:{$eventLink}:project_link:{$projectLink}";
    }

    public static function eventSessionByCodeAndEventLink($code, $eventLink)
    {
        return "event_session_by_code:{$code}:event_link:{$eventLink}";
    }

    public static function pollsByEventSessionIdWithOptions($eventSessionId)
    {
        return "polls_by_event_session_id:{$eventSessionId}";
    }

    public static function pollsByEventSessionIdAndIsAdminOrModeratorKey($eventSessionId, $isAdminOrModerator)
    {
        return "polls_by_event_session_id:{$eventSessionId}:is_admin_or_moderator:{$isAdminOrModerator}";
    }

    public static function eventByEventSessionIdKey($eventSessionId)
    {
        return "event_by_event_session_id:{$eventSessionId}";
    }

    public static function eventSessionKeysByIdKey($eventSessionId)
    {
        return "event_session_keys_by_id:{$eventSessionId}";
    }

    public static function eventSessionsByEventIdKey($eventId)
    {
        return "event_sessions_by_event_id:{$eventId}";
    }

    public static function eventSessionsIdByStreamIdKey($streamId)
    {
        return "event_session_id_by_stream_id:{$streamId}";
    }

    public static function hasEventByLinkAndProjectLinkKey($eventLink, $projectLink)
    {
        return "has_event_by_link:{$eventLink}_project_link:{$projectLink}";
    }

    public static function projectIdByEventIdKey($eventId)
    {
        return "project_id_by_event_id:{$eventId}";
    }

    public static function eventIdByEventLinkKey($eventLink)
    {
        return "event_id_by_event_link:{$eventLink}";
    }

    public static function eventSessionByChatIdKey($chatId)
    {
        return "event_session_by_chat_id:{$chatId}";
    }

    public static function fareByEventSessionIdKey($eventSessionId)
    {
        return "fare_by_event_session_id:{$eventSessionId}";
    }

    public static function eventSessionByStreamIdKey($streamId)
    {
        return "event_session_by_stream_id:{$streamId}";
    }

    public static function isJobCanceledByUuidKey($jobUuid)
    {
        return "is_job_canceled_by_uuid_key:{$jobUuid}";
    }

    public static function contactsWithEventIdKey()
    {
        return "contacts_with_event_id";
    }

    public static function eventTicketIdKey($ticketId)
    {
        return "event_ticket_id:{$ticketId}";
    }
}
