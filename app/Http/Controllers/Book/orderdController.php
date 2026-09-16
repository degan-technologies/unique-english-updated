<?php

namespace App\Http\Controllers\Book;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Book\OrderedBookResource;
use App\Models\Book\OrderedBook;
use App\Services\LangService;
use Illuminate\Support\Str;

class orderdController extends Controller
{
    /**
     * @var LangService
     */
    protected $langService;

    public function __construct(LangService $langService)
    {
        $this->langService = $langService;
    }

    public function index()
    {
        $user = Auth::user();

        $orderedBooks = OrderedBook::with(['books'])
            ->where('user_id', $user->id)
            ->paginate(10);

        $pagination = $orderedBooks->toArray();
        unset($pagination['data']);

        return response()->json([
            'pagination' => $pagination,
            'data' => OrderedBookResource::collection($orderedBooks),
        ]);
    }
    public function store(Request $request)
    {
        $user = auth()->user();
        $validationRules = [
            'enrolled_at' => 'required|date',
            'book_id' => 'required|exists:books,id',

        ];

        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('ordereds'));
        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];
            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }

        $orderedboks = $user->orderedBooks()->create([
            'slug' => Str::uuid(),
            'enrolled_at' => $request->enrolled_at,
            'book_id' => $request->book_id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => $this->langService->getLang('ordered_created_successfully'),
            'data' => new OrderedBookResource($orderedboks),
        ]);
    }

    /**
     * Display a specific ordered book for the authenticated user.
     */
    public function show($id)
    {
        $user = Auth::user();
        $orderedBook = OrderedBook::with(['book'])->where('user_id', $user->id)
            ->findOrFail($id);

        return response()->json(new OrderedBookResource($orderedBook));
    }




    /**
     * Remove a specific ordered book for the authenticated user.
     */
    public function destroy($id)
    {
        $user = Auth::user();

        $orderedBook = OrderedBook::where('user_id', $user->id)->findOrFail($id);
        $orderedBook->delete();

        return response()->json([
            'message' => $this->langService->getLang('ordered_book_deleted'),
        ]);
    }
}
