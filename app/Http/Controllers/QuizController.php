<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;

class QuizController extends Controller
{
    protected $api = "https://the-trivia-api.com/api";
    public function index(Request $request)
    {
        
    }

    
}
