<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('campaign_media')) {
            Schema::rename('campaign_media', 'campaign_media_legacy');
        }

        Schema::create('campaign_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instagram_media_id')->constrained()->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['campaign_id', 'instagram_media_id']);
        });

        if (Schema::hasTable('campaign_media_legacy')) {
            $legacyRows = DB::table('campaign_media_legacy')->get();

            foreach ($legacyRows as $legacyRow) {
                $campaignName = trim((string) ($legacyRow->campaign_name ?? '')) !== ''
                    ? (string) $legacyRow->campaign_name
                    : 'Uncategorized';

                $campaignId = DB::table('campaigns')
                    ->where('client_id', $legacyRow->client_id)
                    ->where('name', $campaignName)
                    ->value('id');

                if ($campaignId === null) {
                    $campaignId = DB::table('campaigns')->insertGetId([
                        'client_id' => $legacyRow->client_id,
                        'proposal_id' => null,
                        'name' => $campaignName,
                        'description' => null,
                        'created_at' => $legacyRow->created_at ?? now(),
                        'updated_at' => $legacyRow->updated_at ?? now(),
                    ]);
                }

                DB::table('campaign_media')->updateOrInsert(
                    [
                        'campaign_id' => $campaignId,
                        'instagram_media_id' => $legacyRow->instagram_media_id,
                    ],
                    [
                        'notes' => $legacyRow->notes,
                        'created_at' => $legacyRow->created_at ?? now(),
                        'updated_at' => $legacyRow->updated_at ?? now(),
                    ],
                );
            }

            Schema::drop('campaign_media_legacy');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('campaign_media')) {
            Schema::rename('campaign_media', 'campaign_media_refactored');
        }

        Schema::create('campaign_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instagram_media_id')->constrained()->cascadeOnDelete();
            $table->string('campaign_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['client_id', 'instagram_media_id']);
        });

        if (Schema::hasTable('campaign_media_refactored')) {
            $rows = DB::table('campaign_media_refactored')
                ->join('campaigns', 'campaigns.id', '=', 'campaign_media_refactored.campaign_id')
                ->select([
                    'campaign_media_refactored.instagram_media_id',
                    'campaign_media_refactored.notes',
                    'campaign_media_refactored.created_at',
                    'campaign_media_refactored.updated_at',
                    'campaigns.client_id',
                    'campaigns.name as campaign_name',
                ])
                ->get();

            foreach ($rows as $row) {
                DB::table('campaign_media')->updateOrInsert(
                    [
                        'client_id' => $row->client_id,
                        'instagram_media_id' => $row->instagram_media_id,
                    ],
                    [
                        'campaign_name' => $row->campaign_name,
                        'notes' => $row->notes,
                        'created_at' => $row->created_at ?? now(),
                        'updated_at' => $row->updated_at ?? now(),
                    ],
                );
            }

            Schema::drop('campaign_media_refactored');
        }
    }
};
