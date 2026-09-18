<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Http\Resources\Blog\BlogPostResource;
use App\Models\Blog\BlogPost;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BlogPostController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $perPage = max(1, min($perPage, 50));

        $query = BlogPost::query()
            ->with('user')
            ->published()
            ->latest('published_at');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($subQuery) use ($search) {
                $subQuery
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tag')) {
            $query->whereJsonContains('tags', $request->tag);
        }

        $posts = $query->paginate($perPage);

        $pagination = $posts->toArray();
        unset($pagination['data']);

        return response()->json([
            'pagination' => $pagination,
            'data' => BlogPostResource::collection($posts),
        ]);
    }

    public function show($slug)
    {
        $post = BlogPost::query()
            ->with('user')
            ->where('slug', $slug)
            ->published()
            ->first();

        if (!$post) {
            return response()->json([
                'message' => 'Blog post not found',
            ], 404);
        }

        return response()->json([
            'data' => new BlogPostResource($post),
        ]);
    }

    public function manageIndex(Request $request)
    {
        $author = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$author) {
            return response()->json([
                'message' => 'Unauthorized action',
            ], 403);
        }

        $perPage = (int) $request->get('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $query = BlogPost::query()
            ->with('user')
            ->latest('created_at');

        if (!$author->systemAdmin()->exists()) {
            $query->where('user_id', $author->id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($subQuery) use ($search) {
                $subQuery
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $posts = $query->paginate($perPage);

        $pagination = $posts->toArray();
        unset($pagination['data']);

        return response()->json([
            'pagination' => $pagination,
            'data' => BlogPostResource::collection($posts),
        ]);
    }

    public function store(Request $request)
    {
        $author = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$author) {
            return response()->json([
                'message' => 'Unauthorized action',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255|unique:blog_posts,title',
            'excerpt' => 'nullable|string|max:1000',
            'content' => 'required|string',
            'cover_image' => 'nullable',
            'cover_image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:4096',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'status' => ['nullable', Rule::in([DRAFT, PUBLISHED])],
        ]);

        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->all()[0],
                'errors' => $validator->errors(),
            ], 422);
        }

        $status = $request->status ?? DRAFT;
        $coverImage = $this->resolveCoverImage($request);

        $post = $author->blogPosts()->create([
            'slug' => $this->generateUniqueSlug($request->title),
            'title' => $request->title,
            'excerpt' => $request->excerpt,
            'content' => $request->content,
            'cover_image' => $coverImage,
            'tags' => $request->tags,
            'status' => $status,
            'published_at' => $status === PUBLISHED ? now() : null,
        ]);

        $post->load('user');

        return response()->json([
            'message' => 'Blog post created successfully',
            'data' => new BlogPostResource($post),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $author = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$author) {
            return response()->json([
                'message' => 'Unauthorized action',
            ], 403);
        }

        $postQuery = BlogPost::query()->where('id', $id);

        if (!$author->systemAdmin()->exists()) {
            $postQuery->where('user_id', $author->id);
        }

        $post = $postQuery->first();

        if (!$post) {
            return response()->json([
                'message' => 'Blog post not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255|unique:blog_posts,title,' . $post->id,
            'excerpt' => 'sometimes|nullable|string|max:1000',
            'content' => 'sometimes|string',
            'cover_image' => 'sometimes|nullable',
            'cover_image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:4096',
            'tags' => 'sometimes|nullable|array',
            'tags.*' => 'string|max:50',
            'status' => ['sometimes', Rule::in([DRAFT, PUBLISHED])],
        ]);

        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->all()[0],
                'errors' => $validator->errors(),
            ], 422);
        }

        $oldStatus = $post->status;
        $newStatus = $request->status ?? $oldStatus;

        if ($request->filled('title') && $request->title !== $post->title) {
            $post->slug = $this->generateUniqueSlug($request->title, $post->id);
        }

        $post->fill($request->only([
            'title',
            'excerpt',
            'content',
            'tags',
            'status',
        ]));

        if ($request->hasFile('cover_image_file') || $request->has('cover_image')) {
            $newCoverImage = $this->resolveCoverImage($request);

            if ($post->cover_image && $newCoverImage !== $post->cover_image) {
                $this->deleteStoredCoverImage($post->cover_image);
            }

            $post->cover_image = $newCoverImage;
        }

        if ($oldStatus !== PUBLISHED && $newStatus === PUBLISHED) {
            $post->published_at = now();
        }

        if ($newStatus === DRAFT) {
            $post->published_at = null;
        }

        $post->save();
        $post->load('user');

        return response()->json([
            'message' => 'Blog post updated successfully',
            'data' => new BlogPostResource($post),
        ]);
    }

    public function destroy($id)
    {
        $author = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$author) {
            return response()->json([
                'message' => 'Unauthorized action',
            ], 403);
        }

        $postQuery = BlogPost::query()->where('id', $id);

        if (!$author->systemAdmin()->exists()) {
            $postQuery->where('user_id', $author->id);
        }

        $post = $postQuery->first();

        if (!$post) {
            return response()->json([
                'message' => 'Blog post not found',
            ], 404);
        }

        if ($post->cover_image) {
            $this->deleteStoredCoverImage($post->cover_image);
        }

        $post->delete();

        return response()->json([
            'message' => 'Blog post deleted successfully',
        ]);
    }

    public function changeStatus(Request $request, $id)
    {
        $author = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$author) {
            return response()->json([
                'message' => 'Unauthorized action',
            ], 403);
        }

        $postQuery = BlogPost::query()->where('id', $id);

        if (!$author->systemAdmin()->exists()) {
            $postQuery->where('user_id', $author->id);
        }

        $post = $postQuery->first();

        if (!$post) {
            return response()->json([
                'message' => 'Blog post not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in([DRAFT, PUBLISHED, ARCHIVED])],
        ]);

        if (!$validator->passes()) {
            return response()->json([
                'message' => $validator->errors()->all()[0],
                'errors' => $validator->errors(),
            ], 422);
        }

        $post->status = $request->status;
        $post->published_at = $request->status === PUBLISHED ? now() : null;
        $post->save();
        $post->load('user');

        return response()->json([
            'message' => 'Blog post status updated successfully',
            'data' => new BlogPostResource($post),
        ]);
    }

    private function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);

        if ($baseSlug === '') {
            $baseSlug = 'blog-post';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (BlogPost::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function resolveCoverImage(Request $request): ?string
    {
        if ($request->hasFile('cover_image_file')) {
            // Upload to S3
            return Storage::disk('s3')->putFile('blog/images', $request->file('cover_image_file'));
        }

        if ($request->has('cover_image')) {
            $coverImage = $request->input('cover_image');
            return $coverImage !== null && $coverImage !== '' ? $coverImage : null;
        }

        return null;
    }

    private function deleteStoredCoverImage(string $coverImage): void
    {
        // Skip external URLs (e.g. from old public disk or CDN)
        if (str_starts_with($coverImage, 'http://') || str_starts_with($coverImage, 'https://')) {
            return;
        }

        // Delete from S3
        Storage::disk('s3')->delete($coverImage);
    }
}
