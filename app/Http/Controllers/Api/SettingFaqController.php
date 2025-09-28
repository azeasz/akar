<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SettingFaq;

class SettingFaqController extends Controller
{
    /**
     * Get all settings by type
     *
     * @param int $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByType($type)
    {
        $setting = SettingFaq::where('type', $type)->first();

        if (!$setting) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json($setting);
    }

    /**
     * Get about content
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function about()
    {
        return $this->getByType(4);
    }

    /**
     * Get privacy policy content
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function privacyPolicy()
    {
        return $this->getByType(2);
    }

    /**
     * Get terms and conditions content
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function termsConditions()
    {
        return $this->getByType(3);
    }

    /**
     * Get FAQ content
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function faq()
    {
        return $this->getByType(5);
    }
} 