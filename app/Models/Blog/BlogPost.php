<?php

namespace App\Models\Blog;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogPost extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'author_id',
        'blog_category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'status',
        'views_count',
        'reading_time',
        'meta_tags',
    ];

    protected $casts = [
        'meta_tags' => 'array',
        'views_count' => 'integer',
        'reading_time' => 'integer',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tag');
    }

    public function views()
    {
        return $this->hasMany(BlogPostView::class, 'blog_post_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function recordView($request)
    {
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $userId = auth()->id();
        $sessionId = session()->getId();

        // Check if this user/device has viewed in the last 24 hours
        $recentView = BlogPostView::where('blog_post_id', $this->id)
            ->where(function ($query) use ($ipAddress, $userId, $sessionId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId)
                        ->orWhere('ip_address', $ipAddress);
                }
            })
            ->where('viewed_at', '>', now()->subHours(24))
            ->exists();

        if (!$recentView) {
            // Record new view
            BlogPostView::create([
                'blog_post_id' => $this->id,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'user_id' => $userId,
                'session_id' => $sessionId,
                'viewed_at' => now(),
            ]);

            // Update the counter
            $this->increment('views_count');
        }
    }
}
