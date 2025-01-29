<?php

namespace App\Http\Controllers\Live;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\LangService;
use App\Models\Live\LiveSession;
use App\Models\Live\LiveResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Live\LiveResourceResource;
class LiveResourceController extends Controller{
    protected $langService;

    public function __construct(LangService $langService) {
        $this->langService = $langService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index() {
        $resources = LiveResource::all()
            ->where('user_id', Auth::id())
            ->paginate(10);

        $pagination = $resources->toArray();
        unset($pagination['data']);

        return response()->json([
            'pagination' => $pagination,
            'data' => LiveResourceResource::collection($resources),
        ]);
    }
    public function store(Request $request){
        
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();
            if (!$user) return;
            
        $liveSessionId = $request->live_session_id ?? null;
  
        $liveSession= LiveSession::query()
                     ->where('user_id', $user->id)
                     ->find($liveSessionId );
         
         if(!$liveSession) {
             return response()->json([
                 'message' => $this->langService->getLang('live_session_not_found')
             ], 404);
         }
    
        $validationRules = [
            'resource_name' => 'required|string|max:255',
            'resurce_url' => 'required|url|unique:live_resources,resurce_url',
        ];
     
        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('LiveResource'));
    
        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];
            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }
    
        $resource = $user->liveResources()->create([
            'slug' => Str::uuid(),
            'resource_name' => $request->resource_name,
            'live_session_id' => $liveSession->id, 
            'resurce_url' => $request->resurce_url,
        ]);
    
        return response()->json([
            'message' => $this->langService->getLang('resource_created_successfully'),
            'data' => new LiveResourceResource($resource),
        ]);
    }
    
    /**
     * Display the specified resource.
     */
    public function show($id){
        $resource = LiveResource::query()
            ->where('user_id', Auth::id())
            ->first();

        return response()->json([
            'data' => new  LiveResourceResource($resource)
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::query()
            ->whereSystemAdminOrInstructor()
            ->first();
            if (!$user) return;
    
        $resource = LiveResource::query()
            ->where('user_id', $user->id)
            ->findOrFail($id);
    
        if (!$resource) {
            return response()->json([
                'message' => $this->langService->getLang('resource_not_found'),
            ], 404);
        }
    
        $liveSession = $user->liveSessions()->latest()->first(); 
    
        if (!$liveSession) {
            return response()->json([
                'message' => 'No active live session found.',
            ], 404);
        }
    
        $validationRules = [
            'resource_name' => 'required|string|max:255',
            'resurce_url' => 'required|url|unique:live_resources,resurce_url,' . $resource->id, 
        ];
    
        $validator = Validator::make($request->all(), $validationRules, $this->langService->getLang('LiveResource'));
    
        if (!$validator->passes()) {
            $message = $validator->errors()->all()[0];
            return response()->json([
                'message' => $message,
                'errors' => $validator->errors()
            ], 422);
        }
    
        $validatedData = $validator->validated();
        $validatedData['live_session_id'] = $liveSession->id; 
    
        $resource->update($validatedData);
    
        return response()->json([
            'message' => $this->langService->getLang('resource_updated_successfully'),
            'data' => new LiveResourceResource($resource),
        ]);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id){
        $user = User::query()
        ->whereSystemAdminOrInstructor()
        ->first();
        if (!$user) return;

        $resource = LiveResource::query()
        ->where('user_id', $user->id)
        ->findOrFail($id);

        if(!$resource) {
            return response()->json([
                'message' => $this->langService->getLang('resource_not_found'),
            ], 404);
        }

        $resource->delete();

        return response()->json([
            'message' => $this->langService->getLang('resource_deleted_successfully'),
        ]);
    }
}
