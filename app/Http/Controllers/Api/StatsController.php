<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanionProfile;
use App\Models\Review;
use App\Models\Media;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    /**
     * Retorna estatísticas gerais da plataforma
     */
    public function general(): JsonResponse
    {
        try {
            $stats = [
                'total_companions' => CompanionProfile::where('verified', true)->count(),
                'total_reviews' => Review::where('status', 'approved')->count(),
                'total_videos' => Media::where('file_type', 'video')->count(),
                'total_cities' => City::count(),
            ];

            return response()->json([
                'data' => $stats,
                'message' => 'Estatísticas gerais carregadas com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao carregar estatísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retorna estatísticas de uma cidade específica
     */
    public function city(string $citySlug): JsonResponse
    {
        try {
            $city = City::where('slug', $citySlug)->first();

            if (!$city) {
                return response()->json([
                    'message' => 'Cidade não encontrada'
                ], 404);
            }

            $stats = [
                'city' => $city,
                'total_companions' => CompanionProfile::where('city_id', $city->id)
                    ->where('verified', true)
                    ->count(),
                'total_reviews' => Review::whereHas('companionProfile', function ($query) use ($city) {
                    $query->where('city_id', $city->id);
                })->where('status', 'approved')->count(),
                'online_companions' => CompanionProfile::where('city_id', $city->id)
                    ->where('verified', true)
                    ->where('online_status', true)
                    ->count(),
            ];

            return response()->json([
                'data' => $stats,
                'message' => 'Estatísticas da cidade carregadas com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao carregar estatísticas da cidade',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
