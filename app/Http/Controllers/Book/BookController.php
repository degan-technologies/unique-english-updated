<?php

namespace App\Http\Controllers\Book;

use App\Http\Controllers\Book\Trait\PdfReaderTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\Book\BookResource;
use App\Http\Resources\StudentResources\StdBook\StdBookResource;
use App\Jobs\ProcessBookVideo;
use App\Models\Book\Book;
use App\Models\User;
use App\Services\LangService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Str;
use Carbon\Carbon;



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
    public function index(Request $request) {
        /**
         * @var mixed $resources
         */

        $resources = Book::with(['user'])
            ->where('user_id', Auth::id())
            ->when($request->searchQuery, fn($q) => $q->where('title', 'like', "%{$request->searchQuery}%"))
            ->when($request->language, fn($q) => $q->where('language', $request->language))
            ->paginate($request->rowsPerPageOptions ?? 10);
    
        // Get book statistics
        $stats = Book::query()
            ->where('user_id', Auth::id())
            ->selectRaw(
                'COUNT(*) as total, SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as newToday',
                [Carbon::now()->format('Y-m-d')]
            )
            ->first();
    
        $pagination = $resources->toArray();
        unset($pagination['data']);
    
        return response()->json([
            'newToday'   => $stats->newToday,
            'total'      => $stats->total,
            'pagination' => $pagination,
            'data'       => BookResource::collection($resources),
        ]);
    }

    public function allBooks() {
        /**
         * @var mixed $resources
         */

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
    public function store(Request $request) {
        $canStoreBook = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();
        /**
         * @var User $user  
         */
        
        $user = Auth::user();

        $validationRules = [ 
            'price' => 'required|integer',
            'language' => 'required|string', 
            'eddition' => 'required|integer',
            'publish_date' => 'required',
            'description' => 'required|string',
            'title' => 'required|string|max:255',
            'auther' => 'required|string|max:255',
            'file_url' => 'required',
            'intro_video' => 'required',
            'cover_page_url' => 'required',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('books'));
        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];
            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }
 
        $book = $user->books()->create([
            'slug' => Str::uuid(),
            'title' => $request->title,
            'price' => $request->price,
            'auther' => $request->auther,
            'eddition' => $request->eddition,
            'discount' => 0,
            'language' => $request->language,
            'file_format' => 'pdf',
            'publish_date' => $request->publish_date,
            'page_number' => $request->page_number ?? 0,
            'description' => $request->description,
            'tag' => json_encode(value: 'English'),
            'file_url' => $request->file_url,
            'cover_page_url' => $request->cover_page_url,
            'intro_vedio'=>$request->intro_video, 
            'video_optimized' => false,
            'isDownloadable' => false, 
        ]);

        if ($request->intro_vedio !== null) {
            ProcessBookVideo::dispatch($request->intro_video, $book->id);
        }

        return response()->json([
            'message' => $this->langService->getLang('book_created_successfully'),
            'data' => new BookResource($book),
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

    public function update(Request $request, $id) {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$user) {
            return response()->json([
                'message' => $this->langService->getLang('unauthorized_action'),
            ], 403);
        }

        $book = Book::query()
            ->where('user_id', $user->id)
            ->findOrFail($id);
    
        if (!$book) {
            return response()->json([
                'message' => $this->langService->getLang('book_not_found'),
            ], 404);
        }
        
    
        $validationRules = [ 
            'price' => 'required|integer',
            'language' => 'required|string', 
            'eddition' => 'required|integer',
            'publish_date' => 'required',
            'description' => 'required|string',
            'title' => 'required|string|max:255',
            'auther' => 'required|string|max:255',
            'file_url' => 'nullable', 
            'intro_vedio' => 'nullable',
            'cover_page_url' => 'nullable',
            'page_number' => 'nullable|integer',
        ];
    
        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('courses'));
    
        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];
            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }
    
        $data = $validator->validated();
     
        if ($request->file_url !== null) { 
     
            $data['file_url'] = $request->file_url; 
            $data['page_number'] = $request->page_number ?? 0;
        }
     
        if ($request->cover_page_url !== null) {
            $data['cover_page_url'] = $request->cover_page_url;
        }
     
        if ($request->intro_vedio !== null) {
            $data['intro_vedio'] = $request->intro_vedio;
            ProcessBookVideo::dispatch($request->intro_vedio, $book->id);
        }

        $book->update($data);
    
        return response()->json([
            'message' => $this->langService->getLang('book_updated_successfully'),
            'data' => BookResource::make($book),
        ]);
    }
    
    
    public function destroy(string $id) {


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

    public function uploadPdf(Request $request) {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$user) {
            return response()->json([
                'message' => $this->langService->getLang('unauthorized_action'),
            ], 403);
        }

        $validationRules = [ 
            'file_url' => 'required|file|mimes:pdf', 
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('books'));
        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];
            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }
 
        $pageNumber = null;
        $path = null;

        if ($request->hasFile('file_url')) {
            $pdfFile = $request->file('file_url');
            $fileFormat = $pdfFile->getClientOriginalExtension();

            if (strtolower($fileFormat) !== 'pdf')  return;

            $path = $request->file('file_url')->store('books/pdfFiles', 'private');

            $parser = new Parser();
            $pdf = $parser->parseFile($pdfFile->getPathname());
            $pages = $pdf->getPages();
            $pageNumber = count($pages);
        }

        return response()->json([
            'message' => $this->langService->getLang('pdf_uploaded_successfully'),
            'file_path' => $path,
            'page_number' => $pageNumber,
        ]);
    }

    public function uploadCoverImage(Request $request) {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$user) {
            return response()->json([
                'message' => $this->langService->getLang('unauthorized_action'),
            ], 403);
        }

        $validationRules = [ 
            'cover_page_url' => 'required|image',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('books'));
        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];
            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }

        $path = $request->file('cover_page_url')->store('books/pdfFiles', 'public');

        return response()->json([
            'message' => $this->langService->getLang('cover_image_uploaded_successfully'),
            'file_path' => $path,
        ]);
    }

    public function uploadIntroVideo(Request $request) {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();

        if (!$user) {
            return response()->json([
                'message' => $this->langService->getLang('unauthorized_action'),
            ], 403);
        }

        $validationRules = [
            'intro_video' => 'required',
        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('books'));
        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];
            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }

        $path = $request->file('intro_video')->store('books/video/original', 'public');

        return response()->json([
            'message' => $this->langService->getLang('intro_video_uploaded_successfully'),
            'file_path' => $path,
        ]);
    }
}
