<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class BlogCategoryController extends Controller
{
    /**
     * Listar categorias do blog
     */
    public function index(Request $request): JsonResponse
    {
        $query = BlogCategory::withCount('posts');

        if ($request->with_posts) {
            $query->withPosts();
        }

        if ($request->popular) {
            $query->popular($request->limit ?? 10);
        } else {
            $query->orderBy('name');
        }

        $categories = $query->paginate($request->per_page ?? 50);

        return response()->json([
            'data' => $categories->items(),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
                'last_page' => $categories->lastPage(),
            ]
        ]);
    }

    /**
     * Mostrar categoria específica
     */
    public function show(BlogCategory $category): JsonResponse
    {
        $category->load(['posts' => function ($query) {
            $query->published()->latest('published_at');
        }]);

        return response()->json([
            'data' => $category
        ]);
    }

    /**
     * Criar categoria (Admin)
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', BlogCategory::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:blog_categories',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $category = BlogCategory::create($request->only(['name', 'description']));

        return response()->json([
            'message' => 'Categoria criada com sucesso',
            'data' => $category
        ], 201);
    }

    /**
     * Atualizar categoria (Admin)
     */
    public function update(Request $request, BlogCategory $category): JsonResponse
    {
        $this->authorize('update', $category);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:100|unique:blog_categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $category->update($request->only(['name', 'description']));

        return response()->json([
            'message' => 'Categoria atualizada com sucesso',
            'data' => $category->fresh()
        ]);
    }

    /**
     * Excluir categoria (Admin)
     */
    public function destroy(BlogCategory $category): JsonResponse
    {
        $this->authorize('delete', $category);

        // Verificar se há posts associados
        if ($category->posts()->count() > 0) {
            return response()->json([
                'message' => 'Não é possível excluir uma categoria que possui posts associados'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Categoria excluída com sucesso'
        ]);
    }

    /**
     * Categorias populares
     */
    public function popular(Request $request): JsonResponse
    {
        $categories = BlogCategory::popular($request->limit ?? 10)->get();

        return response()->json([
            'data' => $categories
        ]);
    }

    /**
     * Posts por categoria
     */
    public function posts(BlogCategory $category, Request $request): JsonResponse
    {
        $posts = $category->posts()
                         ->published()
                         ->with(['user'])
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
