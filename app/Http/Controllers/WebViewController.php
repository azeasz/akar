<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SettingFaq;

class WebViewController extends Controller
{
    /**
     * Tampilkan halaman terms and conditions
     *
     * @return \Illuminate\View\View
     */
    public function termsConditions()
    {
        $setting = SettingFaq::where('type', 3)->first();
        
        if (!$setting) {
            $setting = new SettingFaq();
            $setting->title = 'Syarat dan Ketentuan';
            $setting->description = 'Konten syarat dan ketentuan belum tersedia.';
            $setting->updated_at = now();
        }
        
        return view('terms-conditions', compact('setting'));
    }
    
    /**
     * Tampilkan halaman privacy policy
     *
     * @return \Illuminate\View\View
     */
    public function privacyPolicy()
    {
        $setting = SettingFaq::where('type', 2)->first();
        
        if (!$setting) {
            $setting = new SettingFaq();
            $setting->title = 'Kebijakan Privasi';
            $setting->description = 'Konten kebijakan privasi belum tersedia.';
            $setting->updated_at = now();
        }
        
        return view('privacy-policy', compact('setting'));
    }
    
    /**
     * Tampilkan halaman about
     *
     * @return \Illuminate\View\View
     */
    public function about()
    {
        $setting = SettingFaq::where('type', 4)->first();
        
        if (!$setting) {
            $setting = new SettingFaq();
            $setting->title = 'Tentang AKAR';
            $setting->description = 'Konten tentang AKAR belum tersedia.';
            $setting->updated_at = now();
        }
        
        return view('about', compact('setting'));
    }
} 