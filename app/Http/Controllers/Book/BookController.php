<?php

namespace App\Http\Controllers\Book;

use App\Http\Controllers\Book\Trait\PdfReaderTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\Book\BookResource;
use App\Http\Resources\StudentResources\StdBook\StdBookResource;
use App\Models\Book\Book;
use App\Models\User;
use App\Services\LangService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BookController extends Controller {

    use PdfReaderTrait;

    protected $langService;

    public function __construct(LangService $langService)
    {
        $this->langService = $langService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index() {
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

    public function allBooks() {
        $resources = Book::with(['user']) 
            ->paginate(10);

        $pagination = $resources->toArray();
        unset($pagination['data']);

        return response()->json([
            'pagination' => $pagination,
            'data' => StdBookResource::collection($resources),
        ]);
    }

    public function getBook($slug){
        $user = Auth::user();

        $book = Book::query()
            ->where('slug', $slug)
            ->first();

        $verifyTransaction = Book::checkEligibility($book->id);

        if (!$verifyTransaction) {
            return response()->json([
                'message' => 'Unauthorized action.',
            ], 403);
        }
        return response()->json([
            'data' => new BookResource($book),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $canStoreBook = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();
        /**
         * @var User $user  
         */
        
        $user = Auth::user();

        $validationRules = [
            'tag' => 'min:3',
            'price' => 'required|integer',
            'language' => 'required|string',
            'discount' => 'nullable|integer',
            'eddition' => 'required|integer',
            'publish_date' => 'required',
            'description' => 'required|string',
            'file_format' => 'required|string',
            'page_number' => 'required|integer',
            'title' => 'required|string|max:255',
            'auther' => 'required|string|max:255',
            // 'isDownloadable' => 'required|boolean',
             'file_url' => 'mimes:pdf',
            'intro_vedio' => 'nullable|file|mimetypes:video/mp4,video/avi,video/mpeg',

            'cover_page_url' => 'image',

           
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('books'));
        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];
            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }
        $imagePath = null;
        if($request->hasFile('file_url')) {
            $imagePath = $request->file('file_url')->store('/books/images', 'public');
        }
        $imagesPath = null;
        if($request->hasFile('cover_page_url')) {
            $imagesPath = $request->file('cover_page_url')->store('/books/images', 'public');
        }
        $videoPath = null;
        if($request->hasFile('intro_vedio')) {
            $videoPath = $request->file('intro_vedio')->store('/books/videos', 'public');
        }

        $boks = $user->books()->create([
            'slug' => Str::uuid(),
            'title' => $request->title,
            'price' => $request->price,
            'auther' => $request->auther,
            'eddition' => $request->eddition,
            'discount' => $request->discount,
            'language' => $request->language,
            'file_format' => $request->file_format,
            'publish_date' => $request->publish_date,
            'page_number' =>$request->page_number,
            // 'not_deleted' => 1,
            'description' => $request->description,
            'tag' => json_encode($request->tag),
            'file_url' => $imagePath,
            'cover_page_url' => $imagesPath,
            'intro_vedio'=>$videoPath ,
            'isDownloadable' => false,

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
    
        $book = Book::query()->findOrFail($id);
    
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
            'publish_date' => 'required',
            'description' => 'required|string',
            'file_format' => 'required|string',
            'page_number' => 'required|integer',
            'title' => 'required|string|max:255',
            'auther' => 'required|string|max:255',
            // 'isDownloadable' => 'required|boolean',
            'intro_vedio' => 'required|file|mimetypes:video/mp4,video/avi,video/mpeg',
            'file_url' => 'mimes:pdf',
            'cover_page_url' => 'image',
        ];
    
        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('courses'));
        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];
            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }
    
        // Get validated data
        $data = $validator->validated();
    
        if ($request->hasFile('file_url')) {
            $data['file_url'] = $request->file('file_url')->store('/books/images', 'public');
        }
        if ($request->hasFile('cover_page_url')) {
            $data['cover_page_url'] = $request->file('cover_page_url')->store('/books/images', 'public');
        }
        if($request->hasFile('intro_vedio')) {
            $data['intro_vedio'] = $request->file('intro_vedio')->store('/books/videos', 'public');
        }
        
        $book->update($data);
    
        return response()->json([
            'message' => $this->langService->getLang('book_updated_successfully'),
            'data' => BookResource::make($book),
        ]);
    }
    
    public function destroy(string $id)
    {


        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        $book = Book::query()
            // ->where('user_id', $user->id)
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
