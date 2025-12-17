<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Template extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name',
        'message',
        'is_default',
        'clinic_id',
        'campaign_type_id',
        'template_category_id',
    ];

    protected $appends = ['formatted_message'];

    /**
     * Get the template category associated with this template.
     *
     * @return BelongsTo
     */
    public function teamplate_category()
    {
        return $this->belongsTo(TemplateCategory::class, 'template_category_id');
    }

    /**
     * Get the clinic that this template belongs to.
     *
     * @return BelongsTo
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the template category that this template belongs to.
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TemplateCategory::class, 'template_category_id');
    }

    /**
     * Get the campaign type that this template belongs to.
     *
     * @return BelongsTo
     */
    public function campaignType(): BelongsTo
    {
        return $this->belongsTo(CampaignType::class);
    }

    /**
     * Set the message attribute after cleaning HTML and formatting.
     *
     * @param string $value
     *
     * @return void
     */
    public function setMessageAttribute(string $value): void
    {
        $allowed_tags = '<b><br><br />';
        $value = strip_tags($value, $allowed_tags);

        // Convert <br> or <br /> to newline safely
        $value = preg_replace('/<br\s*\/?>/i', "\n", $value);

        // Convert &nbsp; to regular space
        $value = str_replace('&nbsp;', ' ', $value);

        // Remove any remaining HTML tags
        $this->attributes['message'] = $value;
    }

    /**
     * Get the formatted message with HTML spans and line breaks.
     *
     * @return string
     */
    public function getFormattedMessageAttribute(): string
    {
        $text = $this->message;

        $allowedFields = config('constants.TEMPLATE_ALLOW_DYNAMIC_FIELDS');

        $search = [];
        $replace = [];

        foreach ($allowedFields as $field) {
            $search[] = '('.$field.')';
            $replace[] = '<span class="template-custom-field placeholder-insert" style="color:#465DFF" data-text="('.$field.')"><span contenteditable="false">('.$field.')</span></span>';
        }

        $text = str_replace($search, $replace, $text);

        $field = __('messages.page_texts.opt_message_text');
        $optHtml = '<span class="placeholder-insert" style="color:#747985 !important" data-text="'.$field.'"><span contenteditable="false" style="color:#747985" class="opt-out-text">'.$field.'</span></span>';

        $text = str_replace($field, $optHtml, $text);

        $text = str_replace("' '", '&nbsp;', $text);

        $text = nl2br($text);
        $text = str_replace(["\r", "\n"], '', $text);

        return $text;
    }
}
