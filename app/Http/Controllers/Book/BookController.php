<?php

namespace App\Http\Controllers\Book;

use App\Http\Controllers\Controller;
use App\Http\Resources\Book\BookResource;
use App\Models\Book\Book;
use App\Models\User;
use App\Services\LangService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BookController extends Controller
{
    protected $langService;

    public function __construct(LangService $langService)
    {
        $this->langService = $langService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $resources = Book::with(['user'])
            ->where('user_id', Auth::id())
            ->paginate(10);
        $pagination = $resources->toArray();
        unset($pagination['data']);

        return response()->json([
            'pagination' => $pagination,
            'data' => BookResource::collection($resources),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        $validationRules = [
            'tag' => 'min:3',
            'price' => 'required|integer',
            'language' => 'required|string',
            'discount' => 'nullable|integer',
            'eddition' => 'required|integer',
            'not_deleted' => 'required|boolean',
            'publish_date' => 'required|date',
            'description' => 'required|string',
            'file_format' => 'required|string',
            'page_number' => 'required|integer',
            'title' => 'required|string|max:255',
            'auther' => 'required|string|max:255',
            'isDownloadable' => 'required|boolean',
            'file_url' => 'required|url|unique:book,file_url',
            'cover_page_url' => 'required|url|unique:book,cover_page_url',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('book'));
        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];
            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }

        $boks = $user->book()->create([
            'slug' => Str::uuid(),
            'title' => $request->title,
            'price' => $request->price,
            'auther' => $request->auther,
            'eddition' => $request->eddition,
            'discount' => $request->discount,
            'language' => $request->language,
            'file_format' => $request->file_format,
            'page_number' => $request->page_number,
            'not_deleted' => $request->not_deleted,
            'description' => $request->description,
            'tag' => json_encode($request->tag),
            'cover_page_url' => $request->cover_page_url,
            'isDownloadable' => $request->isDownloadable,

        ]);

        return response()->json([
            'message' => $this->langService->getLang('book_created_successfully'),
            'data' => new BookResource($boks),
        ]);
    }

    public function show(string $id)
    {
        $boks = Book::query()
            ->where('user_id', Auth::user()->id)
            ->first();

        return response()->json([
            'data' => new  BookResource($boks)
        ]);
    }


    public function update(Request $request, $id)
    {

        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        $book = Book::query()
            ->where('user_id', $user->id)
            ->findOrFail($id);

        if (!$book) {
            return response()->json([
                'message' => $this->langService->getLang('book_not_found'),
            ], 404);
        }


        $validationRules = [
            'tag' => 'min:3',
            'price' => 'required|integer',
            'language' => 'required|string',
            'discount' => 'nullable|integer',
            'eddition' => 'required|integer',
            'not_deleted' => 'required|boolean',
            'publish_date' => 'required|date',
            'description' => 'required|string',
            'file_format' => 'required|string',
            'page_number' => 'required|integer',
            'title' => 'required|string|max:255',
            'auther' => 'required|string|max:255',
            'isDownloadable' => 'required|boolean',
            'file_url' => 'required|url|unique:book,file_url',
            'cover_page_url' => 'required|url|unique:book,cover_page_url',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('courses'));
        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];
            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }

        $book->update($validator->validated());

        return response()->json([
            'message' => $this->langService->getLang('book_updated_successfully'),
            'data' => new BookResource($book),
        ]);
    }


    public function destroy(string $id)
    {


        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        $book = Book::query()
            ->where('user_id', $user->id)
            ->findOrFail($id);

        if (!$book) {
            return response()->json([
                'message' => $this->langService->getLang('book_not_found'),
            ], 404);
        }

        $book->delete();

        return response()->json([
            'message' => $this->langService->getLang('book_deleted_successfully'),
        ]);
    }
}
