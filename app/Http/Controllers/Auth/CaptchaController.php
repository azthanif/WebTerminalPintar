<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CaptchaController extends Controller
{
    public function generate()
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $captchaText = '';
        $captchaData = [];

        for ($i = 0; $i < 6; $i++) {
            $char = $chars[rand(0, strlen($chars) - 1)];
            $captchaText .= $char;
            
            $captchaData[] = [
                'char' => $char,
                'style' => [
                    'transform' => 'rotate(' . (rand(0, 40) - 20) . 'deg) translateY(' . (rand(0, 10) - 5) . 'px)',
                    'fontSize' => (rand(0, 8) + 20) . 'px',
                    'opacity' => (70 + rand(0, 30)) / 100,
                ]
            ];
        }

        Session::put('captcha_answer', $captchaText);

        return response()->json([
            'captcha' => $captchaData
        ]);
    }

    public function verify(Request $request)
    {
        $input = strtoupper($request->input('answer', ''));
        $answer = Session::get('captcha_answer');

        if ($input && $input === $answer) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 422);
    }
}
