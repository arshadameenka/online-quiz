<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Quiz') }}
        </h2>
    </x-slot>


    <div class=" mb-10  bg-gradient-to-r from-violet-600 to-violet-500 dark:bg-gray-800  shadow-sm p-4">
        <div class="container">
            <h1 class="text-3xl text-center mb-8 text-gray-50 font-bold">Online Quiz</h1>
            <div id="category-container">

                <h2 class="text-center text-xl  mb-4">Select Quiz Type</h2>
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
                <p id="question" class="text-center text-white p-2 bg-blue-950 py-4 text-xl rounded-xl"></p>
                <div id="choices" class="grid grid-cols-2 gap-1"></div>
                <div class="flex justify-center mt-4">
                    <button
                        class='reset text-center text-lg text-white p-2 rounded-md font-bold bg-blue-900'>Reset</button>
                </div>

            </div>

        </div>

    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let questions = [];
            let currentQuestionIndex = 0;
            let timer;
            let countdown = 90;
            let userAnswers = [];
            // Fetch categories when clicking a pagination dot
            $('.pagination-dot').click(function() {
                let page = $(this).data('page');
                $.ajax({
                    url: "{{ route('quiz.index') }}?page=" + page,
                    method: 'GET',
                    success: function(response) {
                        let newCategories = $(response).find('#categories').html();
                        $('#categories').html(newCategories);

                        $('.pagination-dot').removeClass('text-black');
                        $(`.pagination-dot[data-page="${page}"]`).addClass('text-black');
                    }
                });
            });

            // Fetch questions when clicking a category
            $(document).on('click', '.category-btn', function() {
                let categoryName = $(this).data('name');
                $.ajax({
                    url: `/quiz/questions/${encodeURIComponent(categoryName)}`,
                    method: 'GET',
                    success: function(response) {
                        if (response.length > 0) {
                            questions = response;
                            currentQuestionIndex = 0;
                            userAnswers = []; // Reset user answers for new quiz
                            $('#quiz-container').show();
                            $('#category-container').hide();
                            showQuestion();
                        }
                    }
                });
            });
            //show qustions
            function showQuestion() {
                if (currentQuestionIndex >= questions.length) {
                    showSummary();
                    return;
                }

                let question = questions[currentQuestionIndex];
                $('#question-number').text(` ${currentQuestionIndex + 1}`);
                $('#question').text(question.question);
                $('#choices').empty();

                let choices = [...question.choices];
                choices.sort(() => Math.random() - 0.5); // Shuffle choices

                choices.forEach(choice => {
                    $('#choices').append(
                        `<button class="choice-btn bg-blue-900 text-white p-2 rounded-md m-6 text-lg " data-answer="${choice}">${choice}</button>`
                    );
                    $('.choice-btn').prop('disabled', false);
                });

                resetTimer();
            }
            //reset timer
            function resetTimer() {
                let totalTime = 90;
                clearInterval(timer);
                countdown = 90;
                updateTimerDisplay(countdown);

                timer = setInterval(() => {
                    countdown--;
                    updateTimerDisplay(countdown);

                    if (countdown <= 0) {
                        clearInterval(timer);
                        saveAnswer(null); // go to next question if time expires
                    }
                    if (countdown < 10) {
                        $('#timer').removeClass("bg-yellow-600 bg-white").addClass("bg-red-500");
                    } else if (countdown < 30) {
                        $('#timer').removeClass("bg-white bg-red-500").addClass("bg-yellow-600");
                    } else {
                        $('#timer').removeClass("bg-yellow-600 bg-red-500").addClass("bg-white");
                    }

                }, 1000);
            }

            //timer update in each second
            function updateTimerDisplay(seconds) {
                let minutes = Math.floor(seconds / 60);
                let secs = seconds % 60;
                let formattedTime = `${minutes}:${secs < 10 ? '0' : ''}${secs}`;
                $('#timer').text(` ${formattedTime}`);
            }
            //choice selected
            $(document).on('click', '.choice-btn', function() {
                let selectedAnswer = $(this).data('answer');
                $('.choice-btn').prop('disabled', true);
                saveAnswer(selectedAnswer);
            });
            //save user selected answer and correct answer to an array for result calculation
            function saveAnswer(selectedAnswer) {
                clearInterval(timer);

                let question = questions[currentQuestionIndex];
                let questionId = questions[currentQuestionIndex].id;

                $.ajax({
                    url: "/quiz/check-answer",
                    method: "POST",
                    data: {
                        question_id: questionId,
                        selected_answer: selectedAnswer,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        correctAnswer = response.correctAnswer;
                        userAnswers.push({
                            question: question.question,
                            selected: selectedAnswer,
                            correct: correctAnswer,
                            isCorrect: selectedAnswer === correctAnswer
                        });

                        currentQuestionIndex++;
                        showQuestion();
                    }
                });


            }

            function showSummary() {
                $('#quiz-container').hide();
                let correctAnswers = userAnswers.filter(entry => entry.isCorrect).length;
                let totalQuestions = userAnswers.length;
                let scorePercentage = (correctAnswers / totalQuestions) * 100;

                let resultText = "";
                let resultClass = "";

                if (scorePercentage >= 60) {
                    resultText = "Winner";
                    resultClass = "text-green-500";
                } else if (scorePercentage >= 40) {
                    resultText = "Better";
                    resultClass = "text-yellow-500";
                } else {
                    resultText = "Failed";
                    resultClass = "text-red-500";
                }
                let summaryHtml = `<h2 class='text-center text-lg text-white font-bold mb-4'>Quiz Result</h2> 
                <h3 class='text-center ${resultClass} text-2xl font-bold'>${resultText}</h3>`;

                userAnswers.forEach((entry, index) => {
                    let resultClass = entry.isCorrect ? "text-green-500" : "text-red-500";
                    summaryHtml += `
                        <div class='flex flex-col justify-between p-4 bg-blue-900 text-white rounded-md mb-4'>
                            <div>
                                 <span class="text-white text-lg"><strong>${index + 1}:</strong> ${entry.question} </span>
                            </div>
                            <span class="grid grid-cols-3 gap-3">
                               
                                <span class="${resultClass} ">Your Answer : <b>${entry.selected || "No Answer"}</b></span> 
                                <span class="text-green-500">Correct Answer : <b>${entry.correct}</b></span>
                            </span>
                        </div>
                    `;
                });

                summaryHtml +=
                    "<button class='reset text-center text-lg text-white p-2 rounded-md font-bold bg-blue-900'>Reset</button>";

                $('#quiz-container').after(`<div id="quiz-summary">${summaryHtml}</div>`);
            }

            $(document).on('click', '.reset', function() {
                $('#quiz-summary').remove();
                $('#quiz-container').hide();
                $('#category-container').show();
                // Reset pagination to page 1
                $('.pagination-dot').removeClass('text-black');
                $('.pagination-dot[data-page="1"]').addClass('text-black');

                // Fetch categories for page 1
                $.ajax({
                    url: "{{ route('quiz.index') }}?page=1",
                    method: 'GET',
                    success: function(response) {
                        let newCategories = $(response).find('#categories').html();
                        $('#categories').html(newCategories);
                    }
                });

            });
        });
    </script>
</x-app-layout>
