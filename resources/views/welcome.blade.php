{{-- <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

        <!-- Styles -->
        <style>
            /*! normalize.css v8.0.1 | MIT License | github.com/necolas/normalize.css */html{line-height:1.15;-webkit-text-size-adjust:100%}body{margin:0}a{background-color:transparent}[hidden]{display:none}html{font-family:system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica Neue,Arial,Noto Sans,sans-serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol,Noto Color Emoji;line-height:1.5}*,:after,:before{box-sizing:border-box;border:0 solid #e2e8f0}a{color:inherit;text-decoration:inherit}svg,video{display:block;vertical-align:middle}video{max-width:100%;height:auto}.bg-white{--bg-opacity:1;background-color:#fff;background-color:rgba(255,255,255,var(--bg-opacity))}.bg-gray-100{--bg-opacity:1;background-color:#f7fafc;background-color:rgba(247,250,252,var(--bg-opacity))}.border-gray-200{--border-opacity:1;border-color:#edf2f7;border-color:rgba(237,242,247,var(--border-opacity))}.border-t{border-top-width:1px}.flex{display:flex}.grid{display:grid}.hidden{display:none}.items-center{align-items:center}.justify-center{justify-content:center}.font-semibold{font-weight:600}.h-5{height:1.25rem}.h-8{height:2rem}.h-16{height:4rem}.text-sm{font-size:.875rem}.text-lg{font-size:1.125rem}.leading-7{line-height:1.75rem}.mx-auto{margin-left:auto;margin-right:auto}.ml-1{margin-left:.25rem}.mt-2{margin-top:.5rem}.mr-2{margin-right:.5rem}.ml-2{margin-left:.5rem}.mt-4{margin-top:1rem}.ml-4{margin-left:1rem}.mt-8{margin-top:2rem}.ml-12{margin-left:3rem}.-mt-px{margin-top:-1px}.max-w-6xl{max-width:72rem}.min-h-screen{min-height:100vh}.overflow-hidden{overflow:hidden}.p-6{padding:1.5rem}.py-4{padding-top:1rem;padding-bottom:1rem}.px-6{padding-left:1.5rem;padding-right:1.5rem}.pt-8{padding-top:2rem}.fixed{position:fixed}.relative{position:relative}.top-0{top:0}.right-0{right:0}.shadow{box-shadow:0 1px 3px 0 rgba(0,0,0,.1),0 1px 2px 0 rgba(0,0,0,.06)}.text-center{text-align:center}.text-gray-200{--text-opacity:1;color:#edf2f7;color:rgba(237,242,247,var(--text-opacity))}.text-gray-300{--text-opacity:1;color:#e2e8f0;color:rgba(226,232,240,var(--text-opacity))}.text-gray-400{--text-opacity:1;color:#cbd5e0;color:rgba(203,213,224,var(--text-opacity))}.text-gray-500{--text-opacity:1;color:#a0aec0;color:rgba(160,174,192,var(--text-opacity))}.text-gray-600{--text-opacity:1;color:#718096;color:rgba(113,128,150,var(--text-opacity))}.text-gray-700{--text-opacity:1;color:#4a5568;color:rgba(74,85,104,var(--text-opacity))}.text-gray-900{--text-opacity:1;color:#1a202c;color:rgba(26,32,44,var(--text-opacity))}.underline{text-decoration:underline}.antialiased{-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.w-5{width:1.25rem}.w-8{width:2rem}.w-auto{width:auto}.grid-cols-1{grid-template-columns:repeat(1,minmax(0,1fr))}@media (min-width:640px){.sm\:rounded-lg{border-radius:.5rem}.sm\:block{display:block}.sm\:items-center{align-items:center}.sm\:justify-start{justify-content:flex-start}.sm\:justify-between{justify-content:space-between}.sm\:h-20{height:5rem}.sm\:ml-0{margin-left:0}.sm\:px-6{padding-left:1.5rem;padding-right:1.5rem}.sm\:pt-0{padding-top:0}.sm\:text-left{text-align:left}.sm\:text-right{text-align:right}}@media (min-width:768px){.md\:border-t-0{border-top-width:0}.md\:border-l{border-left-width:1px}.md\:grid-cols-2{grid-template-columns:repeat(2,minmax(0,1fr))}}@media (min-width:1024px){.lg\:px-8{padding-left:2rem;padding-right:2rem}}@media (prefers-color-scheme:dark){.dark\:bg-gray-800{--bg-opacity:1;background-color:#2d3748;background-color:rgba(45,55,72,var(--bg-opacity))}.dark\:bg-gray-900{--bg-opacity:1;background-color:#1a202c;background-color:rgba(26,32,44,var(--bg-opacity))}.dark\:border-gray-700{--border-opacity:1;border-color:#4a5568;border-color:rgba(74,85,104,var(--border-opacity))}.dark\:text-white{--text-opacity:1;color:#fff;color:rgba(255,255,255,var(--text-opacity))}.dark\:text-gray-400{--text-opacity:1;color:#cbd5e0;color:rgba(203,213,224,var(--text-opacity))}.dark\:text-gray-500{--tw-text-opacity:1;color:#6b7280;color:rgba(107,114,128,var(--tw-text-opacity))}}
        </style>

        <style>
            body {
                font-family: 'Nunito', sans-serif;
            }
        </style>
    </head>
    <body class="antialiased">
        <div class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center py-4 sm:pt-0">
            @if (Route::has('login'))
                <div class="hidden fixed top-0 right-0 px-6 py-4 sm:block">
                    @auth
                        <a href="{{ url('/home') }}" class="text-sm text-gray-700 dark:text-gray-500 underline">Home</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-gray-700 dark:text-gray-500 underline">Log in</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="ml-4 text-sm text-gray-700 dark:text-gray-500 underline">Register</a>
                        @endif
                    @endauth
                </div>
            @endif

            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-center pt-8 sm:justify-start sm:pt-0">
                    <svg viewBox="0 0 651 192" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-16 w-auto text-gray-700 sm:h-20">
                        <g clip-path="url(#clip0)" fill="#EF3B2D">
                            <path d="M248.032 44.676h-16.466v100.23h47.394v-14.748h-30.928V44.676zM337.091 87.202c-2.101-3.341-5.083-5.965-8.949-7.875-3.865-1.909-7.756-2.864-11.669-2.864-5.062 0-9.69.931-13.89 2.792-4.201 1.861-7.804 4.417-10.811 7.661-3.007 3.246-5.347 6.993-7.016 11.239-1.672 4.249-2.506 8.713-2.506 13.389 0 4.774.834 9.26 2.506 13.459 1.669 4.202 4.009 7.925 7.016 11.169 3.007 3.246 6.609 5.799 10.811 7.66 4.199 1.861 8.828 2.792 13.89 2.792 3.913 0 7.804-.955 11.669-2.863 3.866-1.908 6.849-4.533 8.949-7.875v9.021h15.607V78.182h-15.607v9.02zm-1.431 32.503c-.955 2.578-2.291 4.821-4.009 6.73-1.719 1.91-3.795 3.437-6.229 4.582-2.435 1.146-5.133 1.718-8.091 1.718-2.96 0-5.633-.572-8.019-1.718-2.387-1.146-4.438-2.672-6.156-4.582-1.719-1.909-3.032-4.152-3.938-6.73-.909-2.577-1.36-5.298-1.36-8.161 0-2.864.451-5.585 1.36-8.162.905-2.577 2.219-4.819 3.938-6.729 1.718-1.908 3.77-3.437 6.156-4.582 2.386-1.146 5.059-1.718 8.019-1.718 2.958 0 5.656.572 8.091 1.718 2.434 1.146 4.51 2.674 6.229 4.582 1.718 1.91 3.054 4.152 4.009 6.729.953 2.577 1.432 5.298 1.432 8.162-.001 2.863-.479 5.584-1.432 8.161zM463.954 87.202c-2.101-3.341-5.083-5.965-8.949-7.875-3.865-1.909-7.756-2.864-11.669-2.864-5.062 0-9.69.931-13.89 2.792-4.201 1.861-7.804 4.417-10.811 7.661-3.007 3.246-5.347 6.993-7.016 11.239-1.672 4.249-2.506 8.713-2.506 13.389 0 4.774.834 9.26 2.506 13.459 1.669 4.202 4.009 7.925 7.016 11.169 3.007 3.246 6.609 5.799 10.811 7.66 4.199 1.861 8.828 2.792 13.89 2.792 3.913 0 7.804-.955 11.669-2.863 3.866-1.908 6.849-4.533 8.949-7.875v9.021h15.607V78.182h-15.607v9.02zm-1.432 32.503c-.955 2.578-2.291 4.821-4.009 6.73-1.719 1.91-3.795 3.437-6.229 4.582-2.435 1.146-5.133 1.718-8.091 1.718-2.96 0-5.633-.572-8.019-1.718-2.387-1.146-4.438-2.672-6.156-4.582-1.719-1.909-3.032-4.152-3.938-6.73-.909-2.577-1.36-5.298-1.36-8.161 0-2.864.451-5.585 1.36-8.162.905-2.577 2.219-4.819 3.938-6.729 1.718-1.908 3.77-3.437 6.156-4.582 2.386-1.146 5.059-1.718 8.019-1.718 2.958 0 5.656.572 8.091 1.718 2.434 1.146 4.51 2.674 6.229 4.582 1.718 1.91 3.054 4.152 4.009 6.729.953 2.577 1.432 5.298 1.432 8.162 0 2.863-.479 5.584-1.432 8.161zM650.772 44.676h-15.606v100.23h15.606V44.676zM365.013 144.906h15.607V93.538h26.776V78.182h-42.383v66.724zM542.133 78.182l-19.616 51.096-19.616-51.096h-15.808l25.617 66.724h19.614l25.617-66.724h-15.808zM591.98 76.466c-19.112 0-34.239 15.706-34.239 35.079 0 21.416 14.641 35.079 36.239 35.079 12.088 0 19.806-4.622 29.234-14.688l-10.544-8.158c-.006.008-7.958 10.449-19.832 10.449-13.802 0-19.612-11.127-19.612-16.884h51.777c2.72-22.043-11.772-40.877-33.023-40.877zm-18.713 29.28c.12-1.284 1.917-16.884 18.589-16.884 16.671 0 18.697 15.598 18.813 16.884h-37.402zM184.068 43.892c-.024-.088-.073-.165-.104-.25-.058-.157-.108-.316-.191-.46-.056-.097-.137-.176-.203-.265-.087-.117-.161-.242-.265-.345-.085-.086-.194-.148-.29-.223-.109-.085-.206-.182-.327-.252l-.002-.001-.002-.002-35.648-20.524a2.971 2.971 0 00-2.964 0l-35.647 20.522-.002.002-.002.001c-.121.07-.219.167-.327.252-.096.075-.205.138-.29.223-.103.103-.178.228-.265.345-.066.089-.147.169-.203.265-.083.144-.133.304-.191.46-.031.085-.08.162-.104.25-.067.249-.103.51-.103.776v38.979l-29.706 17.103V24.493a3 3 0 00-.103-.776c-.024-.088-.073-.165-.104-.25-.058-.157-.108-.316-.191-.46-.056-.097-.137-.176-.203-.265-.087-.117-.161-.242-.265-.345-.085-.086-.194-.148-.29-.223-.109-.085-.206-.182-.327-.252l-.002-.001-.002-.002L40.098 1.396a2.971 2.971 0 00-2.964 0L1.487 21.919l-.002.002-.002.001c-.121.07-.219.167-.327.252-.096.075-.205.138-.29.223-.103.103-.178.228-.265.345-.066.089-.147.169-.203.265-.083.144-.133.304-.191.46-.031.085-.08.162-.104.25-.067.249-.103.51-.103.776v122.09c0 1.063.568 2.044 1.489 2.575l71.293 41.045c.156.089.324.143.49.202.078.028.15.074.23.095a2.98 2.98 0 001.524 0c.069-.018.132-.059.2-.083.176-.061.354-.119.519-.214l71.293-41.045a2.971 2.971 0 001.489-2.575v-38.979l34.158-19.666a2.971 2.971 0 001.489-2.575V44.666a3.075 3.075 0 00-.106-.774zM74.255 143.167l-29.648-16.779 31.136-17.926.001-.001 34.164-19.669 29.674 17.084-21.772 12.428-43.555 24.863zm68.329-76.259v33.841l-12.475-7.182-17.231-9.92V49.806l12.475 7.182 17.231 9.92zm2.97-39.335l29.693 17.095-29.693 17.095-29.693-17.095 29.693-17.095zM54.06 114.089l-12.475 7.182V46.733l17.231-9.92 12.475-7.182v74.537l-17.231 9.921zM38.614 7.398l29.693 17.095-29.693 17.095L8.921 24.493 38.614 7.398zM5.938 29.632l12.475 7.182 17.231 9.92v79.676l.001.005-.001.006c0 .114.032.221.045.333.017.146.021.294.059.434l.002.007c.032.117.094.222.14.334.051.124.088.255.156.371a.036.036 0 00.004.009c.061.105.149.191.222.288.081.105.149.22.244.314l.008.01c.084.083.19.142.284.215.106.083.202.178.32.247l.013.005.011.008 34.139 19.321v34.175L5.939 144.867V29.632h-.001zm136.646 115.235l-65.352 37.625V148.31l48.399-27.628 16.953-9.677v33.862zm35.646-61.22l-29.706 17.102V66.908l17.231-9.92 12.475-7.182v33.841z"/>
                        </g>
                    </svg>
                </div>

                <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-2">
                        <div class="p-6">
                            <div class="flex items-center">
                                <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" class="w-8 h-8 text-gray-500"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                <div class="ml-4 text-lg leading-7 font-semibold"><a href="https://laravel.com/docs" class="underline text-gray-900 dark:text-white">Documentation</a></div>
                            </div>

                            <div class="ml-12">
                                <div class="mt-2 text-gray-600 dark:text-gray-400 text-sm">
                                    Laravel has wonderful, thorough documentation covering every aspect of the framework. Whether you are new to the framework or have previous experience with Laravel, we recommend reading all of the documentation from beginning to end.
                                </div>
                            </div>
                        </div>

                        <div class="p-6 border-t border-gray-200 dark:border-gray-700 md:border-t-0 md:border-l">
                            <div class="flex items-center">
                                <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" class="w-8 h-8 text-gray-500"><path d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <div class="ml-4 text-lg leading-7 font-semibold"><a href="https://laracasts.com" class="underline text-gray-900 dark:text-white">Laracasts</a></div>
                            </div>

                            <div class="ml-12">
                                <div class="mt-2 text-gray-600 dark:text-gray-400 text-sm">
                                    Laracasts offers thousands of video tutorials on Laravel, PHP, and JavaScript development. Check them out, see for yourself, and massively level up your development skills in the process.
                                </div>
                            </div>
                        </div>

                        <div class="p-6 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" class="w-8 h-8 text-gray-500"><path d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                                <div class="ml-4 text-lg leading-7 font-semibold"><a href="https://laravel-news.com/" class="underline text-gray-900 dark:text-white">Laravel News</a></div>
                            </div>

                            <div class="ml-12">
                                <div class="mt-2 text-gray-600 dark:text-gray-400 text-sm">
                                    Laravel News is a community driven portal and newsletter aggregating all of the latest and most important news in the Laravel ecosystem, including new package releases and tutorials.
                                </div>
                            </div>
                        </div>

                        <div class="p-6 border-t border-gray-200 dark:border-gray-700 md:border-l">
                            <div class="flex items-center">
                                <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" class="w-8 h-8 text-gray-500"><path d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div class="ml-4 text-lg leading-7 font-semibold text-gray-900 dark:text-white">Vibrant Ecosystem</div>
                            </div>

                            <div class="ml-12">
                                <div class="mt-2 text-gray-600 dark:text-gray-400 text-sm">
                                    Laravel's robust library of first-party tools and libraries, such as <a href="https://forge.laravel.com" class="underline">Forge</a>, <a href="https://vapor.laravel.com" class="underline">Vapor</a>, <a href="https://nova.laravel.com" class="underline">Nova</a>, and <a href="https://envoyer.io" class="underline">Envoyer</a> help you take your projects to the next level. Pair them with powerful open source libraries like <a href="https://laravel.com/docs/billing" class="underline">Cashier</a>, <a href="https://laravel.com/docs/dusk" class="underline">Dusk</a>, <a href="https://laravel.com/docs/broadcasting" class="underline">Echo</a>, <a href="https://laravel.com/docs/horizon" class="underline">Horizon</a>, <a href="https://laravel.com/docs/sanctum" class="underline">Sanctum</a>, <a href="https://laravel.com/docs/telescope" class="underline">Telescope</a>, and more.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-center mt-4 sm:items-center sm:justify-between">
                    <div class="text-center text-sm text-gray-500 sm:text-left">
                        <div class="flex items-center">
                            <svg fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor" class="-mt-px w-5 h-5 text-gray-400">
                                <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>

                            <a href="https://laravel.bigcartel.com" class="ml-1 underline">
                                Shop
                            </a>

                            <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" class="ml-4 -mt-px w-5 h-5 text-gray-400">
                                <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>

                            <a href="https://github.com/sponsors/taylorotwell" class="ml-1 underline">
                                Sponsor
                            </a>
                        </div>
                    </div>

                    <div class="ml-4 text-center text-sm text-gray-500 sm:text-right sm:ml-0">
                        Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
                    </div>
                </div>
            </div>
        </div>
    </body>
</html> --}}
@extends('layouts.app')

@section('title', 'Buat Surat Bddaru')
@section('bawah', 'Kelola Suraddddt Masuk/Keluar & Nota Dinas')

@section('content')
<style>
    .preview-box { 
        border: 2px solid #dee2e6; 
        border-radius: 8px; 
        padding: 30px; 
        background: #fff;
        font-family: 'Times New Roman', Times, serif;
        min-height: 600px;
    }
    .form-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .section-title {
        font-weight: 600;
        color: #495057;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #0d6efd;
    }
</style>

<div class="container-fluid px-4">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Buat Surat Baru</h4>
        <a href="{{ route('surat.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <form action="{{ route('surat.store') }}" method="POST" enctype="multipart/form-data" id="suratForm">
        @csrf
        
        <!-- SECTION 1: INFORMASI DASAR -->
        <div class="form-section">
            <div class="section-title">📋 Informasi Dasar Surat</div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="jenis" class="form-label">Jenis Surat <span class="text-danger">*</span></label>
                    <select class="form-control" id="jenis_surat" name="jenis" required>
    <option value="">-- Pilih Jenis Surat --</option>
    <option value="surat_keluar" {{ old('jenis') == 'surat_keluar' ? 'selected' : '' }}>Surat Keluar</option>
    <option value="surat_masuk" {{ old('jenis') == 'surat_masuk' ? 'selected' : '' }}>Surat Masuk</option>
    <option value="nota_dinas" {{ old('jenis') == 'nota_dinas' ? 'selected' : '' }}>Nota Dinas</option>
</select>
                    @error('jenis')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="nomor_surat" class="form-label">Nomor Surat</label>
                    <input type="text" class="form-control @error('nomor_surat') is-invalid @enderror" 
                           id="nomor_surat" name="nomor_surat" 
                           value="{{ old('nomor_surat') }}" 
                           readonly placeholder="Auto-generate">
                    <small class="text-muted">Nomor akan di-generate otomatis</small>
                    @error('nomor_surat')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="tanggal_surat" class="form-label">Tanggal Surat <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('tanggal_surat') is-invalid @enderror" 
                           id="tanggal_surat" name="tanggal_surat" 
                           value="{{ old('tanggal_surat', date('Y-m-d')) }}" required>
                    @error('tanggal_surat')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="perihal" class="form-label">Perihal <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('perihal') is-invalid @enderror" 
                           id="perihal" name="perihal" 
                           value="{{ old('perihal') }}" 
                           placeholder="Masukkan perihal surat" required>
                    @error('perihal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- SECTION 2: KLASIFIKASI & SIFAT -->
        <div class="form-section">
            <div class="section-title">🔐 Klasifikasi & Sifat Surat</div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="col-md-6 mb-3">
    <label class="form-label">Sifat Surat <span class="text-danger">*</span></label>
    <select class="form-control" id="sifat_surat" name="sifat" required>
        <option value="biasa" {{ old('sifat') == 'biasa' ? 'selected' : '' }}>Biasa</option>
        <option value="penting" {{ old('sifat') == 'penting' ? 'selected' : '' }}>Penting</option>
        <option value="rahasia" {{ old('sifat') == 'rahasia' ? 'selected' : '' }}>Rahasia</option>
    </select>
    @error('sifat')
        <div class="text-danger small">{{ $message }}</div>
    @enderror
</div>
                    @error('sifat')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="klasifikasi" class="form-label">Klasifikasi</label>
                    <select class="form-select @error('klasifikasi') is-invalid @enderror" id="klasifikasi" name="klasifikasi">
                        <option value="">-- Pilih Klasifikasi --</option>
                        <option value="umum" {{ old('klasifikasi') == 'umum' ? 'selected' : '' }}>Umum</option>
                        <option value="penting" {{ old('klasifikasi') == 'penting' ? 'selected' : '' }}>Penting</option>
                        <option value="rahasia" {{ old('klasifikasi') == 'rahasia' ? 'selected' : '' }}>Rahasia</option>
                        <option value="sangat_rahasia" {{ old('klasifikasi') == 'sangat_rahasia' ? 'selected' : '' }}>Sangat Rahasia</option>
                    </select>
                    @error('klasifikasi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="derajat_keamanan" class="form-label">Derajat Keamanan</label>
                    <select class="form-select @error('derajat_keamanan') is-invalid @enderror" 
                            id="derajat_keamanan" name="derajat_keamanan">
                        <option value="">-- Pilih Derajat --</option>
                        <option value="terbuka" {{ old('derajat_keamanan') == 'terbuka' ? 'selected' : '' }}>Terbuka</option>
                        <option value="terbatas" {{ old('derajat_keamanan') == 'terbatas' ? 'selected' : '' }}>Terbatas</option>
                        <option value="rahasia" {{ old('derajat_keamanan') == 'rahasia' ? 'selected' : '' }}>Rahasia</option>
                    </select>
                    @error('derajat_keamanan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- SECTION 3: ISI SURAT -->
        <div class="form-section">
            <div class="section-title">📝 Isi Surat</div>
            
            <div class="mb-3">
                <label for="letter_type_selector" class="form-label">Template Cepat (Opsional)</label>
                <select class="form-select" id="letter_type_selector">
                    <option value="">-- Pilih Template --</option>
                    <option value="undangan_rapat">Undangan Rapat</option>
                    <option value="permohonan_izin">Permohonan Izin</option>
                    <option value="pemberitahuan">Pemberitahuan</option>
                    <option value="permohonan_pembayaran">Permohonan Pembayaran</option>
                    <option value="surat_tugas">Surat Tugas</option>
                    <option value="lain-lain">Kosongkan (Tulis Manual)</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="isi_surat" class="form-label">Isi Surat <span class="text-danger">*</span></label>
                <textarea class="form-control @error('isi_surat') is-invalid @enderror" 
                           id="isi_surat" name="isi_surat" rows="12" required>{{ old('isi_surat') }}</textarea>
                <small class="text-muted">Tip: Gunakan format surat yang baku dan jelas</small>
                @error('isi_surat')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- SECTION 4: LAMPIRAN -->
        <div class="form-section">
            <div class="section-title">📎 Lampiran Dokumen</div>
            <div class="mb-3">
                <label for="berkas_surat" class="form-label">Upload File</label>
                <input type="file" class="form-control @error('berkas_surat') is-invalid @enderror" 
                       id="berkas_surat" name="berkas_surat[]" multiple 
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                <small class="text-muted">Format: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Max 5MB per file)</small>
                @error('berkas_surat')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                <div id="fileList" class="mt-2"></div>
            </div>
        </div>

        <!-- SECTION 5: INFORMASI PEMBUAT -->
        <div class="form-section">
            <div class="section-title">✍️ Informasi Pembuat Surat</div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tempat_pembuatan" class="form-label">Tempat Pembuatan</label>
                    <input type="text" class="form-control @error('tempat_pembuatan') is-invalid @enderror" 
                           id="tempat_pembuatan" name="tempat_pembuatan" 
                           value="{{ old('tempat_pembuatan', 'Bandung') }}" 
                           placeholder="Contoh: Bandung">
                    @error('tempat_pembuatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="jabatan_pembuat" class="form-label">Jabatan Pembuat</label>
                    <input type="text" class="form-control @error('jabatan_pembuat') is-invalid @enderror" 
                           id="jabatan_pembuat" name="jabatan_pembuat" 
                           value="{{ old('jabatan_pembuat', auth()->user()->jabatan ?? '') }}" 
                           placeholder="Contoh: Kepala Bagian Keuangan">
                    @error('jabatan_pembuat')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- SECTION 6: PENERIMA & CATATAN -->
        {{-- <div class="form-section">
            <div class="section-title">📨 Penerima & Catatan</div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="user_id" class="form-label">Penerima Surat (Internal)</label>
                    <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
                        <option value="">-- Pilih Penerima --</option>
                        @foreach($coba as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->jabatan }} - {{ $user->nama_lengkap }} ({{ ucfirst($user->role) }})
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Opsional: Kosongkan jika surat untuk eksternal</small>
                    @error('user_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="catatan_tambahan" class="form-label">Catatan Tambahan</label>
                    <textarea class="form-control @error('catatan_tambahan') is-invalid @enderror" 
                               id="catatan_tambahan" name="catatan_tambahan" rows="3" 
                               placeholder="Catatan internal (opsional)">{{ old('catatan_tambahan') }}</textarea>
                    @error('catatan_tambahan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div> --}}

        <!-- PREVIEW BOX -->
        <div id="previewBox" class="mb-4" style="display:none;">
            <div class="section-title">👁️ Pratinjau Surat</div>
            <div id="previewContent" class="preview-box">
                <!-- Preview akan dimuat di sini -->
            </div>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="d-flex gap-2 flex-wrap mb-5">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-save me-2"></i>Simpan Surat
            </button>
            <button type="button" id="tombol_preview" class="btn btn-warning btn-lg">
                <i class="bi bi-eye me-2"></i>Lihat Pratinjau
            </button>
            <button type="reset" class="btn btn-outline-danger btn-lg" onclick="return confirm('Yakin ingin reset form?')">
                <i class="bi bi-arrow-clockwise me-2"></i>Reset
            </button>
            <a href="{{ route('surat.index') }}" class="btn btn-outline-secondary btn-lg">
                <i class="bi bi-x-circle me-2"></i>Batal
            </a>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Setup CSRF token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Auto-generate nomor surat saat tanggal berubah
    $('#tanggal_surat, #jenis').on('change', function() {
        generateNomorSurat();
    });

    function generateNomorSurat() {
        const jenis = $('#jenis').val();
        const tanggal = $('#tanggal_surat').val();
        
        if (!jenis || !tanggal) return;

        $.ajax({
            url: "{{ route('surat.generate-number') }}",
            type: 'GET',
            data: { jenis: jenis, tanggal: tanggal },
            success: function(response) {
                $('#nomor_surat').val(response.nomor_surat);
            }
        });
    }

    // Load template isi surat
    $('#letter_type_selector').on('change', function() {
        const selectedType = $(this).val();
        
        if (selectedType) {
            $.ajax({
                url: "{{ route('get.surat.template') }}",
                type: 'GET',
                data: { jenis: selectedType },
                success: function(response) {
                    $('#isi_surat').val(response.isi_surat);
                },
                error: function() {
                    $('#isi_surat').val('');
                }
            });
        }
    });

    // Preview toggle
    $('#previewBox').hide();
    let latestFormData = $('#suratForm').serialize();
    let debounceTimer;

    $('#suratForm input, #suratForm textarea, #suratForm select').on('input change', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            latestFormData = $('#suratForm').serialize();
            if ($('#previewBox').is(':visible')) {
                $('#previewBox').slideUp(200).empty();
                $('#tombol_preview').html('<i class="bi bi-eye me-2"></i>Refresh Pratinjau').removeClass('btn-primary btn-danger').addClass('btn-warning');
            }
        }, 400);
    });

    $('#tombol_preview').on('click', function(e) {
        e.preventDefault();
        const $previewBox = $('#previewBox');
        const $btn = $(this);

        if ($previewBox.is(':hidden')) {
            $.ajax({
                type: 'POST',
                url: "{{ route('surat.preview.ajax') }}",
                data: latestFormData,
                beforeSend: function() {
                    $previewBox.html('<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3">Memuat Pratinjau...</p></div>').slideDown(200);
                },
                success: function(response) {
                    $previewBox.html('<div class="section-title">👁️ Pratinjau Surat</div><div id="previewContent" class="preview-box">' + response + '</div>').slideDown(200);
                    $btn.html('<i class="bi bi-x-circle me-2"></i>Tutup Pratinjau').removeClass('btn-warning').addClass('btn-danger');
                },
                error: function(xhr) {
                    $previewBox.html('<div class="alert alert-danger">Gagal memuat pratinjau. Pastikan semua field required sudah diisi.</div>').slideDown(200);
                }
            });
        } else {
            $previewBox.slideUp(200, function() {
                $previewBox.empty();
            });
            $btn.html('<i class="bi bi-eye me-2"></i>Lihat Pratinjau').removeClass('btn-danger').addClass('btn-warning');
        }
    });

    // File input display
    $('#berkas_surat').on('change', function() {
        const files = this.files;
        let fileList = '<ul class="list-group">';
        for (let i = 0; i < files.length; i++) {
            fileList += `<li class="list-group-item d-flex justify-content-between align-items-center">
                ${files[i].name}
                <span class="badge bg-primary rounded-pill">${(files[i].size / 1024).toFixed(2)} KB</span>
            </li>`;
        }
        fileList += '</ul>';
        $('#fileList').html(fileList);
    });
});
</script>
@endpush
