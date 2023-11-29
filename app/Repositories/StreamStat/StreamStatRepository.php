<?php

namespace App\Repositories\StreamStat;

use App\Constants\DynamicTablePrefixes;
use App\Models\StreamStat;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class StreamStatRepository extends BaseRepository
{
    public function __construct(public StreamStat $streamStat)
    {
        parent::__construct($streamStat);
    }

    public function createTableWithStreamId($streamId)
    {
        $tableName = DynamicTablePrefixes::STREAM_STATS . $streamId;

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->foreignId('stream_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->boolean('is_playing')->nullable();
            $table->longtext('data_json')->nullable();
            $table->string("ip");
            $table->string("useragent");
            $table->timestamps();
        });
    }

    public function getStreamConnectedUsers($streamId)
    {
        $tableName = DynamicTablePrefixes::STREAM_STATS . $streamId;

        $this->streamStat->setTable($tableName);

        if (!Schema::hasTable($tableName)) {
            return [];
        }

        $connectedUserIds = $this->streamStat
            ->where([
                ["is_playing", true],
                ["created_at", ">", Carbon::now()->subSeconds(90)],
            ])
            ->distinct(['user_id'])
            ->pluck('user_id');

        return $connectedUserIds;
    }
}
