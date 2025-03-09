<x-app-layout>
<div class="flex items-center justify-center min-h-screen bg-gray-100 dark:bg-gray-900">
    <div class="text-center">
        <h1 class="text-6xl font-bold text-gray-800 dark:text-white">{{ $message }}</h1>
        
        
        
        <a href="{{ url('/') }}" class="mt-6 inline-block px-6 py-3 text-white bg-blue-600 rounded-lg hover:bg-blue-700">
            Go Back Home
        </a>
    </div>
</div>
</x-app-layout>