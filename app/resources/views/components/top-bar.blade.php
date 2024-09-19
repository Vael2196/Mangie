@props(["title"])

<div class="bg-gray-100 dark:bg-gray-700 border-b-2 px-4 py-2 mb-7 flex justify-between">
    <h1 class="text-2xl my-5 mx-2 font-medium">{{$title}}</h1>
    <a class = "flex items-center hover:cursor-pointer" href = "/"><i class="fa-solid fa-user fa-3x"></i></a>
</div>