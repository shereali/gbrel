<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Property extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Statuses that are never shown on the public site.
     *
     * @var list<string>
     */
    public const HIDDEN_STATUSES = ['Draft', 'Delisted', 'Pending Review', 'Rejected'];

    /**
     * Owner and review data. Only staff and the owner see these (see withPrivateFields()).
     *
     * @var list<string>
     */
    protected $hidden = [
        'owner_id', 'review_status', 'review_note', 'owner_details', 'owner_pending_changes', 'owner_agreement',
        'submitted_at', 'reviewed_at', 'reviewed_by', 'published_at',
    ];

    protected $casts = [
        'buyer_details' => 'array',
        'price' => 'float',
        'land_size' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'balconies' => 'integer',
        'square_footage' => 'integer',
        'parking' => 'integer',
        'floor_number' => 'integer',
        'total_floors' => 'integer',
        'year_built' => 'integer',
        'agent_id' => 'integer',
        'is_featured' => 'boolean',
        'is_rajuk_approved' => 'boolean',
        'is_verified' => 'boolean',
        'has_open_house' => 'boolean',
        'hide_price' => 'boolean',
        'hide_agent_photo' => 'boolean',
        'hide_agent_contact' => 'boolean',
        'hide_exact_address' => 'boolean',
        'hide_floor_plan' => 'boolean',
        'hide_mortgage_calculator' => 'boolean',
        'images' => 'array',
        'amenities' => 'array',
        'documents_verified' => 'array',
        'owner_details' => 'array',
        'owner_pending_changes' => 'array',
        'owner_agreement' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    protected $appends = ['feature_image', 'gallery'];

    public static function getTableColumns(): array
    {
        static $columns = null;
        if ($columns === null) {
            try {
                $columns = Schema::getColumnListing((new static)->getTable());
            } catch (\Throwable $e) {
                $columns = [
                    'id', 'title', 'slug', 'tagline', 'description', 'address', 'city', 'state', 'area_name', 'buyer_details',
                    'price', 'price_unit', 'hide_price', 'price_display_text', 'listing_type', 'property_type', 'status', 'bedrooms', 'bathrooms',
                    'balconies', 'square_footage', 'land_size', 'land_unit', 'parking', 'floor_number',
                    'total_floors', 'facing', 'completion_status', 'year_built', 'is_featured',
                    'is_rajuk_approved', 'is_verified', 'has_open_house', 'latitude', 'longitude',
                    'agent_id', 'hide_agent_photo', 'hide_agent_contact', 'hide_exact_address', 'hide_floor_plan', 'hide_mortgage_calculator',
                    'images', 'amenities', 'documents_verified', 'brochure_url', 'created_at', 'updated_at',
                ];
            }
        }

        return $columns;
    }

    public function getFeatureImageAttribute(): ?string
    {
        return ! empty($this->images) && is_array($this->images) ? $this->images[0] : null;
    }

    public function getGalleryAttribute(): array
    {
        return ! empty($this->images) && is_array($this->images) && count($this->images) > 1
            ? array_values(array_slice($this->images, 1))
            : [];
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PropertyDocument::class);
    }

    /**
     * Listings the public may see: not hidden by status, and owner submissions only once GBREL has published them.
     */
    public function scopeVisibleToPublic(Builder $query): Builder
    {
        return $query->whereNotIn('status', self::HIDDEN_STATUSES)
            ->where(function (Builder $inner) {
                $inner->whereNull('owner_id')->orWhereNotNull('published_at');
            });
    }

    public function isVisibleToPublic(): bool
    {
        return ! in_array($this->status, self::HIDDEN_STATUSES, true)
            && ($this->owner_id === null || $this->published_at !== null);
    }

    public function withPrivateFields(): static
    {
        return $this->makeVisible($this->hidden);
    }
}
