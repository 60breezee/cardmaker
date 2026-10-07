<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCardRequest;
use App\Models\Card;
use App\Models\Template;
use App\Services\QuotaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CardApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json($request->user()->cards()->with('template')->latest()->paginate(20));
    }

    public function store(StoreCardRequest $request, QuotaService $quota): JsonResponse
    {
        $template = Template::findOrFail($request->integer('template_id'));
        $quota->ensureCanUseTemplate($request->user(), $template);
        $quota->ensureCanCreateCard($request->user());
        $card = $request->user()->cards()->create(['template_id' => $request->integer('template_id'), 'name' => $request->string('name'), 'data' => $request->input('data', [])]);

        return response()->json($card->load('template'), 201);
    }

    public function show(Card $card): JsonResponse
    {
        $this->authorize('view', $card);

        return response()->json($card->load('template', 'exports'));
    }
}
