<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{
    /**
     * Listar posts do blog
     */
    public function index(Request $request): JsonResponse
    {
        $query = BlogPost::with(['user', 'categories']);

        if ($request->status) {
            $query->where('status', $request->status);
        } else {
            $query->published();
        }

        if ($request->category_id) {
            $query->byCategory($request->category_id);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('content', 'like', "%{$request->search}%");
            });
        }

        if ($request->featured) {
            $query->featured();
        }

        $posts = $query->latest('published_at')
                      ->paginate($request->per_page ?? 10);

        return response()->json([
            'data' => $posts->items(),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'last_page' => $posts->lastPage(),
            ]
        ]);
    }

    /**
     * Mostrar post específico
     */
    public function show(BlogPost $post): JsonResponse
    {
        if (!$post->isPublished()) {
            $this->authorize('view', $post);
        }

        $post->load(['user', 'categories']);

        return response()->json([
            'data' => $post
        ]);
    }

    /**
     * Criar novo post (Admin/Author)
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', BlogPost::class);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:blog_categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $post = BlogPost::create([
            'title' => $request->title,
            'content' => $request->content,
            'excerpt' => $request->excerpt,
            'featured_image' => $request->featured_image,
            'status' => $request->status,
            'user_id' => $request->user()->id,
            'published_at' => $request->status === 'published' ? now() : null,
        ]);

        if ($request->category_ids) {
            $post->categories()->attach($request->category_ids);
        }

        return response()->json([
            'message' => 'Post criado com sucesso',
            'data' => $post->load(['user', 'categories'])
        ], 201);
    }

    /**
     * Atualizar post
     */
    public function update(Request $request, BlogPost $post): JsonResponse
    {
        $this->authorize('update', $post);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'status' => 'sometimes|in:draft,published,archived',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:blog_categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $post->update($request->only(['title', 'content', 'excerpt', 'featured_image', 'status']));

        if ($request->has('category_ids')) {
            $post->categories()->sync($request->category_ids ?? []);
        }

        return response()->json([
            'message' => 'Post atualizado com sucesso',
            'data' => $post->fresh()->load(['user', 'categories'])
        ]);
    }

    /**
     * Excluir post
     */
    public function destroy(BlogPost $post): JsonResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return response()->json([
            'message' => 'Post excluído com sucesso'
        ]);
    }

    /**
     * Publicar post
     */
    public function publish(BlogPost $post): JsonResponse
    {
        $this->authorize('update', $post);

        $post->publish();

        return response()->json([
            'message' => 'Post publicado com sucesso',
            'data' => $post->fresh()
        ]);
    }

    /**
     * Arquivar post
     */
    public function archive(BlogPost $post): JsonResponse
    {
        $this->authorize('update', $post);

        $post->archive();

        return response()->json([
            'message' => 'Post arquivado com sucesso',
            'data' => $post->fresh()
        ]);
    }

    /**
     * Posts recentes
     */
    public function recent(Request $request): JsonResponse
    {
        $posts = BlogPost::recent($request->limit ?? 5)->get();

        return response()->json([
            'data' => $posts
        ]);
    }

    /**
     * Posts em destaque
     */
    public function featured(Request $request): JsonResponse
    {
        $posts = BlogPost::featured()
                        ->published()
                        ->latest('published_at')
                        ->limit($request->limit ?? 5)
                        ->get();

        return response()->json([
            'data' => $posts
        ]);
    }

    /**
     * Buscar posts
     */
    public function search(Request $request): JsonResponse
    {
        $term = $request->get('term');

        if (!$term || strlen($term) < 3) {
            return response()->json([
                'data' => [],
                'meta' => ['total' => 0]
            ]);
        }

        $posts = BlogPost::published()
                        ->where(function ($query) use ($term) {
                            $query->where('title', 'like', "%{$term}%")
                                  ->orWhere('content', 'like', "%{$term}%")
                                  ->orWhere('excerpt', 'like', "%{$term}%");
                        })
                        ->with(['user', 'categories'])
                        ->latest('published_at')
                        ->paginate($request->per_page ?? 10);

        return response()->json([
            'data' => $posts->items(),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'last_page' => $posts->lastPage(),
            ]
        ]);
    }
}
