<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Quiz') }}
        </h2>
    </x-slot>


    <div class=" mb-10  bg-gradient-to-r from-violet-600 to-violet-500 dark:bg-gray-800  shadow-sm p-4">
        <div class="p-4">

            <div class="flex justify-between items-center mb-10">
                <h1 class="text-3xl text-center text-gray-50 font-bold flex-1 ">Online Quiz</h1>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg" stroke="white">
                            <path d="M21 12L13 12" stroke="white" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"></path>
                            <path d="M18 15L20.913 12.087V12.087C20.961 12.039 20.961 11.961 20.913 11.913V11.913L18 9"
                                stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path
                                d="M16 5V4.5V4.5C16 3.67157 15.3284 3 14.5 3H5C3.89543 3 3 3.89543 3 5V19C3 20.1046 3.89543 21 5 21H14.5C15.3284 21 16 20.3284 16 19.5V19.5V19"
                                stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </a>
                </form>
            </div>
            
            <div id="error-block" class="bg-red-500 text-white p-3 rounded-md mb-4 relative hidden">
                <span class="absolute top-1 right-2 cursor-pointer" onclick="document.getElementById('error-block').classList.add('hidden')">
                    &times;
                </span>
                <p id="error-message"></p>
            </div>
            


            <div id="category-container">
                <div class="flex justify-center items-center">

                    <h2 class="text-center text-xl  mb-4">Select Quiz Type</h2>
                </div>
                <div id="categories" class="grid grid-cols-2 gap-4">
                    @foreach ($categories as $category)
                        <button class="category-btn bg-blue-900 text-white p-2 rounded-md py-4"
                            data-name="{{ $category }}">{{ $category }}</button>
                    @endforeach
                </div>
                {{-- Pagination --}}
                <div id="pagination" class="mt-3 text-center">
                    @for ($i = 1; $i <= $categories->lastPage(); $i++)
                        <span
                            class="pagination-dot text-gray-300 m-1 cursor-pointer text-[20px] {{ $i == $categories->currentPage() ? 'text-black' : '' }}"
                            data-page="{{ $i }}">●</span>
                    @endfor
                </div>
            </div>
            <div id="quiz-container" style="display: none;">
                <div class="flex justify-between mb-4">

                    <h3 id="question-number"
                        class="w-10 h-10 flex items-center justify-center rounded-full bg-red-600 text-white text-lg font-bold">
                    </h3>
                    <span class="flex justify-between">

                        <p id="timer" class="text-lg font-bold bg-white rounded-lg p-3 w-16"></p>

                    </span>
                </div>
                <div class="h-20 bg-blue-950 rounded-xl">

                    <p id="question" class=" text-center text-white py-4 text-xl "></p>
                </div>
                <div id="choices" class="grid grid-cols-2 gap-1"></div>
                <div class="flex justify-center mt-4">
                    <button
                        class='reset text-center text-lg text-white p-2 rounded-md font-bold bg-blue-900'>Reset</button>
                </div>

            </div>

        </div>

    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <Script>
        var indexRoute = "{{ route('quiz.index') }}";
        var getQustionsRoute = "/quiz/questions/";
        var checkAnswerRoute = "{{ route('quiz.check') }}";
        var logoutRoute = "{{ route('logout') }}";
    </Script>

    @vite('resources/js/quiz.js');

</x-app-layout>
